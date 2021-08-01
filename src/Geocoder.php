<?php

declare(strict_types=1);

namespace Baraja\Shop\Address;


use Baraja\Shop\Address\Entity\Address;
use Baraja\Shop\Address\Entity\Coordinates;

final class Geocoder
{
	/** @var GeocoderAdapter[] */
	private array $adapters = [];


	public function decode(Address $address): Coordinates
	{
		$coordinates = $address->getCoordinates();
		if ($coordinates !== null) {
			return $coordinates;
		}
		$lastException = null;
		foreach ($this->getGeocoderAdapters() as $adapter) {
			try {
				return $adapter->decode($address);
			} catch (\Throwable $e) {
				$lastException = $e;
			}
		}

		throw new \LogicException(
			'Address "' . $address->getFullString() . '" can not be geocoded.',
			500,
			$lastException,
		);
	}


	/**
	 * @return GeocoderAdapter[]
	 */
	public function getGeocoderAdapters(): array
	{
		if ($this->adapters === []) {
			$this->adapters[] = new DefaultMapyCzGeocoder;
		}

		return $this->adapters;
	}


	public function addGeocoderAdapter(GeocoderAdapter $adapter): void
	{
		$this->adapters[] = $adapter;
	}
}
