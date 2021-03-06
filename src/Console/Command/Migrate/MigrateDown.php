<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/boot project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Boot\Console\Command\Migrate;

use Daikon\Dbal\Migration\MigrationInterface;
use Daikon\Dbal\Migration\MigrationTargetMap;
use Daikon\Interop\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrateDown extends Command
{
    private MigrationTargetMap $migrationTargetMap;

    public function __construct(MigrationTargetMap $migrationTargetMap)
    {
        $this->migrationTargetMap = $migrationTargetMap;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('migrate:down')
            ->setDescription('Migrate down to a specified migration version.')
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'Name of the target to migrate (if omitted all enabled targets will be migrated).'
            )
            ->addOption(
                'to',
                null,
                InputOption::VALUE_REQUIRED,
                'The version to migrate towards (if omitted all previous migrations will be reversed if possible).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $target = $input->getOption('target');
        $version = intval($input->getOption('to'));

        foreach ($this->migrationTargetMap->getEnabledTargets() as $targetKey => $migrationTarget) {
            if ($target && $target !== $targetKey) {
                continue;
            }
            $output->writeln(sprintf('Reversing migrations for target <options=bold>%s</>', $targetKey));
            try {
                $reversedMigrations = $migrationTarget->migrate(MigrationInterface::MIGRATE_DOWN, $version);
                if ($reversedMigrations->count() > 0) {
                    foreach ($reversedMigrations as $migration) {
                        $output->writeln(sprintf(
                            '  <info>Reversed migration version %d (%s)</info>',
                            $migration->getVersion(),
                            $migration->getName()
                        ));
                    }
                } else {
                    $output->writeln('  <comment>No reversible migrations found</comment>');
                }
            } catch (InvalidArgumentException $exception) {
                $output->writeln('  <error>'.$exception->getMessage().'</error>');
            }
        }

        return 0;
    }
}
