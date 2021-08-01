<?php

declare(strict_types=1);

namespace Baraja\Shop\Address\Entity;


class Coordinates
{
	public const
		LATITUDE_MIN_VALUE = -90.0,
		LATITUDE_MAX_VALUE = 90.0,
		LONGITUDE_MIN_VALUE = -180.0,
		LONGITUDE_MAX_VALUE = 180.0;


	public function __construct(
		private float $latitude,
		private float $longitude,
	) {
		if ($latitude < self::LATITUDE_MIN_VALUE || $latitude > self::LATITUDE_MAX_VALUE) {
			throw new \InvalidArgumentException(
				'Latitude is invalid, because value "' . $latitude . '" given.',
			);
		}
		if ($longitude < self::LONGITUDE_MIN_VALUE || $longitude > self::LONGITUDE_MAX_VALUE) {
			throw new \InvalidArgumentException(
				'Longitude is invalid, because value "' . $longitude . '" given.',
			);
		}
	}


	public function getLatitude(): float
	{
		return $this->latitude;
	}


	public function getLongitude(): float
	{
		return $this->longitude;
	}
}
