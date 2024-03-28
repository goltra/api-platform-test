<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Tiempo;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/***
 * @implements ProviderInterface<Tiempo[] | Tiempo | null>
 */
class TiempoGetProvider implements ProviderInterface
{

    public function __construct(
        #[Autowire('@api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): null|array|object
    {
        $tiempo = $this->itemProvider->provide($operation, $uriVariables, $context);
        if ($tiempo instanceof Tiempo) {
            $tiempo->setDescripcion('llamado desde proveedor personalizado');
        }
        return $tiempo;
    }
}
