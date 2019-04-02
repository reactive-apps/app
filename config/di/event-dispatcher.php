<?php declare(strict_types=1);

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Monolog\Processor;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Rx\Shutdown;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use WyriHaximus\Broadcast\ComposerJsonListenerProvider;
use WyriHaximus\Broadcast\Dispatcher;
use WyriHaximus\Monolog\FormattedPsrHandler\FormattedPsrHandler;
use WyriHaximus\Monolog\Processors\ExceptionClassProcessor;
use WyriHaximus\Monolog\Processors\KeyValueProcessor;
use WyriHaximus\Monolog\Processors\RuntimeProcessor;
use WyriHaximus\Monolog\Processors\ToContextProcessor;
use WyriHaximus\Monolog\Processors\TraceProcessor;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;
use WyriHaximus\React\PSR3\Stdio\StdioLogger;
use function DI\factory;
use function DI\get;

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
