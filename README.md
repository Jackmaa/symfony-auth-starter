# 🔐 Symfony Auth Starter

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Symfony](https://img.shields.io/badge/Symfony-6.4%20%7C%207.x-black.svg)](https://symfony.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4.svg)](https://php.net)

> **A modern, opinionated authentication starter for Symfony apps — including social login — installable in minutes.**

Stop wasting hours setting up authentication. Get a production-ready auth system with Google OAuth, email verification, and password reset in a single command.

---

## 🎯 Why This Package?

Building authentication in Symfony is powerful but time-consuming. Every new project requires:

- Setting up Security configuration
- Creating User entities
- Implementing OAuth providers
- Building email verification
- Adding password reset flows
- Designing login/register forms

**This package does all of that for you** — with sensible defaults and easy customization.

---

## ✨ Features

- ✅ **Classic Email/Password Authentication** — Login and registration out of the box
- ✅ **Google OAuth2 Login** — One-click social authentication
- ✅ **Auto-Registration** — First-time Google users are automatically registered
- ✅ **Email Verification** — Secure account activation flow
- ✅ **Password Reset** — Complete forgot/reset password system
- ✅ **Production-Ready Templates** — Clean, minimal Twig templates included
- ✅ **Auto-Configuration** — Security config generated automatically
- ✅ **Fully Customizable** — Override everything: entities, templates, controllers

---

## 📦 Installation

### Requirements

- PHP 8.1+
- Symfony 6.4 or 7.x
- Composer

### Install via Composer

```bash
composer require valentin/symfony-auth-starter
```

### Run the Installation Command

```bash
php bin/console auth:install
```

That's it! 🎉

The command will:

1. Install required dependencies
2. Generate your `User` entity
3. Create authentication controllers
4. Publish Twig templates
5. Configure Symfony Security
6. Set up database migrations

---

## ⚙️ Configuration

### 1. Set Up Environment Variables

Add your Google OAuth credentials to `.env`:

```env
###> Google OAuth ###
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
###< Google OAuth ###
```

**Get Google OAuth credentials:**

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select existing)
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URI: `https://yourdomain.com/connect/google/check`

### 2. Configure the Package (Optional)

Edit `config/packages/auth_starter.yaml`:

```yaml
auth_starter:
    # Where to redirect after successful login
    redirect_after_login: '/dashboard'
    
    # Enable/disable Google OAuth
    enable_google: true
    
    # Auto-verify users who sign up via OAuth
    auto_verify_oauth_users: true
    
    # Customize email sender
    from_email: 'noreply@yourapp.com'
    from_name: 'Your App'
```

### 3. Run Migrations

```bash
php bin/console doctrine:migrations:migrate
```

---

## 🚀 Usage

### Available Routes

After installation, these routes are automatically available:

| Route                     | Purpose                    |
| ------------------------- | -------------------------- |
| `/login`                  | Login page                 |
| `/register`               | Registration page          |
| `/logout`                 | Logout action              |
| `/connect/google`         | Initiate Google OAuth      |
| `/connect/google/check`   | Google OAuth callback      |
| `/forgot-password`        | Request password reset     |
| `/reset-password/{token}` | Reset password form        |
| `/verify-email`           | Email verification handler |

### User Entity

The generated `User` entity includes:

```php
class User implements UserInterface
{
    private ?int $id;
    private string $email;
    private array $roles = [];
    private ?string $password;      // Nullable for OAuth-only users
    private ?string $googleId;      // Google OAuth identifier
    private bool $isVerified;
    private DateTimeImmutable $createdAt;
}
```

---

## 🎨 Customization

### Override Templates

Copy templates to your project and customize:

```bash
cp -r vendor/valentin/symfony-auth-starter/templates/auth templates/auth
```

Then modify `templates/auth/login.html.twig`, etc.

### Extend the User Entity

Add custom fields to your `User` entity:

```php
// src/Entity/User.php

use Valentin\AuthStarter\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User extends BaseUser
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;
    
    // Add your custom fields...
}
```

### Override Controllers

Create your own controller extending the base:

```php
// src/Controller/CustomLoginController.php

use Valentin\AuthStarter\Controller\LoginController as BaseLoginController;

class CustomLoginController extends BaseLoginController
{
    // Override methods as needed
}
```

---

## 🔧 Advanced Configuration

### Disable Google OAuth

Set `enable_google: false` in `auth_starter.yaml`, or unset the Google credentials in `.env`.

### Custom Redirect After Login

```yaml
auth_starter:
    redirect_after_login: '/my-custom-page'
```

### Customize Email Templates

Override email templates by creating:

- `templates/auth/email/verification.html.twig`
- `templates/auth/email/reset_password.html.twig`

---

## 📚 How It Works

### Classic Authentication Flow

1. User visits `/register`
2. User submits email + password
3. Account created, verification email sent
4. User clicks verification link
5. Account verified, user can login

### Google OAuth Flow

1. User clicks "Sign in with Google"
2. Redirected to Google for authorization
3. Google redirects back to `/connect/google/check`
4. If user exists → logged in
5. If new user → account auto-created and logged in

---

## 🛠️ Development Roadmap

### ✅ MVP (v1.0)

- [x] Email/Password authentication
- [x] Google OAuth2
- [x] Email verification
- [x] Password reset
- [x] Auto-configuration command

### 🚧 Future Features (v2.0+)

- [ ] Multi-provider support (GitHub, Facebook, LinkedIn)
- [ ] Two-Factor Authentication (2FA)
- [ ] API/JWT support
- [ ] Account management UI (profile, change email, etc.)
- [ ] Rate limiting
- [ ] Remember me functionality
- [ ] Social account linking

### ❌ Out of Scope

- Multi-tenancy
- Role-based permissions (use Symfony's built-in voters)
- SPA/React integration (use with API Platform)

---

## 🤝 Contributing

Contributions are welcome! This is an opinionated starter, but improvements are always appreciated.

### Development Setup

```bash
git clone https://github.com/valentin/symfony-auth-starter.git
cd symfony-auth-starter
composer install
```

### Running Tests

```bash
composer test
```

### Submitting Issues

Please use GitHub Issues for:

- Bug reports
- Feature requests
- Documentation improvements

---

## 📝 License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.

---

## 🙏 Credits

Built with ❤️ using:

- [Symfony Security Component](https://symfony.com/doc/current/security.html)
- [KnpUOAuth2ClientBundle](https://github.com/knpuniversity/oauth2-client-bundle)
- [SymfonyCasts VerifyEmailBundle](https://github.com/SymfonyCasts/verify-email-bundle)
- [SymfonyCasts ResetPasswordBundle](https://github.com/SymfonyCasts/reset-password-bundle)

---

## 💡 Need Help?

- 📖 [Read the Documentation](https://github.com/valentin/symfony-auth-starter/wiki)
- 🐛 [Report an Issue](https://github.com/valentin/symfony-auth-starter/issues)
- 💬 [Join Discussions](https://github.com/valentin/symfony-auth-starter/discussions)

---

**⭐ If this package saves you time, please star the repo!*
