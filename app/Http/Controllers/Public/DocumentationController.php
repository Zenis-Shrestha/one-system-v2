<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class DocumentationController extends Controller
{
    public function index()
    {
        return view('public.documentation.index');
    }

    public function api()
    {
        return view('public.documentation.api');
    }

    public function security()
    {
        return view('public.documentation.security');
    }

    public function deployment()
    {
        $deploymentGuide = [
            'title' => 'Deployment Guide',
            'description' => 'Complete guide for deploying CAS authentication system to production',
            'sections' => [
                'requirements' => 'System Requirements',
                'docker' => 'Docker Deployment',
                'kubernetes' => 'Kubernetes Deployment',
                'environment' => 'Environment Configuration',
                'security' => 'Production Security',
                'monitoring' => 'Monitoring & Maintenance'
            ]
        ];

        return view('public.documentation.deployment', compact('deploymentGuide'));
    }

    public function troubleshooting()
    {
        $troubleshootingGuide = [
            'title' => 'Troubleshooting Guide',
            'description' => 'Common issues and solutions for CAS authentication system',
            'sections' => [
                'authentication' => 'Authentication Issues',
                'database' => 'Database Connection Problems',
                'ssl' => 'SSL/Certificate Issues',
                'performance' => 'Performance Problems',
                'logging' => 'Logging and Debugging',
                'recovery' => 'System Recovery'
            ]
        ];

        return view('public.documentation.troubleshooting', compact('troubleshootingGuide'));
    }

    public function examples()
    {
        return view('public.documentation.examples');
    }

    public function laravel()
    {
        $laravelGuide = [
            'title' => 'Laravel Integration Guide',
            'description' => 'Complete guide for integrating Laravel applications with CAS SSO',
            'sections' => [
                'installation' => 'Package Installation',
                'configuration' => 'Environment Configuration',
                'middleware' => 'Middleware Setup',
                'routes' => 'Route Protection',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.laravel', compact('laravelGuide'));
    }

    public function dotnet()
    {
        $dotnetGuide = [
            'title' => '.NET MVC C# Integration Guide',
            'description' => 'Complete guide for integrating .NET MVC applications with CAS SSO',
            'sections' => [
                'setup' => 'Project Setup',
                'configuration' => 'Configuration',
                'authentication' => 'Authentication',
                'middleware' => 'Middleware',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.dotnet', compact('dotnetGuide'));
    }

    public function nodejs()
    {
        $nodejsGuide = [
            'title' => 'Node.js/Express Integration Guide',
            'description' => 'Complete guide for integrating Node.js applications with CAS SSO',
            'sections' => [
                'setup' => 'Project Setup',
                'jwt' => 'JWT Validation',
                'middleware' => 'Express Middleware',
                'routes' => 'Route Protection',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.nodejs', compact('nodejsGuide'));
    }

    public function java()
    {
        $javaGuide = [
            'title' => 'Java Spring Boot Integration Guide',
            'description' => 'Complete guide for integrating Java Spring Boot applications with CAS SSO',
            'sections' => [
                'setup' => 'Project Setup',
                'configuration' => 'Configuration',
                'security' => 'Security Configuration',
                'controller' => 'Controller Examples',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.java', compact('javaGuide'));
    }

    public function python()
    {
        $pythonGuide = [
            'title' => 'Python/Django Integration Guide',
            'description' => 'Complete guide for integrating Python Django applications with CAS SSO',
            'sections' => [
                'setup' => 'Project Setup',
                'middleware' => 'Django Middleware',
                'views' => 'View Protection',
                'models' => 'User Models',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.python', compact('pythonGuide'));
    }

    public function javascript()
    {
        $javascriptGuide = [
            'title' => 'JavaScript/HTML Integration Guide',
            'description' => 'Complete guide for frontend integration with CAS SSO',
            'sections' => [
                'setup' => 'Setup',
                'authentication' => 'Authentication Flow',
                'token' => 'Token Management',
                'spa' => 'Single Page Applications',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.javascript', compact('javascriptGuide'));
    }

    public function react()
    {
        $reactGuide = [
            'title' => 'React Integration Guide',
            'description' => 'Complete guide for integrating React applications with CAS SSO using hooks and context',
            'sections' => [
                'setup' => 'Package Installation',
                'provider' => 'CasProvider Setup',
                'hooks' => 'Hooks (useCasAuth, useCasUser)',
                'components' => 'Protected Routes & Components',
                'roles' => 'Role-Based Access',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.react', compact('reactGuide'));
    }

    public function nextjs()
    {
        $nextjsGuide = [
            'title' => 'Next.js Integration Guide',
            'description' => 'Complete guide for integrating Next.js App Router applications with CAS SSO',
            'sections' => [
                'setup' => 'Package Installation',
                'middleware' => 'Next.js Middleware',
                'server' => 'Server-Side Auth',
                'client' => 'Client Components',
                'api_routes' => 'API Route Handlers',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.nextjs', compact('nextjsGuide'));
    }

    public function angular()
    {
        $angularGuide = [
            'title' => 'Angular Integration Guide',
            'description' => 'Complete guide for integrating Angular applications with CAS SSO',
            'sections' => [
                'setup' => 'Package Installation',
                'module' => 'Module Configuration',
                'guards' => 'Route Guards',
                'interceptors' => 'HTTP Interceptors',
                'services' => 'Auth Service',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.angular', compact('angularGuide'));
    }

    public function vue()
    {
        $vueGuide = [
            'title' => 'Vue 3 Integration Guide',
            'description' => 'Complete guide for integrating Vue 3 applications with CAS SSO using Composition API',
            'sections' => [
                'setup' => 'Package Installation',
                'plugin' => 'Vue Plugin Setup',
                'composables' => 'Composables (useCasAuth, useCasUser)',
                'router' => 'Router Guards',
                'pinia' => 'Pinia Store',
                'examples' => 'Code Examples'
            ]
        ];

        return view('public.documentation.vue', compact('vueGuide'));
    }

    public function rust()
    {
        $rustGuide = [
            'title' => 'Rust Integration Guide',
            'description' => 'Complete guide for integrating Rust applications with CAS SSO using the rust-cas-client crate',
            'sections' => [
                'overview' => 'Overview',
                'installation' => 'Add the Crate',
                'configuration' => 'Configuration',
                'client' => 'Build the Client',
                'flow' => 'Login, Callback & Logout',
                'examples' => 'Complete Example'
            ]
        ];

        return view('public.documentation.rust', compact('rustGuide'));
    }

    public function section($section)
    {
        $sectionMethods = [
            'laravel' => 'laravel',
            'dotnet' => 'dotnet',
            'nodejs' => 'nodejs',
            'java' => 'java',
            'python' => 'python',
            'javascript' => 'javascript',
            'react' => 'react',
            'nextjs' => 'nextjs',
            'angular' => 'angular',
            'vue' => 'vue',
            'rust' => 'rust',
            'api' => 'api',
            'deployment' => 'deployment',
            'troubleshooting' => 'troubleshooting',
            'examples' => 'examples',
            'authentication' => 'authentication'
        ];

        if (isset($sectionMethods[$section]) && method_exists($this, $sectionMethods[$section])) {
            return $this->{$sectionMethods[$section]}();
        }

        abort(404, 'Documentation section not found');
    }

    public function apiEndpoint($endpoint = 'overview')
    {
        $apiEndpoints = [
            'overview' => [
                'title' => 'API Overview',
                'description' => 'Complete REST API reference for CAS authentication system',
                'endpoints' => [
                    'POST /api/sso/token' => 'Generate SSO authentication token',
                    'POST /api/validate-token' => 'Validate and consume an SSO token',
                    'GET /api/user' => 'Get authenticated user information',
                    'POST /api/logout' => 'Logout and invalidate session'
                ]
            ],
            'authentication' => [
                'title' => 'Authentication Endpoints',
                'description' => 'Core authentication API endpoints',
                'endpoints' => [
                    'POST /api/login' => 'User login with credentials',
                    'POST /api/register' => 'User registration',
                    'POST /api/logout' => 'User logout'
                ]
            ],
            'sso' => [
                'title' => 'SSO Endpoints',
                'description' => 'Single Sign-On API endpoints',
                'endpoints' => [
                    'POST /api/sso/token' => 'Generate SSO token with client credentials',
                    'POST /api/validate-token' => 'Validate and consume an SSO token',
                    'GET /auth/sso/callback' => 'SSO callback handler'
                ]
            ]
        ];

        $endpointData = $apiEndpoints[$endpoint] ?? $apiEndpoints['overview'];

        return view('public.documentation.api', compact('endpointData', 'endpoint'));
    }

    public function authentication()
    {
        $authGuide = [
            'title' => 'Authentication Guide',
            'description' => 'Complete guide for CAS authentication flows and security features',
            'sections' => [
                'overview' => 'Authentication Overview',
                'flows' => 'Authentication Flows',
                'tokens' => 'JWT Token Management',
                'security' => 'Security Features',
                'examples' => 'Implementation Examples'
            ]
        ];

        return view('public.documentation.authentication', compact('authGuide'));
    }
}
