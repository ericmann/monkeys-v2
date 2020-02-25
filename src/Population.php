<?php
namespace EAMann\Machines;

abstract class Population
{
	protected $population;

	protected $maxFitness = 0;
	protected $sumOfMaxMinusFitness = 0;

	private const MUTATION_PROBABILITY = 0.01;

	private const CROSSOVER_PROBABILITY = 0.87;

	public function createChild(): Genome
	{
		/**
		 * @var Genome $parent1
		 * @var Genome $parent2
		 */
		list($parent1, $parent2) = $this->getParents();

		if ($this->randomFloat() < self::CROSSOVER_PROBABILITY) {
			$child = $parent1->breedWith($parent2);
		} else {
			$child = $parent1;
		}

		if ($this->randomFloat() < self::MUTATION_PROBABILITY) {
			$child = $child->mutate();
		}

		return $child;
	}

	function getParents(): array
	{
		$randomParent = function(int $sum, int $max): Genome
		{
			$val = $this->randomFloat() * $sum;

			for ($i = 0; $i < count($this->population); $i++) {
				/** @var Genome $member */
				$member = $this->population[$i];
				$maxMinusFitness = $max - $member->determineFitness();
				if ($val < $maxMinusFitness) {
					return $member;
				}
				$val -= $maxMinusFitness;
			}

			throw new \Exception('Unable to select a parent!');
		};

		return [
			$randomParent($this->sumOfMaxMinusFitness, $this->maxFitness),
			$randomParent($this->sumOfMaxMinusFitness, $this->maxFitness),
		];
	}

	public function step()
	{
		// Calculate some useful values we need later for selecting random parents.
		foreach($this->population as $member) {
			/** @var Genome $member */
			$this->maxFitness = max($this->maxFitness, $member->determineFitness());
		}
		$this->maxFitness += 1.0;

		foreach($this->population as $member) {
			/** @var Genome $member */
			$this->sumOfMaxMinusFitness += ($this->maxFitness - $member->determineFitness());
		}

		// Create a new population
		$newPopulation = [];
		while(count($newPopulation) < count($this->population)) {
			$newPopulation[] = $this->createChild();
		}

		$this->population = $newPopulation;
		$this->maxFitness = 0;
		$this->sumOfMaxMinusFitness = 0;
	}

	public function best(): string
	{
		return $this->bestGenome()->asString();
	}

	public function bestGenome(): Genome
	{
		$best = null;
		$bestFitness = PHP_INT_MAX;
		foreach($this->population as $member) {
			/** @var Genome $member */
			if ($member->determineFitness() < $bestFitness) {
				$best = $member;
			}
		}

		return $best;
	}

	abstract function __construct(int $members);

	private function randomFloat(): float
	{
		return random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX;
	}
}