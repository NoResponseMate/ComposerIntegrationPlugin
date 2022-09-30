<?php

declare(strict_types=1);

namespace ComposerIntegration\Env;

final class EnvHandler
{
    private const MAIN_ENV = '.env';
    private const LOCAL_ENV = '.env.local';
    private const ENV_PARAMETER = 'APP_ENV';
    private const NEW_ENV_TEMPLATE = self::ENV_PARAMETER . '=%s' . PHP_EOL;

    public static function updateAppEnv(string $dir, string $newEnv): void
    {
        $localEnvFile = self::getEnvPath($dir, self::LOCAL_ENV);
        if (!self::hasLocalEnv($dir)) {
            copy(self::getEnvPath($dir, self::MAIN_ENV), $localEnvFile);
        }

        self::saveNewEnvParameter($localEnvFile, $newEnv);
    }

    public static function hasEnv(string $dir): bool
    {
        return self::envFileExists($dir, self::MAIN_ENV);
    }

    private static function saveNewEnvParameter(string $file, string $env): void
    {
        $currentEnvFileContent = (string) file_get_contents($file);
        $newContentLines = [];

        $oldLines = explode(PHP_EOL, $currentEnvFileContent);
        foreach ($oldLines as $line) {
            if ('' === trim($line)) {
                continue;
            }
            if (self::sameLocalEnv($line, $env)) {
                return;
            }
            if (str_starts_with($line, self::ENV_PARAMETER)) {
                $line = '#' . $line;
            }

            $newContentLines[] = $line;
        }

        $newContentLines[] = sprintf(self::NEW_ENV_TEMPLATE, $env);

        file_put_contents($file, implode(PHP_EOL, $newContentLines));
    }

    private static function sameLocalEnv(string $currentEnv, string $newEnv): bool
    {
        return str_ends_with(trim($currentEnv, " \t\n\r\0\x0B\"\'"), $newEnv);
    }

    private static function hasLocalEnv(string $dir): bool
    {
        return self::hasEnv($dir) && self::envFileExists($dir, self::LOCAL_ENV);
    }

    private static function envFileExists(string $dir, string $fileName): bool
    {
        return file_exists(self::getEnvPath($dir, $fileName));
    }

    private static function getEnvPath(string $dir, string $fileName): string
    {
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
    }
}
