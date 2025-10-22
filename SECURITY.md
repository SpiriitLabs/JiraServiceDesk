# Security Policy

## Supported Versions

This project is actively maintained. The following versions are currently supported with security updates:

| Version | Supported          |
|---------|--------------------|
| Latest  | ✅                 |

## Reporting a Vulnerability

If you discover a security vulnerability, **please do not open a public issue**.

Instead, report it securely by emailing:

📫 **[hello@spiriit.com](mailto:hello@spiriit.com)**

Please include:
- A clear and concise description of the issue
- Steps to reproduce if possible
- Any relevant logs or proof-of-concept code

We aim to respond to all responsible disclosures **within 72 hours**.

## Scope

This security policy applies to:

- The Symfony web application
- FrankenPHP binary deployment setup
- Integration with Jira via the [php-JiraCloud-RESTAPI](https://github.com/lesstif/php-JiraCloud-RESTAPI)

It does not apply to:
- Third-party dependencies or services (e.g., Jira itself, database configuration, reverse proxy setup)
- Custom deployments outside the officially supported Docker or binary method

## Recommendations

To protect your instance, we recommend:
- Running behind a secure reverse proxy (e.g., Nginx with HTTPS)
- Keeping your `.env` file secure and out of version control
- Using strong, unique secrets for `APP_SECRET` and tokens
- Monitoring logs and webhook activity
