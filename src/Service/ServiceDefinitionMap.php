<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/boot project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Boot\Service;

use Daikon\DataStructure\TypedMap;

final class ServiceDefinitionMap extends TypedMap
{
    public function __construct(iterable $serviceDefinitions = [])
    {
        $this->init($serviceDefinitions, [ServiceDefinitionInterface::class]);
    }
}
