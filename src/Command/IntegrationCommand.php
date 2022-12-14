<?php

declare(strict_types=1);

namespace ComposerIntegration\Command;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Composer\Util\Platform;
use ComposerIntegration\Env\EnvHandler;
use ComposerIntegration\Util\CacheHelper;
use ComposerIntegration\Util\ComposerHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class IntegrationCommand extends BaseCommand
{
    public const NAME = 'integration';
    private const ARGUMENT_NAME = 'integration_name';
    private const COMPOSER_BASE_TEMPLATE = 'COMPOSER="%s" composer -n %s --working-dir="%s"';

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Install an integration\'s dependencies.')
            ->setDefinition(new InputDefinition([
                new InputArgument(self::ARGUMENT_NAME, InputArgument::REQUIRED),
                new InputOption('with-scripts', mode: InputOption::VALUE_NONE)
            ]))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $integration = (string) $input->getArgument(self::ARGUMENT_NAME);
        $enableScripts = $input->getOption('with-scripts');

        $composer = $this->requireComposer();

        $integrationRequired = ComposerHelper::getIntegrationRequired($composer, $integration);
        if ([] === $integrationRequired) {
            $output->writeln('Integration "' . $integration . '" is not configured.' . PHP_EOL);

            return 1;
        }

        $cache = CacheHelper::create($composer, Factory::getComposerFile(), $integration);
        $cache->cacheIntegrationIfNecessary($integrationRequired);

        $workingDir = Platform::getCwd();
        $envDir = ComposerHelper::getEnvDirectory($composer, $workingDir);

        $integrationEnv = ComposerHelper::getIntegrationEnv($composer, $integration);

        if ('' !== $integrationEnv && EnvHandler::hasEnv($envDir)) {
            EnvHandler::updateAppEnv($envDir, $integrationEnv);
        }

        $installCommand = sprintf(self::COMPOSER_BASE_TEMPLATE, $cache->getCachedComposerFile(), 'install', $workingDir);

        if (!$enableScripts) {
            $installCommand .= ' --no-scripts';
        }

        passthru($installCommand, $status);

        return $status;
    }
}
