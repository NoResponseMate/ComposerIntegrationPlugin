<?php

declare(strict_types=1);

namespace Tests\ComposerIntegration;

final class WithoutEnvTest extends FixtureAwareComposerTestCase
{
    private const FIXTURE_DIR = __DIR__ . DIRECTORY_SEPARATOR .'fixtures' . DIRECTORY_SEPARATOR . 'without-env';

    public function testOneIntegrationWithoutEnv(): void
    {
        [, $status] = $this->runIntegration('with-env');
        $this->assertEquals(0, $status);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('psr/http-message');
        $this->assertFileDoesNotExist(self::TEMP_DIR . DIRECTORY_SEPARATOR . '.env');
        $this->assertFileDoesNotExist(self::TEMP_DIR . DIRECTORY_SEPARATOR . '.env.local');
    }

    protected function getFixtureDir(): string
    {
        return self::FIXTURE_DIR;
    }
}
