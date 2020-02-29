<?php
namespace EAMann\Machines;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MonkeyCommand extends Command
{
	protected static $defaultName = 'genetic:monkeys';

	protected function configure()
	{
		$this
			->setDescription('Runs our monkey typing simulation using a genetic algorithm.')
			->setHelp('Runs our monkey typing simulation using a genetic algorithm.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Create an initial population
        $population = new Monkeys\Population();
        $population->initialize(30);
        $kernel = new Kernel($population);

		$startTime = time();
		$i = 0;
		while ($population->fitness($kernel->bestGenome()) > 0) {
			$i++;
			$kernel = $kernel->next();
			$best = $kernel->bestGenome();

			if ($i % 10 === 0) {
				$genPerSec = floor($i/(time() - $startTime + 1));
				// Print status

				$out = '';
				$out .= "\nGeneration:   " . $i;
				$out .="\nGen / sec:    " . $genPerSec;
				$out .="\nBest Fitness: " . $population->fitness($best);
				$out .= "\n\n" . $best;

				$lines = substr_count($out, "\n");
				$output->write(str_repeat("\x1B[1A\x1B[2K", $lines));
				$output->write($out);
			}
		}

		// Print final status
		$totalTime = (time() - $startTime);
		$output->write("\n\nMonkeys are done!");
		$output->write(sprintf("\nWe ran %d generations!", $i));
		$output->write(sprintf("\nEverything completed in %s seconds.\n", $totalTime));

		// Done
		return 0;
	}
}