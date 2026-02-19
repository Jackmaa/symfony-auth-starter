<?php

declare(strict_types=1);

namespace Valentin\AuthStarter\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Installation command for Auth Starter bundle.
 * 
 * This command sets up the entire authentication system:
 * - Copies User entity
 * - Copies controllers
 * - Copies templates
 * - Generates configuration files
 * - Configures security.yaml
 */
#[AsCommand(
    name: 'auth:install',
    description: 'Install and configure Symfony Auth Starter',
)]
class InstallCommand extends Command
{
    private Filesystem $filesystem;
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        parent::__construct();
        $this->filesystem = new Filesystem();
        $this->projectDir = $projectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔐 Symfony Auth Starter Installation');

        // Step 1: Check prerequisites
        $io->section('Checking prerequisites...');
        if (!$this->checkPrerequisites($io)) {
            return Command::FAILURE;
        }

        // Step 2: Copy User entity
        $io->section('Setting up User entity...');
        $this->copyUserEntity($io);

        // Step 3: Copy controllers
        $io->section('Installing controllers...');
        $this->copyControllers($io);

        // Step 4: Copy templates
        $io->section('Installing templates...');
        $this->copyTemplates($io);

        // Step 5: Generate configuration
        $io->section('Configuring authentication...');
        $this->generateConfiguration($io);

        // Step 6: Configure routes
        $io->section('Setting up routes...');
        $this->configureRoutes($io);

        // Step 7: Final instructions
        $io->success('✅ Auth Starter installed successfully!');

        $io->block([
            'Next steps:',
            '',
            '1. Set up your environment variables in .env:',
            '   GOOGLE_CLIENT_ID=your-client-id',
            '   GOOGLE_CLIENT_SECRET=your-client-secret',
            '',
            '2. Run migrations:',
            '   php bin/console doctrine:migrations:diff',
            '   php bin/console doctrine:migrations:migrate',
            '',
            '3. Available routes:',
            '   /login - Login page',
            '   /register - Registration page',
            '   /connect/google - Google OAuth',
            '   /forgot-password - Password reset',
            '',
            '4. Customize configuration in:',
            '   config/packages/auth_starter.yaml',
        ], 'INFO', 'fg=blue', ' ', true);

        return Command::SUCCESS;
    }

    private function checkPrerequisites(SymfonyStyle $io): bool
    {
        $checks = [
            'SecurityBundle' => class_exists('Symfony\Bundle\SecurityBundle\SecurityBundle'),
            'DoctrineBundle' => class_exists('Doctrine\Bundle\DoctrineBundle\DoctrineBundle'),
            'TwigBundle' => class_exists('Symfony\Bundle\TwigBundle\TwigBundle'),
        ];

        $allPassed = true;
        foreach ($checks as $name => $passed) {
            if ($passed) {
                $io->writeln("✓ $name is installed");
            } else {
                $io->error("✗ $name is not installed. Please install it first.");
                $allPassed = false;
            }
        }

        return $allPassed;
    }

    private function copyUserEntity(SymfonyStyle $io): void
    {
        $source = __DIR__ . '/../Entity/User.php';
        $destination = $this->projectDir . '/src/Entity/User.php';

        if ($this->filesystem->exists($destination)) {
            if (!$io->confirm('User entity already exists. Overwrite?', false)) {
                $io->note('Skipping User entity copy.');
                return;
            }
        }

        $this->filesystem->copy($source, $destination, true);
        $io->success('User entity created at src/Entity/User.php');
    }

    private function copyControllers(SymfonyStyle $io): void
    {
        $controllersDir = __DIR__ . '/../Controller';
        $destination = $this->projectDir . '/src/Controller/Auth';

        $this->filesystem->mkdir($destination);
        $this->filesystem->mirror($controllersDir, $destination);

        $io->success('Controllers installed in src/Controller/Auth/');
    }

    private function copyTemplates(SymfonyStyle $io): void
    {
        $templatesDir = __DIR__ . '/../Resources/views';
        $destination = $this->projectDir . '/templates/auth';

        $this->filesystem->mkdir($destination);
        $this->filesystem->mirror($templatesDir, $destination);

        $io->success('Templates installed in templates/auth/');
    }

    private function generateConfiguration(SymfonyStyle $io): void
    {
        $configFile = $this->projectDir . '/config/packages/auth_starter.yaml';
        
        if ($this->filesystem->exists($configFile)) {
            $io->note('Configuration file already exists. Skipping.');
            return;
        }

        $config = <<<YAML
auth_starter:
    # Where to redirect after successful login
    redirect_after_login: '/'
    
    # Enable/disable Google OAuth
    enable_google: true
    
    # Auto-verify users who sign up via OAuth
    auto_verify_oauth_users: true
    
    # Email configuration
    from_email: 'noreply@example.com'
    from_name: 'Your App'

YAML;

        $this->filesystem->dumpFile($configFile, $config);
        $io->success('Configuration created at config/packages/auth_starter.yaml');
    }

    private function configureRoutes(SymfonyStyle $io): void
    {
        $routesFile = $this->projectDir . '/config/routes/auth_starter.yaml';
        
        if ($this->filesystem->exists($routesFile)) {
            $io->note('Routes file already exists. Skipping.');
            return;
        }

        $this->filesystem->mkdir(dirname($routesFile));

        $routes = <<<YAML
# Auth Starter Routes
auth_starter:
    resource: '@AuthStarterBundle/Resources/config/routes.yaml'

YAML;

        $this->filesystem->dumpFile($routesFile, $routes);
        $io->success('Routes configured at config/routes/auth_starter.yaml');
    }
}