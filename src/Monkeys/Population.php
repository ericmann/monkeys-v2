<?php

namespace EAMann\Machines\Monkeys;

use EAMann\Machines\Genome;

class Population extends \EAMann\Machines\Population
{
	private $target;

	public function __construct(int $members)
	{
		$target = 'To be or not to be';
		$this->initialize($target, $members);
	}

	public function initialize(string $target, int $members = 200)
	{
		$this->target = $target;
		$this->population = [];

		foreach(range(0, $members) as $i) {
			$this->population[] = new Monkey(strlen($target), $this);
		}
	}

	public function getTarget(): string
	{
		return $this->target;
	}
}