<?php

namespace EAMann\Machines\Salesman;

use EAMann\Machines\Genome;

class Population extends \EAMann\Machines\Population
{
    public static $cities = [
        'A' => [2, 2],
        'B' => [6, 2],
        'C' => [16, 2],
        'D' => [2, 4],
        'E' => [10, 4],
        'F' => [20, 4],
        'G' => [18, 6],
        'H' => [6, 8],
        'I' => [12, 8],
        'J' => [18, 10],
        'K' => [4, 12],
        'L' => [10, 12],
        'M' => [14, 14],
        'N' => [2, 16],
        'O' => [10, 16],
        'P' => [20, 16],
        'Q' => [8, 18],
        'R' => [14, 18],
        'S' => [16, 20],
        'T' => [18, 20],
    ];

    function crossover(Genome $first, Genome $second): Genome
    {
        $geneA = random_int(0, strlen($first) - 1);
        $geneB = random_int(0, strlen($second) - 1);

        $startGene = min($geneA, $geneB);
        $endGene = max($geneA, $geneB);

        $child = str_split(substr($first, $startGene, $endGene));
        foreach(str_split($second) as $char) {
            if (!in_array($char, $child)) {
                $child[] = $char;
            }
        }

        return new Genome(join('', $child));
    }

    function mutate(Genome $genome): Genome
    {
        $genomeStr = (string) $genome;
        $newGenome = $genomeStr;
        $city1Pos = random_int(0, strlen($genomeStr) - 1);
        $city2Pos = random_int(0, strlen($genomeStr) - 1);

        $newGenome[$city1Pos] = $genomeStr[$city2Pos];
        $newGenome[$city2Pos] = $genomeStr[$city1Pos];

        return new Genome($newGenome);
    }

    function random(): Genome
    {
        return new Genome(str_shuffle('ABCDEFGHIJKLMNOPQRST'));
    }

    function fitness(Genome $genome): int
    {
        $genomeStr = (string) $genome;
        $distance = 0.0;

        for($i = 0; $i < strlen($genomeStr); $i++) {
            $fromCity = $genomeStr[$i];
            if ($i + 1 < strlen($genomeStr)) {
                $toCity = $genomeStr[$i + 1];
            } else {
                $toCity = $genomeStr[0];
            }

            $xDistance = self::$cities[$fromCity][0] - self::$cities[$toCity][0];
            $yDistance = self::$cities[$fromCity][1] - self::$cities[$toCity][1];

            $distance += sqrt(pow($xDistance, 2) + pow($yDistance, 2));
        }

        return floor($distance);
    }
}