from setuptools import setup, find_packages

setup(
    name='cas-client',
    version='2.0.0',
    description='Python client for CAS (Central Authentication Service) SSO integration',
    long_description=open('README.md').read(),
    long_description_content_type='text/markdown',
    author='CAS System',
    author_email='support@innovativesolution.com.np',
    url='https://github.com/insol-dev/python-client',
    packages=find_packages(),
    python_requires='>=3.8',
    install_requires=[
        'requests>=2.28.0',
        'PyJWT>=2.6.0',
    ],
    extras_require={
        'django': ['django>=3.2'],
        'flask': ['flask>=2.0'],
    },
    classifiers=[
        'Development Status :: 5 - Production/Stable',
        'Intended Audience :: Developers',
        'License :: OSI Approved :: MIT License',
        'Programming Language :: Python :: 3',
        'Programming Language :: Python :: 3.8',
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
