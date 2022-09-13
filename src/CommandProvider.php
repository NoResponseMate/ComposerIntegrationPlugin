<?php

declare(strict_types=1);

namespace ComposerIntegration;

use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use ComposerIntegration\Command\IntegrationCommand;

final class CommandProvider implements ComposerCommandProvider
{
    public function getCommands(): array
    {
        return [
            new IntegrationCommand(),
        ];
    }
}
