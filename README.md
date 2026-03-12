# Symfony Auth Starter

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Symfony](https://img.shields.io/badge/Symfony-6.4%20%7C%207.x-black.svg)](https://symfony.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4.svg)](https://php.net)

**The Laravel Breeze of Symfony** — scaffold complete authentication into your app in one command.

One `composer require`, one `php bin/console auth:install`, and you have a fully working auth system. After install, you own all the code — no runtime dependency on this package.

## Quick Start

```bash
composer require vraith/symfony-auth-starter

php bin/console auth:install

# Follow the interactive prompts, then:
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

The installer will ask whether to enable Google OAuth and prompt for your sender email address. It generates all files directly into your project.

## What Gets Generated

```
src/
  Entity/User.php                           # Doctrine entity with email, password, googleId, isVerified
  Controller/Auth/
    LoginController.php                     # Login + logout
    RegistrationController.php              # Register + email verification
    ResetPasswordController.php             # Forgot password + reset
    GoogleController.php                    # Google OAuth (optional)
  Form/
    RegistrationFormType.php                # Email + password fields
    ResetPasswordRequestFormType.php        # Email field
    ChangePasswordFormType.php              # Repeated password field
  Security/
    LoginFormAuthenticator.php              # Form login with CSRF + remember me
    GoogleAuthenticator.php                 # OAuth2 authenticator (optional)
templates/auth/
    login.html.twig                         # Login page
    register.html.twig                      # Registration page
    forgot_password.html.twig               # Request password reset
    check_email.html.twig                   # "Check your email" confirmation
    reset_password.html.twig                # New password form
    email/verification.html.twig            # Verification email
    email/reset_password.html.twig          # Password reset email
config/packages/
    security.yaml                           # Full firewall + access control config
    knpu_oauth2_client.yaml                 # Google OAuth client (optional)
```

## Routes

| Method     | Path                           | Name                        |
|------------|--------------------------------|-----------------------------|
| GET        | `/login`                       | `app_login`                 |
| GET        | `/logout`                      | `app_logout`                |
| GET\|POST  | `/register`                    | `app_register`              |
| GET        | `/verify/email`                | `app_verify_email`          |
| GET\|POST  | `/reset-password`              | `app_forgot_password_request` |
| GET        | `/reset-password/check-email`  | `app_check_email`           |
| GET\|POST  | `/reset-password/reset/{token}`| `app_reset_password`        |
| GET        | `/connect/google`              | `connect_google_start` *    |
| GET        | `/connect/google/check`        | `connect_google_check` *    |

\* Only when Google OAuth is enabled.

## What Each Component Does

**LoginFormAuthenticator** creates a Symfony Security `Passport` with `UserBadge`, `PasswordCredentials`, `CsrfTokenBadge`, and `RememberMeBadge`. It uses `TargetPathTrait` to redirect users back to where they were before login.

**GoogleAuthenticator** extends KnpU's `OAuth2Authenticator`. It fetches the Google user profile, finds or creates a local `User`, and automatically links Google accounts to existing users with matching email addresses. OAuth users are auto-verified.

**ResetPasswordController** uses SymfonyCasts' ResetPasswordBundle with an anti-enumeration pattern — it always redirects to the "check your email" page even when no user is found, preventing attackers from probing which emails have accounts.

**RegistrationController** hashes the password, persists the user, generates a signed email verification URL, and auto-logs in the user via `UserAuthenticatorInterface`.

## Customization

All generated code lives in your `src/` and `templates/` directories. Edit it directly — there are no bundle overrides, no config layers, no abstractions to learn.

**Want to add a username field?** Add it to `User.php` and `RegistrationFormType.php`.
**Want to change the email template?** Edit `templates/auth/email/verification.html.twig`.
**Want to redirect somewhere else after login?** Change the route in `LoginFormAuthenticator::onAuthenticationSuccess()`.

## Google OAuth Setup

If you enabled Google OAuth during install:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a project and enable the Google+ API
3. Create OAuth 2.0 credentials
4. Add authorized redirect URI: `https://yourdomain.com/connect/google/check`
5. Set the credentials in `.env.local`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
```

If you didn't install Google OAuth initially, run `auth:install` again with `--force`.

To add Google OAuth to an existing install, first require the packages:

```bash
composer require knpuniversity/oauth2-client-bundle league/oauth2-google
php bin/console auth:install --force
```

## Prerequisites

The installer checks for these packages and will tell you if any are missing:

- `symfony/security-bundle`
- `symfony/twig-bundle`
- `symfony/mailer`
- `doctrine/doctrine-bundle`
- `symfonycasts/verify-email-bundle`
- `symfonycasts/reset-password-bundle`

All are pulled in automatically via `composer require`.

## Re-running the Installer

Running `auth:install` a second time will skip files that already exist. Use `--force` to overwrite:

```bash
php bin/console auth:install --force
```

## Removing the Package

After installation, all auth code lives in your project. You can safely remove the package:

```bash
composer remove vraith/symfony-auth-starter
```

Your auth system will continue to work since it has zero runtime dependency on the bundle.

## Roadmap

- Multi-provider OAuth (GitHub, Facebook, LinkedIn)
- Two-Factor Authentication (2FA)
- Rate limiting on login/registration
- Account management (change email, change password)

## License

MIT. See [LICENSE](LICENSE).
