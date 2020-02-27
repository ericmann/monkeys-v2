<?php
namespace EAMann\Machines;

abstract class Population
{
	private $population = [];

	protected $mutationProbability;
	protected $crossoverProbability;

	abstract function crossover(Genome $first, Genome $second): Genome;

	abstract function mutate(Genome $genome): Genome;

	abstract function random(): Genome;

	abstract function fitness(Genome $genome): int;

	public function __construct(
	    float $mutationProbability = 0.01,
        float $crossoverProbability = 0.87,
        array $population = []
    )
    {
        $this->mutationProbability = $mutationProbability;
        $this->crossoverProbability = $crossoverProbability;
        $this->population = $population;
    }

    public function initialize(int $populationSize = 50)
    {
        if (!empty($this->population)) {
            throw new \Exception('Population already initialized!');
        }

        foreach(range(1, $populationSize) as $i) {
            $this->population[] = $this->random();
        }
    }

	public function createChild(int $sum, int $max): Genome
	{
		/**
		 * @var Genome $parent1
		 * @var Genome $parent2
		 */
		list($parent1, $parent2) = $this->getParents($sum, $max);

		$child = $this->crossover($parent1, $parent2);
		return $this->mutate($child);
	}

	function getParents(int $sum, int $max): array
	{
		$randomParent = function() use ($sum, $max): Genome
		{
			$val = $this->randomFloat() * $sum;

			foreach($this->population as $member) {
				/** @var Genome $member */
				$maxMinusFitness = $max - $this->fitness($member);
				if ($val < $maxMinusFitness) {
					return $member;
				}
				$val -= $maxMinusFitness;
			}

			throw new \Exception('Unable to select a parent!');
		};

		return [
			$randomParent(),
			$randomParent(),
		];
	}

	public function step(): Population
	{
	    $maxFitness = 0;
	    $sumOfMaxMinusFitness = 0;

		// Calculate some useful values we need later for selecting random parents.
		foreach($this->population as $member) {
			/** @var Genome $member */
			$maxFitness = max($maxFitness, $this->fitness($member));
		}
		$maxFitness += 1.0;

		foreach($this->population as $member) {
			/** @var Genome $member */
			$sumOfMaxMinusFitness += ($maxFitness - $this->fitness($member));
		}

		// Create a new population
		$newPopulation = [];
		$populationSize = count($this->population);

		// Allow the best child to move on
        $newPopulation[] = $this->bestGenome();

        // Add other children
		while(count($newPopulation) < $populationSize) {
			$newPopulation[] = $this->createChild($sumOfMaxMinusFitness, $maxFitness);
		}

		return new static(
		    $this->mutationProbability,
            $this->crossoverProbability,
            $newPopulation
        );
	}

	public function bestGenome(): Genome
	{
		$best = null;
		$bestFitness = PHP_INT_MAX;
		foreach($this->population as $member) {
			/** @var Genome $member */
            $fitness = $this->fitness($member);
			if ($fitness < $bestFitness) {
				$best = $member;
				$bestFitness = $fitness;
			}
		}

		return $best;
	}

	protected function randomFloat(): float
	{
		return random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX;
	}
}