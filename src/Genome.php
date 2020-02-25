<?php
namespace EAMann\Machines;

abstract class Genome
{
	abstract function breedWith(Genome $parent): Genome;

	abstract function mutate(): Genome;

	abstract function determineFitness(): int;

	abstract function asString(): string;
}