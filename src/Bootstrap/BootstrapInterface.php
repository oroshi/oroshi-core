<?php declare(strict_types=1);
/**
 * This file is part of the oroshi/oroshi-core project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oroshi\Core\Bootstrap;

use Auryn\Injector;
use Psr\Container\ContainerInterface;

interface BootstrapInterface
{
    public function __invoke(Injector $injector, array $bootstrapParams): ContainerInterface;
}
