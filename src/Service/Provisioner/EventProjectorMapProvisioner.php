<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/boot project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Boot\Service\Provisioner;

use Auryn\Injector;
use Daikon\Boot\Service\ServiceDefinitionInterface;
use Daikon\Config\ConfigProviderInterface;
use Daikon\ReadModel\Projector\EventProjector;
use Daikon\ReadModel\Projector\EventProjectorMap;
use Daikon\ReadModel\Repository\RepositoryMap;

final class EventProjectorMapProvisioner implements ProvisionerInterface
{
    public function provision(
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $projectorConfigs = (array)$configProvider->get('databases.projectors', []);
        $injector
            ->share(EventProjectorMap::class)
            ->delegate(
                EventProjectorMap::class,
                $this->factory($injector, $projectorConfigs, $serviceDefinition)
            );
    }

    private function factory(
        Injector $injector,
        array $projectorConfigs,
        ServiceDefinitionInterface $serviceDefinition
    ): callable {
        return function (RepositoryMap $repositoryMap) use (
            $injector,
            $projectorConfigs,
            $serviceDefinition
        ): EventProjectorMap {
            $settings = $serviceDefinition->getSettings();
            $defaultWrapper = $settings['wrapper'] ?? EventProjector::class;
            $projectors = [];
            foreach ($projectorConfigs as $projectorName => $projectorConfig) {
                $projectorClass = $projectorConfig['class'];
                $projectorEvents = $projectorConfig['events'];
                $eventProjectFqcn = $projectorConfig['wrapper'] ?? $defaultWrapper;
                $projectors[$projectorName] = $injector->make($eventProjectFqcn, [
                    ':eventExpressions' => $projectorEvents,
                    ':projector' => $injector->make(
                        $projectorClass,
                        [ ':repository' => $repositoryMap->get($projectorConfig['repository']) ]
                    )
                ]);
            }
            return new EventProjectorMap($projectors);
        };
    }
}
