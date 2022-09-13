<?php

declare(strict_types=1);

namespace Tests\ComposerIntegration;

final class WithOneTest extends FixtureAwareComposerTestCase
{
    private const FIXTURE_DIR = __DIR__ . DIRECTORY_SEPARATOR .'fixtures' . DIRECTORY_SEPARATOR . 'with-one';

    public function testOneIntegrationWithOneRequirement(): void
    {
        $this->runCleanComposer();

        [, $status] = $this->runIntegration('with-one');
        $this->assertEquals(0, $status);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('psr/http-message');
    }

    protected function getFixtureDir(): string
    {
        return self::FIXTURE_DIR;
    }
}
