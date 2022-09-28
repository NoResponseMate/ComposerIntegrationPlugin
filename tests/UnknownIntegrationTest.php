<?php

declare(strict_types=1);

namespace Tests\ComposerIntegration;

final class UnknownIntegrationTest extends FixtureAwareComposerTestCase
{
    private const FIXTURE_DIR = __DIR__ . DIRECTORY_SEPARATOR .'fixtures' . DIRECTORY_SEPARATOR . 'with-one';

    public function testMultipleIntegrationWithMultipleRequirements(): void
    {
        [, $status] = $this->runIntegration('first');
        $this->assertEquals(1, $status);
    }

    protected function getFixtureDir(): string
    {
        return self::FIXTURE_DIR;
    }
}
