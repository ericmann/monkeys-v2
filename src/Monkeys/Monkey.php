<?php
namespace EAMann\Machines\Monkeys;

use EAMann\Machines\Genome;

class Monkey extends Genome
{
	protected $community;

	protected $genome;

	/**
	 * Monkey constructor.
	 * @param int        $length
	 * @param Population $parent
	 * @throws \Exception
	 */
	public function __construct(int $length, Population $parent)
	{
		$this->community = $parent;

		$genome = '';
		for($i = 0; $i < $length; $i++) {
			$genome .= $this->validChars()[random_int(1, count($this->validChars())) - 1];
		}

		$this->genome = $genome;
	}

	public function breedWith(Genome $parent): Genome
	{
		$crossoverPoint = random_int(0, strlen($this->genome) - 1);

		$newGenome = substr($this->genome, 0, $crossoverPoint) . substr($parent->asString(), $crossoverPoint);

		$child = new self(0, $this->community);
		$child->genome = $newGenome;

		return $child;
	}

	public function mutate(): Genome
	{
		$newGenome = $this->genome;
		$upDown = random_int(0, 10) < 5 ? -1 : 1;
		$index = random_int(0, strlen($newGenome) - 1);

		$newChar = ord($this->genome[$index]) + $upDown;

		if ($newChar === 31 || $newChar === 127) {
			$newChar = 10;
		} else if ($newChar === 9) {
			$newChar = 126;
		} else if ($newChar === 11) {
			$newChar = 32;
		}
		$newGenome[$index] = chr($newChar);

		$child = new self(0, $this->community);
		$child->genome = $newGenome;

		return $child;
	}

	public static function fromString(string $raw, Population $parent)
	{
		$monkey = new self(0, $parent);
		$monkey->genome = $raw;

		return $monkey;
	}

	public function determineFitness(): int
	{
		$target = $this->community->getTarget();

		$fitness = 0;
		foreach(range(0, strlen($target) - 1) as $index) {
			$fitness += pow(ord($this->genome[$index]) - ord($target[$index]), 2);
		}

		return $fitness;
	}

	public function asString(): string
	{
		return $this->genome;
	}

	private function validChars(): array
	{
		static $_validChars;

		if (!$_validChars) {
			$_validChars[] = chr(10);

			for ($i = 2, $pos = 32; $i < 97; $i++, $pos++) {
				$_validChars[] = chr($pos);
			}
		}

		return $_validChars;
	}
}