<?php

namespace EAMann\Machines\Monkeys;

use EAMann\Machines\Genome;

class Population extends \EAMann\Machines\Population
{
	public static $target = 'To be or not to be, that is the question!';

	public function crossover(Genome $first, Genome $second): Genome
    {
        $crossoverPoint = random_int(0, strlen($first) - 1);

        $newGenome = substr($first, 0, $crossoverPoint) . substr($second, $crossoverPoint);
        return new Genome($newGenome);
    }

	public function mutate(Genome $genome): Genome
    {
        $newGenome = (string) $genome;
        $upDown = random_int(0, 10) < 5 ? -1 : 1;
		$index = random_int(0, strlen($newGenome) - 1);

		$newChar = ord($newGenome[$index]) + $upDown;

		if ($newChar === 31 || $newChar === 127) {
			$newChar = 10;
		} else if ($newChar === 9) {
			$newChar = 126;
		} else if ($newChar === 11) {
			$newChar = 32;
		}
		$newGenome[$index] = chr($newChar);

		return new Genome($newGenome);
    }

	public function random(): Genome
    {
        $genome = '';
        for($i = 0; $i < strlen(self::$target); $i++) {
			$genome .= $this->validChars()[random_int(1, count($this->validChars())) - 1];
		}

        return new Genome($genome);
    }

    public function fitness(Genome $genome): int
    {
        $genomeStr = (string) $genome;
        $fitness = 0;
		foreach(range(0, strlen(self::$target) - 1) as $index) {
			$fitness += pow(ord($genomeStr[$index]) - ord(self::$target[$index]), 2);
		}

		return $fitness;
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