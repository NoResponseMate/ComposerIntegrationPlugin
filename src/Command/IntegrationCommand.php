<?php

declare(strict_types=1);

namespace ComposerIntegration\Command;

use Composer\Command\BaseCommand;
use Composer\Composer;
use Composer\Factory;
use Composer\Util\Platform;
use ComposerIntegration\Util\ComposerHelper;
use ComposerIntegration\Util\FileSystemHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class IntegrationCommand extends BaseCommand
{
    public const NAME = 'integration';
    private const ARGUMENT_NAME = 'integration_name';

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Install an integration\'s dependencies.')
            ->setDefinition(new InputDefinition([
                new InputArgument(self::ARGUMENT_NAME, InputArgument::REQUIRED),
            ]));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $integration = (string) $input->getArgument(self::ARGUMENT_NAME);

        $composer = $this->requireComposer();
        $composerJsonPath = Factory::getComposerFile();

        $integrationCacheDir = self::getIntegrationCacheDir($composer, $integration);
        $integrationComposer = $integrationCacheDir . DIRECTORY_SEPARATOR . 'composer.json';

        $integrationRequired = ComposerHelper::getIntegrationRequired($composer, $integration);
        if ([] === $integrationRequired) {
            $output->writeln('Integration "' . $integration . '" is not configured.');

            return 1;
        }

        $mergedComposer = ComposerHelper::getMergedIntegrationComposer($composer, $integration);
        if (!self::hasCacheForIntegration($integrationCacheDir)) {
            self::cacheIntegration($integrationCacheDir, $integrationComposer, $mergedComposer);
        } elseif (self::cacheDiffers($integrationComposer, $composerJsonPath, $integrationRequired)) {
            FileSystemHelper::removeDirectory($integrationCacheDir);
            self::cacheIntegration($integrationCacheDir, $integrationComposer, $mergedComposer);
        }

        passthru(
            sprintf('COMPOSER="%s" composer -n install --working-dir="%s"', $integrationComposer, Platform::getCwd()),
            $status,
        );

        return $status;
    }

    private static function cacheIntegration(
        string $cacheDir,
        string $cacheFile,
        string $mergedComposer,
    ): void {
        if (!mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $cacheDir));
        }

        file_put_contents($cacheFile, $mergedComposer);
        chmod($cacheFile, 0755);
    }

    private static function cacheDiffers(string $cacheFile, string $originalFile, array $integrationRequires): bool
    {
        $cacheRequired = ComposerHelper::decodeComposer($cacheFile)['require'] ?? [];
        $originalRequired = ComposerHelper::decodeComposer($originalFile)['require'] ?? [];

        foreach ($integrationRequires as $package => $version) {
            $originalRequired[$package] = $version;
        }

        foreach ($originalRequired as $package => $version) {
            if ($version !== ($cacheRequired[$package] ?? '')) {
                return true;
            }
        }

        foreach ($cacheRequired as $package => $version) {
            if ($version !== ($originalRequired[$package] ?? '')) {
                return true;
            }
        }

        return false;
    }

    private static function hasCacheForIntegration(string $dir): bool
    {
        return file_exists($dir);
    }

    private static function getIntegrationCacheDir(Composer $composer, string $integration): string
    {
        $cacheDir = $composer->getConfig()->get('cache-dir');

        return $cacheDir . DIRECTORY_SEPARATOR . self::NAME . DIRECTORY_SEPARATOR . $integration;
    }
}
