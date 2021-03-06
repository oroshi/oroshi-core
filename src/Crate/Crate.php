<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/boot project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Boot\Crate;

final class Crate implements CrateInterface
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function getLocation(): string
    {
        return dirname($this->settings['config_dir']);
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
