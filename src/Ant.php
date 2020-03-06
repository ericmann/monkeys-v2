<?php
namespace EAMann\Machines;

class Ant
{
    private array $cities;

    private array $distances;
    private array $initialPheromones;
    private string $startingPoint;

    public float $length = 0.0;
    public string $route = '';
    public array $pheromones;

    public function __construct(array $cities, string $startingPoint, array $distMatrix, array $pheromoneMatrix)
    {
        $this->cities = $cities;
        $this->startingPoint = $startingPoint;
        $this->distances = $distMatrix;
        $this->initialPheromones = $pheromoneMatrix;
    }

    public function createRoute()
    {
        // Start at the initial point
        $this->route .= $this->startingPoint;

        // Copy our cities array
        $candidates = $this->cities;

        // Initialize a loop
        $at = $this->startingPoint;
        do {
            $weights = [];
            foreach($candidates as $i => $candidateCity) {
                $d = $this->distances[$at][$candidateCity] + 0.01;
                $p = $this->initialPheromones[$at][$candidateCity];

                $weights[$candidateCity] = (1 / $d) * $p;
            }

            // Sort our candidate weights in descending order according to their attractiveness
            arsort($weights);
            $totalWeight = array_sum(array_values($weights));

            // Weighted random choice
            $nextCity = '';
            $remainingDistance = (random_int(0, 100) / 100) * $totalWeight;
            foreach($weights as $city => $weight) {
                $remainingDistance -= $weight;
                $nextCity = $city;
                if ($remainingDistance < 0) {
                    break;
                }
            }

            // Move to our next city and update the route
            $at = $nextCity;
            $this->route .= $nextCity;
            $candidates = array_diff($candidates, [$nextCity]);
        } while (count($candidates) > 0);

        // Return to base
        $this->route .= $this->startingPoint;

        // Update distance
        $routeArray = str_split(substr($this->route, 1));
        $start = $this->startingPoint;
        foreach($routeArray as $city) {
            $this->length += $this->distances[$start][$city];
            $start = $city;
        }

        // Calculate pheromone trail
        $pheromoneDeposit = 1 / $this->length;

        // Build initial empty matrix
        $pheromones = [];
        foreach(array_keys($this->initialPheromones) as $city) {
            $pheromones[$city] = [];
            foreach(array_keys($this->initialPheromones) as $dest) {
                $pheromones[$city][$dest] = 0.0;
            }
        }

        // Update pheromone matrix
        $start = $this->startingPoint;
        foreach($routeArray as $dest) {
            $pheromones[$start][$dest] = $pheromoneDeposit;
            $start = $dest;
        }
        $this->pheromones = $pheromones;

        // Remove the unnecessary last city concatenation
        $this->route = substr($this->route, 0, - 1);
    }
}