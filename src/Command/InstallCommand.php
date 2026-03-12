<?php

declare(strict_types=1);

namespace Vraith\AuthStarter\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'auth:install',
    description: 'Scaffold authentication into your Symfony application',
)]
class InstallCommand extends Command
{
    private Filesystem $filesystem;
    private string $scaffoldDir;

    public function __construct(
        private string $projectDir,
    ) {
        parent::__construct();
        $this->filesystem = new Filesystem();
        $this->scaffoldDir = \dirname(__DIR__) . '/Resources/scaffold';
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        $this->displayBanner($io);

        // ── Prerequisites ──────────────────────────────────────────
        if (!$this->checkPrerequisites($io)) {
            return Command::FAILURE;
        }

        // ── Interactive questions ──────────────────────────────────
        $enableGoogle = $io->confirm('Enable Google OAuth login?', true);

        if ($enableGoogle && !$this->checkGooglePrerequisites($io)) {
            $enableGoogle = false;
        }

        $fromEmail = $io->ask('From email address for outgoing emails', 'noreply@example.com');

        $io->newLine();
        $io->section('Installing authentication scaffolding');

        $createdFiles = [];

        // ── Entity ─────────────────────────────────────────────────
        $this->copyScaffoldFile('Entity/User.php', 'src/Entity/User.php', $io, $force, $createdFiles);

        // ── Form Types ─────────────────────────────────────────────
        $this->copyScaffoldFile('Form/RegistrationFormType.php', 'src/Form/RegistrationFormType.php', $io, $force, $createdFiles);
        $this->copyScaffoldFile('Form/ResetPasswordRequestFormType.php', 'src/Form/ResetPasswordRequestFormType.php', $io, $force, $createdFiles);
        $this->copyScaffoldFile('Form/ChangePasswordFormType.php', 'src/Form/ChangePasswordFormType.php', $io, $force, $createdFiles);

        // ── Controllers ────────────────────────────────────────────
        $this->copyScaffoldFile('Controller/Auth/LoginController.php', 'src/Controller/Auth/LoginController.php', $io, $force, $createdFiles);
        $this->copyScaffoldFile('Controller/Auth/RegistrationController.php', 'src/Controller/Auth/RegistrationController.php', $io, $force, $createdFiles);
        $this->copyScaffoldFile('Controller/Auth/ResetPasswordController.php', 'src/Controller/Auth/ResetPasswordController.php', $io, $force, $createdFiles);

        if ($enableGoogle) {
            $this->copyScaffoldFile('Controller/Auth/GoogleController.php', 'src/Controller/Auth/GoogleController.php', $io, $force, $createdFiles);
        }

        // ── Security Authenticators ────────────────────────────────
        $this->copyScaffoldFile('Security/LoginFormAuthenticator.php', 'src/Security/LoginFormAuthenticator.php', $io, $force, $createdFiles);

        if ($enableGoogle) {
            $this->copyScaffoldFile('Security/GoogleAuthenticator.php', 'src/Security/GoogleAuthenticator.php', $io, $force, $createdFiles);
        }

        // ── Templates ──────────────────────────────────────────────
        $this->copyTemplateFile('templates/auth/login.html.twig', 'templates/auth/login.html.twig', $io, $force, $enableGoogle, $createdFiles);
        $this->copyTemplateFile('templates/auth/register.html.twig', 'templates/auth/register.html.twig', $io, $force, $enableGoogle, $createdFiles);
        $this->copyScaffoldFile('templates/auth/forgot_password.html.twig', 'templates/auth/forgot_password.html.twig', $io, $force, $createdFiles);
        $this->copyScaffoldFile('templates/auth/check_email.html.twig', 'templates/auth/check_email.html.twig', $io, $force, $createdFiles);
        $this->copyScaffoldFile('templates/auth/reset_password.html.twig', 'templates/auth/reset_password.html.twig', $io, $force, $createdFiles);
        $this->copyScaffoldFile('templates/auth/email/verification.html.twig', 'templates/auth/email/verification.html.twig', $io, $force, $createdFiles);
        $this->copyScaffoldFile('templates/auth/email/reset_password.html.twig', 'templates/auth/email/reset_password.html.twig', $io, $force, $createdFiles);

        // ── Config: security.yaml ──────────────────────────────────
        $this->generateSecurityConfig($enableGoogle, $io, $force, $createdFiles);

        // ── Config: knpu_oauth2_client.yaml ────────────────────────
        if ($enableGoogle) {
            $this->generateKnpuConfig($io, $force, $createdFiles);
        }

        // ── Config: framework.yaml parameters ──────────────────────
        $this->ensureAppParameters($fromEmail, $io);

        // ── .env: Google OAuth vars ────────────────────────────────
        if ($enableGoogle) {
            $this->appendGoogleEnvVars($io);
        }

        // ── Route loading check ────────────────────────────────────
        $this->checkRouteLoading($io);

        // ── Summary ────────────────────────────────────────────────
        $io->newLine();
        $io->success('Authentication scaffolding installed successfully!');

        if (\count($createdFiles) > 0) {
            $io->section('Created files');
            $io->listing($createdFiles);
        }

        $io->section('Next steps');
        $io->listing([
            'Run <comment>php bin/console make:migration</comment> and <comment>php bin/console doctrine:migrations:migrate</comment>',
            'Configure <comment>MAILER_DSN</comment> in <comment>.env</comment> for email verification & password reset',
            $enableGoogle
                ? 'Set <comment>GOOGLE_CLIENT_ID</comment> and <comment>GOOGLE_CLIENT_SECRET</comment> in <comment>.env.local</comment>'
                : 'Google OAuth is disabled. Run <comment>auth:install</comment> again to enable it.',
            'Add an <comment>app_home</comment> route (controllers redirect there after login)',
            sprintf('Set <comment>app.from_email</comment> parameter if you want a different sender than <comment>%s</comment>', $fromEmail),
        ]);

        $io->section('Available routes');
        $routes = [
            ['<comment>GET</comment>', '/login', 'app_login'],
            ['<comment>GET</comment>', '/logout', 'app_logout'],
            ['<comment>GET|POST</comment>', '/register', 'app_register'],
            ['<comment>GET</comment>', '/verify/email', 'app_verify_email'],
            ['<comment>GET|POST</comment>', '/reset-password', 'app_forgot_password_request'],
            ['<comment>GET</comment>', '/reset-password/check-email', 'app_check_email'],
            ['<comment>GET|POST</comment>', '/reset-password/reset/{token}', 'app_reset_password'],
        ];

        if ($enableGoogle) {
            $routes[] = ['<comment>GET</comment>', '/connect/google', 'connect_google_start'];
            $routes[] = ['<comment>GET</comment>', '/connect/google/check', 'connect_google_check'];
        }

        $io->table(['Method', 'Path', 'Name'], $routes);

        return Command::SUCCESS;
    }

