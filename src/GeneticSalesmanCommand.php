<?php
namespace EAMann\Machines;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeneticSalesmanCommand extends Command
{
	protected static $defaultName = 'genetic:salesman';

	protected function configure()
	{
		$this
			->setDescription('Runs our traveling salesman simulation using a genetic algorithm.')
			->setHelp('Runs our traveling salesman simulation using a genetic algorithm.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Create an initial population
        $population = new Salesman\Population(0.1);
        $population->initialize(30);
        $kernel = new Kernel($population);

        $initialDistance = $population->fitness($kernel->bestGenome());

		$startTime = time();
		foreach(range(1, 2500) as $i) {
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
		$output->write("\n\nSalesman are done!");
		$output->write(sprintf("\nInit Fitness: %d", $initialDistance));
		$output->write(sprintf("\nBest Fitness: %d", $population->fitness($kernel->bestGenome())));
		$output->write(sprintf("\nEverything completed in %s seconds.\n", $totalTime));

		// Done
		return 0;
	}
}