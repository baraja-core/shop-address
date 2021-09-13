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


	/**
	 * Accepted formats:
	 * - "49.0518417N, 14.4354897E"
	 * - "49.0518417N,14.4354897E"
	 * - "49.0518417, 14.4354897"
	 * - "49.0518417,14.4354897"
	 * - "49.0518417 14.4354897"
	 * - "-47.338388,-0.990228"
	 * - "-47.338388 -0.990228"
	 * - "49°3'6.630"N, 14°26'7.763"E"
	 * - "N 49°3.11050', E 14°26.12938'"
	 * - Google maps URL
	 * - Mapy.cz URL
	 */
	public static function from(string $haystack): self
	{
		// Matching decimals
		if (
			preg_match(
				'/^(-?[1-8]?\d(?:\.\d{1,18})?|90(?:\.0{1,18})?)N?,?\s*?(-?(?:1[0-7]|[1-9])?\d(?:\.\d{1,18})?|180(?:\.0{1,18})?)E?$/u',
				$haystack,
				$match
			) === 1
		) {
			$lat = (float) ($match[1]);
			$lng = (float) ($match[2]);

			return new self($lat, $lng);
		}

		// Matching degrees: 49°3'6.630"N, 14°26'7.763"E
		if (
			preg_match(
				'/^([0-8]?\d|90)°\s?([0-5]?\d\')?\s?(\d+(?:\.\d{1,5})")?N?,?\s?(1[0-7]?\d|180)°\s?([0-5]?\d\')?\s?(\d+(?:\.\d{1,5})")?E?$/u',
				$haystack,
				$match
			) === 1
		) {
			$latDeg = (int) ($match[1]);
			$latMin = (int) ($match[2]);
			$latSec = (float) ($match[3]);

			$lngDeg = (int) ($match[4]);
			$lngMin = (int) ($match[5]);
			$lngSec = (float) ($match[6]);

			$lat = $latDeg + ((($latMin * 60) + ($latSec)) / 3600);
			$lng = $lngDeg + ((($lngMin * 60) + ($lngSec)) / 3600);

			return new self(round($lat, 7), round($lng, 7));
		}

		// Matching degrees: N 49°3.11050', E 14°26.12938'
		if (
			preg_match(
				'/^N?\s?([0-8]?\d|90)°\s?(\d+(?:\.\d{1,5})\'),?\s?E?\s?(1[0-7]?\d|180)°\s?(\d+(?:\.\d{1,5})\')$/u',
				$haystack,
				$match
			) === 1
		) {
			$latDeg = (int) ($match[1]);
			$latMin = (float) ($match[2]);

			$lngDeg = (int) ($match[3]);
			$lngMin = (float) ($match[4]);

			$lat = $latDeg + ($latMin * 60 / 3600);
			$lng = $lngDeg + ($lngMin * 60 / 3600);

			return new self(round($lat, 7), round($lng, 7));
		}

		// Google maps URL
		if (preg_match('/@([0-9.]+),([0-9.]+),([0-9z]+)/u', $haystack, $match) === 1) {
			return new self((float) $match[1], (float) $match[2]);
		}

		// Mapy.cz URL
		if (preg_match('/x=([0-9.]+)&y=([0-9.]+)&z=(\d+)/u', $haystack, $match) === 1) {
			return new self((float) $match[2], (float) $match[1]);
		}

		throw new \InvalidArgumentException('Nothing detected in "' . $haystack . '".');
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
