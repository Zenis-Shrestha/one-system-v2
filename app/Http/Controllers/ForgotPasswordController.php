<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\SecuritySetting;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $settings = SecuritySetting::current();
        if (!$settings->enable_forgot_password) {
            return back()->withErrors(['email' => 'Password reset is currently disabled.']);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('message', 'If an account with that email exists, a password reset link has been sent.');
        }

        $recentAttempts = DB::table('cas_admin.password_resets')
            ->where('email', $request->email)
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        if ($recentAttempts >= 3) {
            return back()->withErrors(['email' => 'Too many reset attempts. Please wait 5 minutes before trying again.']);
        }

        $token = Str::random(64);
        $expiresAt = now()->addMinutes($settings->password_reset_expiry ?? 60);

        DB::table('cas_admin.password_resets')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'expires_at' => $expiresAt,
            'created_at' => now()
        ]);

        try {
            $this->sendResetEmail($user, $token, $settings);
            return back()->with('message', 'If an account with that email exists, a password reset link has been sent.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send reset email. Please contact administrator.']);
        }
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $resetRecords = DB::table('cas_admin.password_resets')
            ->where('email', $request->email)
            ->where('expires_at', '>', now())
            ->where('used_at', null)
            ->get();

        $validToken = null;
        foreach ($resetRecords as $record) {
            if (Hash::check($request->token, $record->token)) {
                $validToken = $record;
                break;
            }
        }

        if (!$validToken) {
            return back()->withErrors(['email' => 'This password reset token is invalid or has expired.']);
        }

        DB::table('cas_admin.users')
            ->where('email', $request->email)
            ->update([
                'password' => Hash::make($request->password),
                'updated_at' => now()
            ]);

        DB::table('cas_admin.password_resets')
            ->where('id', $validToken->id)
            ->update(['used_at' => now()]);

        DB::table('cas_admin.password_resets')
            ->where('email', $request->email)
            ->delete();

        return redirect('/auth/login')->with('message', 'Your password has been reset successfully. Please log in with your new password.');
    }

    private function sendResetEmail($user, $token, $settings)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $settings->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $settings->smtp_username;
            $mail->Password = $settings->smtp_password;
            $mail->SMTPSecure = $settings->smtp_encryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $settings->smtp_port;

            $mail->setFrom($settings->from_email, $settings->from_name);
            $mail->addAddress($user->email, $user->username);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - CAS System';

            $resetUrl = url('/auth/reset-password/' . $token . '?email=' . urlencode($user->email));
            $expiryMinutes = $settings->password_reset_expiry ?? 60;

            $mail->Body = $this->getEmailTemplate($user, $resetUrl, $expiryMinutes);
            $mail->AltBody = "Hello {$user->username},\n\nYou have requested a password reset for your CAS account.\n\nClick the following link to reset your password:\n{$resetUrl}\n\nThis link will expire in {$expiryMinutes} minutes.\n\nIf you did not request this reset, please ignore this email.\n\nBest regards,\nCAS System";

            $mail->send();
        } catch (Exception $e) {
            throw new \Exception("Mailer Error: {$mail->ErrorInfo}");
        }
    }

    private function getEmailTemplate($user, $resetUrl, $expiryMinutes)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <title>Password Reset</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1e40af; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
                .button { display: inline-block; background: #1e40af; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                .footer { text-align: center; color: #666; font-size: 14px; margin-top: 20px; }
                .warning { background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 6px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Password Reset Request</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$user->username},</h2>
                    <p>You have requested a password reset for your CAS System account.</p>
                    <p>Click the button below to reset your password:</p>
                    <a href='{$resetUrl}' class='button'>Reset Password</a>
                    <div class='warning'>
                        <strong>⚠️ Important:</strong>
                        <ul>
                            <li>This link will expire in <strong>{$expiryMinutes} minutes</strong></li>
                            <li>If you did not request this reset, please ignore this email</li>
                            <li>Never share this link with anyone</li>
                        </ul>
                    </div>
                    <p>If the button doesn't work, copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; background: #e5e7eb; padding: 10px; border-radius: 4px; font-family: monospace;'>{$resetUrl}</p>
                </div>
                <div class='footer'>
                    <p>This email was sent by CAS System. If you have any questions, please contact your administrator.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    public function cleanupExpiredTokens()
    {
        DB::table('cas_admin.password_resets')
            ->where('expires_at', '<', now())
            ->delete();
    }
}
