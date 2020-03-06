<?php
namespace EAMann\Machines;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ACOCommand extends Command
{
    use SalesmanTrait, SocketClient;

    private const EVAPORATION_RATE = 0.8;

	protected static $defaultName = 'aco:salesman';

	protected function configure()
	{
		$this
			->setDescription('Runs our traveling salesman simulation using an ant colony.')
			->setHelp('Runs our traveling salesman simulation using an ant colony.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
    	self::loadCities();

        // Build distance matrix
        // {A => {A: 0, B: ?, C: ?, ...}, B => {} ...}
        $distance = [];
        foreach(self::$cities as $city => $coords) {
            $distance[$city] = [];
            foreach(self::$cities as $dest => $destCoords) {
                $xDist = $coords[0] - $destCoords[0];
                $yDist = $coords[1] - $destCoords[1];
                $distance[$city][$dest] = sqrt(pow($xDist, 2) + pow($yDist, 2));
            }
        }

        // Build initial pheromone matrix
        // {A => {A: 0, B: 1, C: 1, ...}, B => {A: 1, B: 0, ...} ...}
        $pheromones = [];
        foreach(self::$cities as $city => $coords) {
            $pheromones[$city] = [];
            foreach(self::$cities as $dest => $destCoords) {
                if ($city === $dest) {
                    $pheromones[$city][$dest] = 0.0;
                } else {
                    $pheromones[$city][$dest] = 1.0;
                }
            }
        }

        $this->connect();

        // Dynamic values
		$cityCount = count(self::$cities);
		$antCount = intval($cityCount / 4);

        // Loop
        $startTime = time();
        $generations = 2500;
		$bestLength = PHP_INT_MAX;
        foreach(range(1, $generations) as $i) {
            // Instantiate agents
            $ants = [];
            $cities = array_keys(self::$cities);
            foreach(range(1, $antCount) as $antIndex) {
                // Pick a random starting city
                $startingPoint = $cities[array_rand($cities)];
                $candidates = array_values(array_diff($cities, [$startingPoint]));

                $ant = new Ant($candidates, $startingPoint, $distance, $pheromones);
                $ant->createRoute();
                $ants[] = $ant;
            }

            // Identify and communicate best agent route
            $bestLength = PHP_INT_MAX;
            $bestAnt = null;
            foreach($ants as $ant) {
                if ($ant->length < $bestLength) {
                    $bestLength = $ant->length;
                    $bestAnt = $ant;
                }
            }

            $payload = [
                'generation' => $i,
                'fitness'    => $bestLength,
                'best'       => $bestAnt->route
            ];
            $this->send($payload);

            // Update pheromone matrix
            $pheromones = $this->updatePheromones($pheromones, $ants);
        }

        // Print final status
		$totalTime = (time() - $startTime);
		$output->write("\n\nAnts are done!");
		$output->write(sprintf("\nBest Fitness: %d", $bestLength));
		$output->write(sprintf("\nEverything completed in %s seconds.\n", $totalTime));

        // Done
        return 0;
    }

    protected function updatePheromones(array $initial, array $ants): array
    {
        $pheromones = [];
        foreach(self::$cities as $city => $coords) {
            $pheromones[$city] = [];
            foreach(self::$cities as $dest => $destCoords) {
                if ($city === $dest) {
                    $pheromones[$city][$dest] = 0.0;
                } else {
                    $residual = $initial[$city][$dest] * self::EVAPORATION_RATE;

                    // Ant contributions
                    $contributions = 0.0;
                    foreach($ants as $ant) {
                        /** @var Ant $ant */
                        $contributions += $ant->pheromones[$city][$dest];
                    }

                    $pheromones[$city][$dest] = $residual + $contributions;
                }
            }
        }

        return $pheromones;
    }
}