    // ── Helpers ─────────────────────────────────────────────────────

    private function displayBanner(SymfonyStyle $io): void
    {
        $io->newLine();
        $io->writeln([
            ' <fg=magenta>╔══════════════════════════════════════════╗</>',
            ' <fg=magenta>║</> <fg=white;options=bold>  Symfony Auth Starter</> <fg=magenta>║</>',
            ' <fg=magenta>║</> <fg=gray>  Authentication scaffolding for Symfony</> <fg=magenta>║</>',
            ' <fg=magenta>╚══════════════════════════════════════════╝</>',
        ]);
        $io->newLine();
    }

    private function checkPrerequisites(SymfonyStyle $io): bool
    {
        $missing = [];

        $bundles = [
            'Symfony\Bundle\SecurityBundle\SecurityBundle' => 'symfony/security-bundle',
            'Doctrine\Bundle\DoctrineBundle\DoctrineBundle' => 'doctrine/doctrine-bundle',
            'Symfony\Bundle\TwigBundle\TwigBundle' => 'symfony/twig-bundle',
            'SymfonyCasts\Bundle\VerifyEmail\SymfonyCastsVerifyEmailBundle' => 'symfonycasts/verify-email-bundle',
            'SymfonyCasts\Bundle\ResetPassword\SymfonyCastsResetPasswordBundle' => 'symfonycasts/reset-password-bundle',
        ];

        foreach ($bundles as $class => $package) {
            if (!class_exists($class)) {
                $missing[] = $package;
            }
        }

        // Check Mailer component
        if (!class_exists('Symfony\Component\Mailer\Mailer')) {
            $missing[] = 'symfony/mailer';
        }

        if (\count($missing) > 0) {
            $io->error('Missing required packages:');
            $io->listing($missing);
            $io->writeln('Install them with:');
            $io->writeln(sprintf('  <comment>composer require %s</comment>', implode(' ', $missing)));

            return false;
        }

        $io->writeln(' <fg=green>✓</> All prerequisites met');

        return true;
    }

