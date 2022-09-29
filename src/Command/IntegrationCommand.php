<?php

declare(strict_types=1);

namespace ComposerIntegration\Command;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Composer\Util\Platform;
use ComposerIntegration\Util\CacheHelper;
use ComposerIntegration\Util\ComposerHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class IntegrationCommand extends BaseCommand
{
    public const NAME = 'integration';
    private const ARGUMENT_NAME = 'integration_name';

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Install an integration\'s dependencies.')
            ->setDefinition(new InputDefinition([
                new InputArgument(self::ARGUMENT_NAME, InputArgument::REQUIRED),
            ]));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $integration = (string) $input->getArgument(self::ARGUMENT_NAME);

        $composer = $this->requireComposer();

        $integrationRequired = ComposerHelper::getIntegrationRequired($composer, $integration);
        if ([] === $integrationRequired) {
            $output->writeln('Integration "' . $integration . '" is not configured.');

            return 1;
        }

        $cache = CacheHelper::create($composer, Factory::getComposerFile(), $integration);
        $cache->cacheIntegrationIfNecessary($integrationRequired);

        $integrationComposer = $cache->getCachedComposerFile();

        passthru(
            sprintf('COMPOSER="%s" composer -n install --working-dir="%s"', $integrationComposer, Platform::getCwd()),
            $status,
        );

        return $status;
    }
}
