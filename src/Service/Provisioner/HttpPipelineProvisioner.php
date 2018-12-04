<?php

declare(strict_types=1);

namespace Oroshi\Core\Service\Provisioner;

use Aura\Router\RouterContainer;
use Auryn\Injector;
use Daikon\Config\ConfigProviderInterface;
use Middlewares\ContentEncoding;
use Middlewares\ContentLanguage;
use Middlewares\Whoops;
use Neomerx\Cors\Analyzer;
use Neomerx\Cors\Contracts\AnalyzerInterface;
use Neomerx\Cors\Strategies\Settings;
use Oroshi\Core\Config\RoutingConfigLoader;
use Oroshi\Core\Middleware\PipelineBuilderInterface;
use Oroshi\Core\Middleware\RoutingHandler;
use Oroshi\Core\Service\ServiceDefinitionInterface;
use Psr\Container\ContainerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final class HttpPipelineProvisioner implements ProvisionerInterface
{
    public function provision(
        Injector $injector,
        ConfigProviderInterface $configProvider,
        ServiceDefinitionInterface $serviceDefinition
    ): void {
        $serviceClass = $serviceDefinition->getServiceClass();
        $settings = $serviceDefinition->getSettings();

        $injector
            ->define($serviceClass, [':settings' => $settings])
            ->share($serviceClass)
            ->alias(PipelineBuilderInterface::class, $serviceClass)
            // Exception Handling
            ->define(Whoops::class, [':whoops' => (new Run)->pushHandler(new PrettyPageHandler)])
            // Content Negotiation
            ->define(ContentLanguage::class, [':languages' => ['en', 'gl', 'es']])
            ->define(ContentEncoding::class, [':encodings' => ['gzip', 'deflate']])
            // Cors
            ->share(AnalyzerInterface::class)
            ->alias(AnalyzerInterface::class, Analyzer::class)
            ->delegate(Analyzer::class, function () use ($configProvider): AnalyzerInterface {
                $corsSettings = new Settings;
                $corsSettings->setServerOrigin([
                    'scheme' => 'http',
                    'host' => $configProvider->get('cors.host'),
                    'port' => $configProvider->get('cors.port'),
                ]);
                return Analyzer::instance($corsSettings);
            })
            // Routing
            ->share(RoutingHandler::class)
            ->delegate(
                RoutingHandler::class,
                function (ContainerInterface $container) use ($configProvider): RoutingHandler {
                    return new RoutingHandler(
                        $this->routerFactory($configProvider),
                        $container
                    );
                }
            );
    }

    private function routerFactory(ConfigProviderInterface $configProvider): RouterContainer
    {
        $appContext = $configProvider->get('app.context');
        $appEnv = $configProvider->get('app.env');
        $appConfigDir = $configProvider->get('app.config_dir');
        $router = new RouterContainer;
        (new RoutingConfigLoader($router, $configProvider))->load(
            array_merge([$appConfigDir], $configProvider->get('crates.*.config_dir')),
            [
                'routing.php',
                "routing.$appContext.php",
                "routing.$appEnv.php",
                "routing.$appContext.$appEnv.php"
            ]
        );
        return $router;
    }
}