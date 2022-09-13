<?php

declare(strict_types=1);

namespace Tests\ComposerIntegration;

final class WithLocalEnvTest extends FixtureAwareComposerTestCase
{
    private const FIXTURE_DIR = __DIR__ . DIRECTORY_SEPARATOR .'fixtures' . DIRECTORY_SEPARATOR . 'with-local-env';

    public function testIntegrationWithEnvDifferentThanInLocalEnv(): void
    {
        [, $status] = $this->runIntegration('with-different-env');
        $this->assertEquals(0, $status);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('psr/http-message');
        $this->assertFileExists(self::TEMP_DIR . DIRECTORY_SEPARATOR . '.env');
        $this->assertFileExists(self::TEMP_DIR . DIRECTORY_SEPARATOR . '.env.local');
        $this->assertFileEquals(
            self::TEMP_DIR . DIRECTORY_SEPARATOR . 'generated_local_env_content_using_different_env',
            self::TEMP_DIR . DIRECTORY_SEPARATOR . '.env.local',
        );
    }

    public function testIntegrationWithEnvSameAsInLocalEnv(): void
    {
        [, $status] = $this->runIntegration('with-same-env');
        $this->assertEquals(0, $status);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('psr/http-message');
        $this->assertFileExists(self::TEMP_DIR . DIRECTORY_SEPARATOR . '.env');
        $this->assertFileExists(self::TEMP_DIR . DIRECTORY_SEPARATOR . '.env.local');
        $this->assertFileEquals(
            self::TEMP_DIR . DIRECTORY_SEPARATOR . 'generated_local_env_content_using_same_env',
            self::TEMP_DIR . DIRECTORY_SEPARATOR . '.env.local',
        );
    }

    protected function getFixtureDir(): string
    {
        return self::FIXTURE_DIR;
    }
}
