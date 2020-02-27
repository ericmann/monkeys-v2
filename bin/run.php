#!/usr/bin/env php
<?php

namespace EAMann\Machines;

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new MonkeyCommand());
$application->add(new GeneticSalesmanCommand());
$application->run();