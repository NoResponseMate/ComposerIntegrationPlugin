<?php

declare(strict_types=1);

namespace Tests\ComposerIntegration;

final class WithEnvInCustomDirectoryTest extends FixtureAwareComposerTestCase
{
    private const FIXTURE_DIR = __DIR__ . DIRECTORY_SEPARATOR .'fixtures' . DIRECTORY_SEPARATOR . 'with-env-custom-dir';

    /**
     * @dataProvider getCustomDirectory
     */
    public function testOneIntegrationWithEnv(string $customDirectory): void
    {
        $this->setComposerPlaceholderValue('%CUSTOM_DIR%', $customDirectory);

        [, $status] = $this->runIntegration('custom-dir-env');
        $this->assertEquals(0, $status);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('league/uri');

        $tempCustomEnvDirectory = vsprintf('%s%s%s%s', [
            self::TEMP_DIR,
            DIRECTORY_SEPARATOR,
            'test' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'test',
            DIRECTORY_SEPARATOR,
        ]);

        $this->assertFileExists($tempCustomEnvDirectory . '.env');
        $this->assertFileExists($tempCustomEnvDirectory . '.env.local');
        $this->assertFileEquals(
            self::TEMP_DIR . DIRECTORY_SEPARATOR . 'generated_local_env_content',
            $tempCustomEnvDirectory . '.env.local',
        );
    }

    public function getCustomDirectory(): array
    {
        return [
            ['test/app/test'],
            ['/test/app/test'],
            ['./test/app/test'],
            ['../temp/test/app/test'],
        ];
    }

    protected function getFixtureDir(): string
    {
        return self::FIXTURE_DIR;
    }
}
