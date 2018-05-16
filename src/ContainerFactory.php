<?php declare(strict_types=1);

namespace ReactiveApps;

use DI\ContainerBuilder;
use PHPDIDefinitions\DefinitionsGatherer;
use Psr\Container\ContainerInterface;

final class ContainerFactory
{
    public static function create(): ContainerInterface
    {
        $definitions = iterator_to_array(DefinitionsGatherer::gather());
        $container = new ContainerBuilder();
        foreach (ConfigurationLocator::locate() as $key => $value) {
            $definitions['config.' . $key] = $value;
        }

        $container->addDefinitions($definitions);

        return $container->build();
    }
}
