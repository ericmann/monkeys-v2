<?php
namespace EAMann\Machines;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WebSocket\Client;

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

        $initialBest = $kernel->bestGenome();
        $initialDistance = $population->fitness($initialBest);

        $client = new Client('ws://localhost:8080');
        $payload = [
        	'generation' => 0,
			'fitness'    => $initialDistance,
			'best'       => (string) $initialBest
		];
        $client->send(json_encode($payload));

		$startTime = time();
		$generations = 2500;
		foreach(range(1, $generations) as $i) {
			$kernel = $kernel->next();
			$best = $kernel->bestGenome();

			if ($i % 10 === 0) {
				$genPerSec = floor($i/(time() - $startTime + 1));
				// Print status

				$bestFitness = $population->fitness($best);

				$out = '';
				$out .= "\nGeneration:   " . $i;
				$out .="\nGen / sec:    " . $genPerSec;
				$out .="\nBest Fitness: " . $bestFitness;
				$out .= "\n\n" . $best;

				$lines = substr_count($out, "\n");
				$output->write(str_repeat("\x1B[1A\x1B[2K", $lines));
				$output->write($out);

				$payload = [
					'generation' => $i,
					'fitness'    => $bestFitness,
					'best'       => (string) $best
				];
				$client->send(json_encode($payload));
			}
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
		$client->send(json_encode($payload));

		$client->close();

		// Done
		return 0;
	}
}