    private function checkGooglePrerequisites(SymfonyStyle $io): bool
    {
        $missing = [];

        if (!class_exists('KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle')) {
            $missing[] = 'knpuniversity/oauth2-client-bundle';
        }

        if (!class_exists('League\OAuth2\Client\Provider\Google')) {
            $missing[] = 'league/oauth2-google';
        }

        if (\count($missing) > 0) {
            $io->warning('Google OAuth requires additional packages:');
            $io->listing($missing);
            $io->writeln(sprintf('  <comment>composer require %s</comment>', implode(' ', $missing)));
            $io->writeln('  Skipping Google OAuth for now.');
            $io->newLine();

            return false;
        }

        return true;
    }

    private function copyScaffoldFile(string $source, string $dest, SymfonyStyle $io, bool $force, array &$createdFiles): bool
    {
        $sourcePath = $this->scaffoldDir . '/' . $source;
        $destPath = $this->projectDir . '/' . $dest;

        if (!$this->filesystem->exists($sourcePath)) {
            $io->warning(sprintf('Scaffold file not found: %s', $source));

            return false;
        }

        if ($this->filesystem->exists($destPath) && !$force) {
            $io->writeln(sprintf(' <fg=yellow>→</> Skipped <comment>%s</comment> (already exists, use --force to overwrite)', $dest));

            return false;
        }

        $this->filesystem->mkdir(\dirname($destPath));
        $this->filesystem->copy($sourcePath, $destPath, true);
        $io->writeln(sprintf(' <fg=green>✓</> Created <comment>%s</comment>', $dest));
        $createdFiles[] = $dest;

        return true;
    }

    private function copyTemplateFile(string $source, string $dest, SymfonyStyle $io, bool $force, bool $enableGoogle, array &$createdFiles): bool
    {
        $sourcePath = $this->scaffoldDir . '/' . $source;
        $destPath = $this->projectDir . '/' . $dest;

        if (!$this->filesystem->exists($sourcePath)) {
            $io->warning(sprintf('Scaffold file not found: %s', $source));

            return false;
        }

        if ($this->filesystem->exists($destPath) && !$force) {
            $io->writeln(sprintf(' <fg=yellow>→</> Skipped <comment>%s</comment> (already exists, use --force to overwrite)', $dest));

            return false;
        }

        $content = file_get_contents($sourcePath);

        if (!$enableGoogle) {
            $content = $this->stripGoogleOAuthSections($content);
        }

        $this->filesystem->mkdir(\dirname($destPath));
        $this->filesystem->dumpFile($destPath, $content);
        $io->writeln(sprintf(' <fg=green>✓</> Created <comment>%s</comment>', $dest));
        $createdFiles[] = $dest;

        return true;
    }

    private function stripGoogleOAuthSections(string $content): string
    {
        // Remove Twig comment markers: {# GOOGLE_OAUTH_START #} ... {# GOOGLE_OAUTH_END #}
        $content = preg_replace(
            '/\s*\{# GOOGLE_OAUTH_START #\}.*?\{# GOOGLE_OAUTH_END #\}/s',
            '',
            $content
        );

        // Remove CSS comment markers: /* GOOGLE_OAUTH_CSS_START */ ... /* GOOGLE_OAUTH_CSS_END */
        $content = preg_replace(
            '/\s*\/\* GOOGLE_OAUTH_CSS_START \*\/.*?\/\* GOOGLE_OAUTH_CSS_END \*\//s',
            '',
            $content
        );

        return $content;
    }

    private function generateSecurityConfig(bool $googleOAuth, SymfonyStyle $io, bool $force, array &$createdFiles): void
    {
        $destPath = $this->projectDir . '/config/packages/security.yaml';

        $authenticators = "                    - App\\Security\\LoginFormAuthenticator";
        if ($googleOAuth) {
            $authenticators .= "\n                    - App\\Security\\GoogleAuthenticator";
        }

        $config = <<<YAML
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        main:
            lazy: true
            provider: app_user_provider

            form_login:
                login_path: app_login
                check_path: app_login
                enable_csrf: true

            custom_authenticators:
{$authenticators}
            entry_point: App\Security\LoginFormAuthenticator

            logout:
                path: app_logout
                target: app_login

            remember_me:
                secret: '%kernel.secret%'
                lifetime: 604800 # 1 week

    access_control:
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/register, roles: PUBLIC_ACCESS }
        - { path: ^/reset-password, roles: PUBLIC_ACCESS }
        - { path: ^/connect, roles: PUBLIC_ACCESS }
        - { path: ^/verify, roles: IS_AUTHENTICATED_FULLY }

YAML;

