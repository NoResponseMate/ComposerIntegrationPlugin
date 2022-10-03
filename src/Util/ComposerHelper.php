<?php

declare(strict_types=1);

namespace ComposerIntegration\Util;

use Composer\Composer;
use Composer\Factory;

final class ComposerHelper
{
    public static function getMergedIntegrationComposer(Composer $composer, string $integration): string
    {
        $originalComposerPath = Factory::getComposerFile();
        $originalComposerContent = self::decodeComposer($originalComposerPath);
        $integrationRequired = self::getIntegrationRequired($composer, $integration);

        foreach ($integrationRequired as $package => $version) {
            $originalComposerContent['require'][$package] = $version;
        }

        return (string) json_encode($originalComposerContent);
    }

    public static function decodeComposer(string $path): array
    {
        return json_decode((string) file_get_contents($path), true);
    }

    public static function getIntegrationRequired(Composer $composer, string $integration): array
    {
        return self::getIntegrationConfig($composer, $integration)['require'] ?? [];
    }

    public static function getIntegrationEnv(Composer $composer, string $integration): string
    {
        return self::getIntegrationConfig($composer, $integration)['env'] ?? '';
    }

    public static function getEnvDirectory(Composer $composer, string $workingDir): string
    {
        $customEnvDir = self::getIntegrationAdditionalOptions($composer)['env-path'] ?? null;
        if (null === $customEnvDir) {
            return $workingDir;
        }

        return (string) realpath($workingDir . DIRECTORY_SEPARATOR . ltrim($customEnvDir, DIRECTORY_SEPARATOR));
    }

    private static function getIntegrationAdditionalOptions(Composer $composer): array
    {
        return $composer->getPackage()->getExtra()['integration-options'] ?? [];
    }

    private static function getIntegrationConfig(Composer $composer, string $integration): array
    {
        return $composer->getPackage()->getExtra()['integration'][$integration] ?? [];
    }
}
