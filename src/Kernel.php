<?php


namespace EAMann\Machines;


class Kernel
{
    private $population;

    public function __construct(Population $population)
    {
        $this->population = $population;
    }

    public function next(): Kernel
    {
        $newPopulation = $this->population->step();

        return new self($newPopulation);
    }

    public function bestGenome(): Genome
    {
        return $this->population->bestGenome();
    }
}