<?php
namespace EAMann\Machines;

class Genome implements \Serializable
{
    private $genome;

    public function __construct($genome)
    {
        $this->genome = $genome;
    }

    public function __toString(): string
    {
        return $this->genome;
    }

    public function serialize()
    {
        return $this->genome;
    }

    public function unserialize($data)
    {
        $this->genome = $data;
    }
}