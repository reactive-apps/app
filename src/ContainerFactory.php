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
        //$version = trim(file_exists(ROOT . 'version') ? file_get_contents(ROOT . 'version') : 'dev');
        $container = new ContainerBuilder();
        //$definitions['app.version'] = $version;
        $definitions['config'] = [
            'foo' => [
                'bar' => 'baz',
            ],
        ];

        $container->addDefinitions($definitions);

        return $container->build();
    }
}
