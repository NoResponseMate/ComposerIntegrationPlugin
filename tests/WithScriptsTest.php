<?php

declare(strict_types=1);

namespace Tests\ComposerIntegration;

final class WithScriptsTest extends FixtureAwareComposerTestCase
{
    private const FIXTURE_DIR = __DIR__ . DIRECTORY_SEPARATOR .'fixtures' . DIRECTORY_SEPARATOR . 'with-scripts';

    public function testOneIntegrationWithOneRequirementAndNoScripts(): void
    {
        [$output, $status] = $this->runIntegration('with-scripts');
        $this->assertEquals(0, $status);
        $this->assertStringNotContainsString('Test script', $output);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('psr/http-message');
    }

    public function testOneIntegrationWithOneRequirementAndScripts(): void
    {
        [$output, $status] = $this->runIntegration('with-scripts', ['--with-scripts']);
        $this->assertEquals(0, $status);
        $this->assertStringContainsString('Test script', $output);

        $this->assertComposerFilesCreated();
        $this->assertPackageInstalled('psr/http-message');
    }

    protected function getFixtureDir(): string
    {
        return self::FIXTURE_DIR;
    }
}
