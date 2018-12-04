<?php

declare(strict_types=1);

namespace Oroshi\Core\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface ActionInterface
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface;

    public function getValidator(): ?callable;

    public function isSecure(): bool;
}
