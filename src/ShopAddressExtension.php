<?php

declare(strict_types=1);

namespace Baraja\Shop\Address;


use Baraja\Doctrine\ORM\DI\OrmAnnotationsExtension;
use Nette\DI\CompilerExtension;

final class ShopAddressExtension extends CompilerExtension
{
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		OrmAnnotationsExtension::addAnnotationPathToManager($builder, 'Baraja\Shop\Address\Entity', __DIR__ . '/Entity');

		$builder->addDefinition($this->prefix('geocoder'))
			->setFactory(Geocoder::class);
	}
}
