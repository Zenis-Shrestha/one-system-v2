from setuptools import setup, find_packages

setup(
    name='cas-system-client',
    version='2.0.0',
    description='Python client for CAS (Central Authentication Service) SSO integration',
    long_description=open('README.md').read(),
    long_description_content_type='text/markdown',
    author='CAS System',
    author_email='support@innovativesolution.com.np',
    url='https://github.com/InSol-2021/one-system',
    project_urls={
        'Source': 'https://github.com/InSol-2021/one-system/tree/master/packages/python-cas-client',
        'Bug Tracker': 'https://github.com/InSol-2021/one-system/issues',
    },
    packages=find_packages(),
    python_requires='>=3.9',
    install_requires=[
        # >=2.32.0 includes fixes for CVE-2023-32681 (.netrc/proxy credential leak)
        # and CVE-2024-35195 (cert-verify bypass).
        'requests>=2.32.0',
    ],
    extras_require={
        'django': ['django>=4.2'],
        'flask': ['flask>=3.0'],
    },
    classifiers=[
        'Development Status :: 5 - Production/Stable',
        'Intended Audience :: Developers',
        'License :: OSI Approved :: MIT License',
        'Programming Language :: Python :: 3',
        'Programming Language :: Python :: 3.9',
        'Programming Language :: Python :: 3.10',
        'Programming Language :: Python :: 3.11',
        'Programming Language :: Python :: 3.12',
        'Topic :: Software Development :: Libraries',
        'Topic :: System :: Systems Administration :: Authentication/Directory',
    ],
    keywords='cas sso authentication jwt single-sign-on',
    license='MIT',
)