        if ($this->filesystem->exists($destPath) && !$force) {
            $io->writeln(' <fg=yellow>→</> Skipped <comment>config/packages/security.yaml</comment> (already exists)');
            $io->note('Add the following to your existing security.yaml:');
            $io->writeln($config);

            return;
        }

        $this->filesystem->mkdir(\dirname($destPath));
        $this->filesystem->dumpFile($destPath, $config);
        $io->writeln(' <fg=green>✓</> Created <comment>config/packages/security.yaml</comment>');
        $createdFiles[] = 'config/packages/security.yaml';
    }

    private function generateKnpuConfig(SymfonyStyle $io, bool $force, array &$createdFiles): void
    {
        $destPath = $this->projectDir . '/config/packages/knpu_oauth2_client.yaml';

        $config = <<<'YAML'
knpu_oauth2_client:
    clients:
        google:
            type: google
            client_id: '%env(GOOGLE_CLIENT_ID)%'
            client_secret: '%env(GOOGLE_CLIENT_SECRET)%'
            redirect_route: connect_google_check
            redirect_params: {}

YAML;

        if ($this->filesystem->exists($destPath) && !$force) {
            $io->writeln(' <fg=yellow>→</> Skipped <comment>config/packages/knpu_oauth2_client.yaml</comment> (already exists)');

            return;
        }

        $this->filesystem->mkdir(\dirname($destPath));
        $this->filesystem->dumpFile($destPath, $config);
        $io->writeln(' <fg=green>✓</> Created <comment>config/packages/knpu_oauth2_client.yaml</comment>');
        $createdFiles[] = 'config/packages/knpu_oauth2_client.yaml';
    }

    private function ensureAppParameters(string $fromEmail, SymfonyStyle $io): void
    {
        $configPath = $this->projectDir . '/config/services.yaml';

        if (!$this->filesystem->exists($configPath)) {
            $io->note(sprintf(
                'Add these parameters to your services.yaml: app.from_email: \'%s\', app.from_name: \'My App\'',
                $fromEmail,
            ));

            return;
        }

        $content = file_get_contents($configPath);

        if (str_contains($content, 'app.from_email')) {
            return;
        }

        $io->note(sprintf(
            "Add these parameters to config/services.yaml under 'parameters:':\n    app.from_email: '%s'\n    app.from_name: 'My App'",
            $fromEmail,
        ));
    }

    private function appendGoogleEnvVars(SymfonyStyle $io): void
    {
        $envPath = $this->projectDir . '/.env';

        if (!$this->filesystem->exists($envPath)) {
            $io->note('Create a .env file with GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET');

            return;
        }

        $content = file_get_contents($envPath);
        $additions = [];

        if (!str_contains($content, 'GOOGLE_CLIENT_ID')) {
            $additions[] = 'GOOGLE_CLIENT_ID=your-google-client-id';
        }

        if (!str_contains($content, 'GOOGLE_CLIENT_SECRET')) {
            $additions[] = 'GOOGLE_CLIENT_SECRET=your-google-client-secret';
        }

        if (\count($additions) > 0) {
            $append = "\n###> vraith/symfony-auth-starter ###\n"
                . implode("\n", $additions)
                . "\n###< vraith/symfony-auth-starter ###\n";

            $this->filesystem->appendToFile($envPath, $append);
            $io->writeln(' <fg=green>✓</> Added Google OAuth env vars to <comment>.env</comment>');
        }
    }

    private function checkRouteLoading(SymfonyStyle $io): void
    {
        $routesPath = $this->projectDir . '/config/routes.yaml';

        if (!$this->filesystem->exists($routesPath)) {
            $io->note('Ensure attribute route loading is configured for App\\Controller\\');

            return;
        }

        $content = file_get_contents($routesPath);

        if (str_contains($content, 'App\\Controller\\') || str_contains($content, "App\\Controller\\")) {
            return;
        }

        $io->note(
            "Add attribute route loading to config/routes.yaml:\n\n"
            . "controllers:\n"
            . "    resource:\n"
            . "        path: ../src/Controller/\n"
            . "        namespace: App\\Controller\n"
            . "    type: attribute\n"
        );
    }
}
