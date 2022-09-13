<?php

declare(strict_types=1);

namespace ComposerIntegration\Util;

final class FileSystemHelper
{
    public static function removeDirectory(string $directory): void
    {
        self::callForEachFileInDir(
            static fn (string $file) => unlink($directory . DIRECTORY_SEPARATOR . $file),
            $directory,
        );

        rmdir($directory);
    }

    public static function callForEachFileInDir(callable $call, string $dir): void
    {
        $dirResource = opendir($dir);
        if (false === $dirResource) {
            throw new \RuntimeException('Directory "' . $dir . '" cannot be opened.');
        }
        while (false !== $file = readdir($dirResource)) {
            if ('.' === $file || '..' === $file) {
                continue;
            }

            $call($file);
        }

        closedir($dirResource);
    }
}
