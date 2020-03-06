<?php
namespace EAMann\Machines;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CitiesCommand extends Command
{
	private const INDEX_CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';

	private const CITY_MIN = 0;
	private const CITY_MAX = 20;

	protected static $defaultName = 'cities';

	protected function configure()
	{
		$this
			->setDescription('Create a random array of cities.')
			->setHelp('Create a random array of cities.')
			->addArgument('number', InputArgument::OPTIONAL, 'How many cities should we create?', 20);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$cityCount = intval($input->getArgument('number'));

		if ($cityCount === 0 || $cityCount > 52) {
			$output->writeln('Invalid number of cities! Must be > 0 and < 52!');
			return 1;
		}

		$fp = fopen(__DIR__ . '/../cities.js', 'w');

		fwrite($fp, 'const points =' . PHP_EOL);
		fwrite($fp,'{' . PHP_EOL);

		$cities = str_split(substr(self::INDEX_CHARACTERS, 0, $cityCount - 1));
		foreach($cities as $city) {
			$x = random_int(self::CITY_MIN, self::CITY_MAX);
			$y = random_int(self::CITY_MIN, self::CITY_MAX);
			$line = sprintf('  "%s": [%d, %d],' . PHP_EOL, $city, $x, $y);
			fwrite($fp, $line);
		}

		$x = random_int(self::CITY_MIN, self::CITY_MAX);
		$y = random_int(self::CITY_MIN, self::CITY_MAX);
		$line = sprintf('  "z": [%d, %d]' . PHP_EOL, $x, $y);
		fwrite($fp, $line);

		fwrite($fp,'}');
		fclose($fp);

		return 0;
	}
}