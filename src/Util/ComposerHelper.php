<?php

declare(strict_types=1);

namespace ComposerIntegration\Util;

use Composer\Composer;
use Composer\Factory;

final class ComposerHelper
{
    public static function decodeComposer(string $path): array
    {
        return json_decode(file_get_contents($path), true);
    }

    public static function getMergedIntegrationComposer(Composer $composer, string $integration): string
    {
        $originalComposerPath = Factory::getComposerFile();
        $originalComposerContent = self::decodeComposer($originalComposerPath);
        $integrationRequired = self::getIntegrationRequired($composer, $integration);

        foreach ($integrationRequired as $packageName => $version) {
            $originalComposerContent['require'][$packageName] = $version;
        }

        return json_encode($originalComposerContent);
    }

    public static function getIntegrationRequired(Composer $composer, string $integration): array
    {
        return $composer->getPackage()->getExtra()['integration'][$integration]['require'] ?? [];
    }
}
