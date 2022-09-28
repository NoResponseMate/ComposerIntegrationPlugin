<?php

declare(strict_types=1);

namespace Tests\ComposerIntegration;

final class WithMultipleTest extends FixtureAwareComposerTestCase
{
    private const FIXTURE_DIR = __DIR__ . DIRECTORY_SEPARATOR .'fixtures' . DIRECTORY_SEPARATOR . 'with-multiple';

    public function testMultipleIntegrationWithMultipleRequirements(): void
    {
        [, $status] = $this->runIntegration('first');
        $this->assertEquals(0, $status);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('psr/http-factory');
        $this->assertPackageInstalled('league/uri');

        [, $status] = $this->runIntegration('second');
        $this->assertEquals(0, $status);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('psr/link');
        $this->assertPackageNotInstalled('league/uri');
        $this->assertPackageNotInstalled('psr/http-factory');

        [, $status] = $this->runIntegration('third');
        $this->assertEquals(0, $status);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('psr/http-factory');
        $this->assertPackageNotInstalled('psr/link');
    }

    protected function getFixtureDir(): string
    {
        return self::FIXTURE_DIR;
    }
}
