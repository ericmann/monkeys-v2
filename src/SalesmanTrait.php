<?php
namespace EAMann\Machines;

trait SalesmanTrait
{
	public static $cities = [];

	public static function loadCities()
	{
		$cityData = file_get_contents(__DIR__ . '/../cities.js');
		$cityData = substr($cityData, 15);

		self::$cities = json_decode($cityData, true);
	}
}