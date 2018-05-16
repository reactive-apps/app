<?php declare(strict_types=1);

use Monolog\ErrorHandler;
use Psr\Log\LoggerInterface;
use ReactiveApps\CommandCreator;
use ReactiveApps\CommandLocator;
use Silly\Application;

return (function () {
    return [
        Application::class => \DI\factory(function (CommandCreator $commandFactory, string $name, string $version) {
            $app = new Application($name, $version);
            foreach (CommandLocator::locate() as $class) {
                $app->command(...$commandFactory->create($class));
            }
            return $app;
        })
        ->parameter('name', \DI\get('config.app.name'))
        ->parameter('version', \DI\get('config.app.version')),
    ];
})();
