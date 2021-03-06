<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/boot project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Boot\Console\Command;

use Daikon\Boot\Crate\CrateMap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ListCrates extends Command
{
    private CrateMap $crateMap;

    public function __construct(CrateMap $crateMap)
    {
        $this->crateMap = $crateMap;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('crate:ls')
            ->setDescription('Lists currently installed crates.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->crateMap as $crateKey => $crate) {
            $output->writeln(sprintf('Summary for crate <options=bold>%s</>', $crateKey));
            $output->writeln('  Location: '.$crate->getLocation());
        }

        return 0;
    }
}
