<?php

declare(strict_types=1);

namespace ComposerIntegration\Util;

use Composer\Composer;

final class CacheHelper
{
    private const INTEGRATION_CACHE_DIR_NAME = 'integration';

    private string $integrationCacheDir;
    private string $integrationComposerFile;

    private function __construct(
        private Composer $composer,
        private string $mainComposerFile,
        private string $integration,
    ) {
        $this->initIntegration();
    }

    public static function create(Composer $composer, string $mainComposerFile, string $integration): self
    {
        return new self($composer, $mainComposerFile, $integration);
    }

    public function cacheIntegrationIfNecessary(array $integrationRequired): void
    {
        $mergedComposer = ComposerHelper::getMergedIntegrationComposer($this->composer, $this->integration);
        if (!$this->hasCacheForIntegration()) {
            $this->cacheIntegration($mergedComposer);
        } elseif ($this->cacheDiffers($integrationRequired)) {
            FileSystemHelper::removeDirectory($this->integrationCacheDir);
            $this->cacheIntegration($mergedComposer);
        }
    }

    public function getCachedComposerFile(): string
    {
        return $this->integrationComposerFile;
    }

    private function cacheIntegration(string $mergedComposer): void
    {
        if (!mkdir($this->integrationCacheDir, 0755, true) && !is_dir($this->integrationCacheDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $this->integrationCacheDir));
        }

        file_put_contents($this->integrationComposerFile, $mergedComposer);
        chmod($this->integrationComposerFile, 0755);
    }

    private function cacheDiffers(array $integrationRequires): bool
    {
        $cacheRequired = ComposerHelper::decodeComposer($this->integrationComposerFile)['require'] ?? [];
        $originalRequired = ComposerHelper::decodeComposer($this->mainComposerFile)['require'] ?? [];

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

    private function hasCacheForIntegration(): bool
    {
        return file_exists($this->integrationCacheDir);
    }

    private function initIntegration(): void
    {
        $cacheDir = $this->composer->getConfig()->get('cache-dir');

        $this->integrationCacheDir = vsprintf('%s%s%s%s%s', [
            $cacheDir ,
            DIRECTORY_SEPARATOR ,
            self::INTEGRATION_CACHE_DIR_NAME ,
            DIRECTORY_SEPARATOR ,
            $this->integration,
        ]);

        $this->integrationComposerFile = sprintf('%s%scomposer.json', $this->integrationCacheDir, DIRECTORY_SEPARATOR);
    }
}
