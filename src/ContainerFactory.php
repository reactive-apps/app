<?php declare(strict_types=1);

namespace ReactiveApps;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use ReactiveApps\CommandCreator;
use ReactiveApps\CommandLocator;
use ReactiveApps\Rx\Shutdown;
use React\EventLoop\LoopInterface;
use Silly\Edition\PhpDi\Application;
use Symfony\Component\Console\Output\OutputInterface;

final class ContainerFactory
{
    public static function create(): ContainerInterface
    {
        $definitions = [];
        //$version = trim(file_exists(ROOT . 'version') ? file_get_contents(ROOT . 'version') : 'dev');
        $container = new ContainerBuilder();
        //$definitions['app.version'] = $version;
        $definitions['config'] = [
            'foo' => [
                'bar' => 'baz',
            ]
        ];

        $container->addDefinitions($definitions);

        return $container->build();
    }
}
