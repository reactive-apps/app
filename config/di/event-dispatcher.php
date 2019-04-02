<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use WyriHaximus\Broadcast\ComposerJsonListenerProvider;
use WyriHaximus\Broadcast\Dispatcher;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;
use function DI\factory;

return (function () {
    return [
        ComposerJsonListenerProvider::class => factory(function (ContainerInterface $container) {
            return new ComposerJsonListenerProvider('extra.reactive-apps.listeners', $container);
        }),
        EventDispatcherInterface::class => factory(function (
            LoggerInterface $logger,
            ComposerJsonListenerProvider $listenerProvider
        ) {
            return new Dispatcher(
                $listenerProvider,
                new ContextLogger(
                    $logger,
                    [
                        'component' => 'event-dispatcher',
                    ],
                    'event-dispatcher'
                )
            );
        }),
    ];
})();
