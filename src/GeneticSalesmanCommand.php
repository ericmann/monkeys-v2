<?php
namespace EAMann\Machines;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeneticSalesmanCommand extends Command
{
    use SocketClient;

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

        $initialBest = $kernel->bestGenome();
        $initialDistance = $population->fitness($initialBest);

        $this->connect();
        $payload = [
        	'generation' => 0,
			'fitness'    => $initialDistance,
			'best'       => (string) $initialBest
		];
        $this->send($payload);

		$startTime = time();
		$generations = 2500;
		foreach(range(1, $generations) as $i) {
			$kernel = $kernel->next();
			$best = $kernel->bestGenome();
			$bestFitness = $population->fitness($best);

			$payload = [
				'generation' => $i,
				'fitness'    => $bestFitness,
				'best'       => (string) $best
			];
			$this->send($payload);
		}

		// Print final status
		$finalBest = $kernel->bestGenome();
		$finalFitness = $population->fitness($finalBest);
		$totalTime = (time() - $startTime);
		$output->write("\n\nSalesman are done!");
		$output->write(sprintf("\nInit Fitness: %d", $initialDistance));
		$output->write(sprintf("\nBest Fitness: %d", $finalFitness));
		$output->write(sprintf("\nEverything completed in %s seconds.\n", $totalTime));

		$payload = [
			'generation' => $generations,
			'fitness'    => $finalFitness,
			'best'       => (string) $finalBest
		];
		$this->send($payload);

		$this->close();

		// Done
		return 0;
	}
}
