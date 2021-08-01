<?php

declare(strict_types=1);

namespace Baraja\Shop\Address;


use Baraja\Shop\Address\Entity\Address;
use Baraja\Shop\Address\Entity\Coordinates;

interface GeocoderAdapter
{
	public function decode(Address $address): Coordinates;
}
