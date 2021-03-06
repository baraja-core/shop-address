<?php

declare(strict_types=1);

namespace Baraja\Shop\Address\Entity;


use Baraja\Country\Entity\Country;
use Baraja\EcommerceStandard\DTO\AddressInterface;
use Baraja\EcommerceStandard\DTO\CoordinatesInterface;
use Baraja\EcommerceStandard\DTO\CountryInterface;
use Baraja\EcommerceStandard\DTO\CustomerInterface;
use Baraja\Geocoder\Coordinates;
use Baraja\Shop\Customer\Entity\Customer;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'shop__address')]
class Address implements AddressInterface
{
	#[ORM\Id]
	#[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
	#[ORM\GeneratedValue]
	protected int $id;

	#[ORM\Column(type: 'string', length: 32)]
	private string $firstName;

	#[ORM\Column(type: 'string', length: 32)]
	private string $lastName;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $companyName = null;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $cin = null;

	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private ?string $tin = null;

	#[ORM\Column(type: 'string', length: 128)]
	private string $street;

	#[ORM\Column(type: 'string', length: 64)]
	private string $city;

	#[ORM\Column(type: 'string', length: 8)]
	private string $zip;

	#[ORM\ManyToOne(targetEntity: Country::class)]
	private Country $country;

	#[ORM\ManyToOne(targetEntity: Customer::class)]
	private ?Customer $customer = null;

	#[ORM\Column(name: '`default`', type: 'boolean')]
	private bool $default = false;

	private ?CoordinatesInterface $coordinates = null;

	#[ORM\Column(type: 'float', nullable: true)]
	private ?float $latitude = null;

	#[ORM\Column(type: 'float', nullable: true)]
	private ?float $longitude = null;


	public function __construct(
		Country $country,
		string $firstName,
		string $lastName,
		string $street,
		string $city,
		string|int $zip,
	) {
		$this->setCountry($country);
		$this->setFirstName($firstName);
		$this->setLastName($lastName);
		$this->setStreet($street);
		$this->setCity($city);
		$this->setZip((string) $zip);
	}


	public function getId(): int
	{
		return $this->id;
	}


	/**
	 * @param array{
	 *    country: Country,
	 *    firstName: string,
	 *    lastName: string,
	 *    street: string,
	 *    city: string,
	 *    zip: string|int,
	 *    companyName?: string|null,
	 *    ic?: string|null,
	 *    dic?: string|null
	 * } $data
	 */
	public static function hydrateData(array $data): self
	{
		$address = new self(
			$data['country'],
			$data['firstName'],
			$data['lastName'],
			$data['street'],
			$data['city'],
			$data['zip'],
		);
		if (isset($data['companyName'])) {
			$address->setCompanyName($data['companyName']);
		}
		if (isset($data['ic'])) {
			$address->setCin($data['ic']);
		}
		if (isset($data['dic'])) {
			$address->setTin($data['dic']);
		}

		return $address;
	}


	public function __toString(): string
	{
		return $this->getFullString();
	}


	public function getFullString(): string
	{
		$companyName = $this->getCompanyName();

		return ($companyName !== null ? $companyName . "\n" : '')
			. $this->getPersonName() . "\n"
			. $this->getStreet() . "\n"
			. $this->getCity() . ' ' . $this->getZip() . "\n"
			. $this->getCountry()->getName();
	}


	public function getName(): string
	{
		return $this->getCompanyName() ?: $this->getPersonName();
	}


	public function getPersonName(): string
	{
		return $this->getFirstName() . ' ' . $this->getLastName();
	}


	public function getFirstName(): string
	{
		return $this->firstName;
	}


	public function setFirstName(string $firstName): void
	{
		$firstName = trim($firstName);
		$prefix = mb_substr($firstName, 0, 1, 'UTF-8');
		$suffix = mb_substr($firstName, 1, null, 'UTF-8');
		$this->firstName = mb_strtoupper($prefix, 'UTF-8') . $suffix;
	}


	public function getLastName(): string
	{
		return $this->lastName;
	}


	public function setLastName(string $lastName): void
	{
		$lastName = trim($lastName);
		$prefix = mb_substr($lastName, 0, 1, 'UTF-8');
		$suffix = mb_substr($lastName, 1, null, 'UTF-8');
		$this->lastName = mb_strtoupper($prefix, 'UTF-8') . $suffix;
	}


	public function getCompanyName(): ?string
	{
		return $this->companyName;
	}


	public function setCompanyName(?string $companyName): void
	{
		$this->companyName = trim($companyName ?? '') ?: null;
	}


	public function getCin(): ?string
	{
		return $this->cin;
	}


	public function setCin(?string $cin): void
	{
		$this->cin = $cin ?: null;
	}


	public function getTin(): ?string
	{
		return $this->tin;
	}


	public function setTin(?string $tin): void
	{
		$this->tin = $tin ?: null;
	}


	public function getStreet(): string
	{
		return $this->street;
	}


	public function setStreet(string $street): void
	{
		$street = trim($street);
		$return = '';
		$firstUpper = false;
		foreach (str_split($street) as $char) {
			if ($char === '.') {
				$firstUpper = false;
			} elseif ($firstUpper === false && preg_match('/[a-zA-Z]/', $char) === 1) {
				$char = mb_strtoupper($char, 'UTF-8');
				$firstUpper = true;
			}
			$return .= $char;
		}

		$this->street = $return;
	}


	public function getCity(): string
	{
		return $this->city;
	}


	public function setCity(string $city): void
	{
		$this->city = $city;
	}


	public function getZip(): string
	{
		return $this->zip;
	}


	public function setZip(string $zip): void
	{
		$this->zip = (string) preg_replace('/\D+/', '', $zip);
	}


	public function getCountry(): CountryInterface
	{
		return $this->country;
	}


	public function setCountry(Country $country): void
	{
		if ($country->isActive() === false) {
			throw new \InvalidArgumentException(sprintf(
				'Country "%s" can not be used, because it must be active.',
				$country->getIsoCode(),
			));
		}
		$this->country = $country;
	}


	public function getCustomer(): ?CustomerInterface
	{
		return $this->customer;
	}


	public function setCustomer(?Customer $customer): void
	{
		$this->customer = $customer;
	}


	public function isDefault(): bool
	{
		return $this->default;
	}


	public function setDefault(bool $default): void
	{
		$this->default = $default;
	}


	public function getCoordinates(): ?CoordinatesInterface
	{
		if ($this->latitude !== null && $this->longitude !== null) {
			$this->coordinates = new Coordinates($this->latitude, $this->longitude);
		}

		return $this->coordinates;
	}


	public function setCoordinates(?CoordinatesInterface $coordinates): void
	{
		$this->coordinates = $coordinates;
		if ($coordinates !== null) {
			$this->latitude = $coordinates->getLatitude();
			$this->longitude = $coordinates->getLongitude();
		}
	}
}
