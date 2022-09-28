<?php

declare(strict_types=1);

namespace Tests\ComposerIntegration;

use Composer\Util\Platform;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class FixtureAwareComposerTestCase extends TestCase
{
    public const TEMP_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'temp';
    private const TEMP_VENDOR = self::TEMP_DIR . DIRECTORY_SEPARATOR . 'vendor';
    private const COMPOSER_COMMAND_TEMPLATE = 'composer integration %s -n --working-dir="' . self::TEMP_DIR . '" %s';

    private Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        if (!is_dir(self::TEMP_DIR) && !mkdir($concurrentDirectory = self::TEMP_DIR) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        Platform::putEnv('COLUMNS', '120');

        $this->filesystem = new Filesystem();

        chmod(self::TEMP_DIR, 0755);

        $fixtureDir = $this->getFixtureDir();

        $this->filesystem->mirror($fixtureDir . DIRECTORY_SEPARATOR, self::TEMP_DIR . DIRECTORY_SEPARATOR);

        $this->replacePlaceholderVersion($this->getVersion());
    }

    public function tearDown(): void
    {
        $this->filesystem->remove(self::TEMP_DIR);

        parent::tearDown();
    }

    public function runCleanComposer(): void
    {
        @exec(sprintf('composer -n install --working-dir="%s" 2>&1', self::TEMP_DIR));
    }

    public function runIntegration(string $integration, array $options = []): array
    {
        $optionsString = implode(' ', $options);
        exec(sprintf(self::COMPOSER_COMMAND_TEMPLATE, $integration, $optionsString) . ' 2>&1', $output, $status);

        $output = implode(PHP_EOL, $output);
        if ($status) {
            fwrite(STDERR, $output);
        }

        return [$output, $status];
    }

    public function assertComposerFilesCreated(): void
    {
        $this->assertFileExists(self::TEMP_DIR . DIRECTORY_SEPARATOR . 'composer.json');
        $this->assertFileExists(self::TEMP_DIR . DIRECTORY_SEPARATOR . 'composer.lock');
        $this->assertDirectoryExists(self::TEMP_VENDOR);
    }

    public function assertPackageInstalled(string $packageName): void
    {
        [$namespace, $package] = explode('/', $packageName);

        $this->assertDirectoryExists(self::TEMP_VENDOR . DIRECTORY_SEPARATOR . $namespace);
        $this->assertDirectoryExists(
            self::TEMP_VENDOR . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $package
        );
    }

    public function assertPackageNotInstalled(string $packageName): void
    {
        [$namespace, $package] = explode('/', $packageName);

        $this->assertDirectoryDoesNotExist(
            self::TEMP_VENDOR . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $package
        );
    }

    abstract protected function getFixtureDir(): string;

    private function getVersion(): string
    {
        $version = exec('git rev-parse --abbrev-ref HEAD');
        if ('HEAD' === $version) {
            return '*';
        }

        return 'dev-' . $version;
    }

    private function replacePlaceholderVersion(string $version): void
    {
        $tempComposer = self::TEMP_DIR . DIRECTORY_SEPARATOR . 'composer.json';
        $contents = file_get_contents($tempComposer);
        $contents = strtr($contents, ['%VERSION%' => $version]);
        file_put_contents($tempComposer, $contents);
    }
}
