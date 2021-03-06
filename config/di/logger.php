<?php declare(strict_types=1);

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use function DI\factory;
use function DI\get;
use Monolog\Logger;
use Monolog\Processor;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use WyriHaximus\Monolog\FormattedPsrHandler\FormattedPsrHandler;
use WyriHaximus\Monolog\Processors\ExceptionClassProcessor;
use WyriHaximus\Monolog\Processors\KeyValueProcessor;
use WyriHaximus\Monolog\Processors\RuntimeProcessor;
use WyriHaximus\Monolog\Processors\ToContextProcessor;
use WyriHaximus\Monolog\Processors\TraceProcessor;
use WyriHaximus\React\PSR3\Stdio\StdioLogger;

return (function () {
    return [
        LoggerInterface::class => factory(function (Logger $logger): LoggerInterface {
            return $logger;
        }),
        Logger::class => factory(function (
            LoopInterface $loop,
            string $name,
            string $version,
            iterable $handlers = [],
            iterable $processors = []
        ): Logger {
            $logger = new Logger(\strtolower($name));
            $logger->pushProcessor(new ToContextProcessor());
            $logger->pushProcessor(new TraceProcessor());
            $logger->pushProcessor(new KeyValueProcessor('version', $version));
            $logger->pushProcessor(new ExceptionClassProcessor());
            $logger->pushProcessor(new RuntimeProcessor());
            $logger->pushProcessor(new Processor\ProcessIdProcessor());
            $logger->pushProcessor(new Processor\IntrospectionProcessor(Logger::NOTICE));
            $logger->pushProcessor(new Processor\MemoryUsageProcessor());
            $logger->pushProcessor(new Processor\MemoryPeakUsageProcessor());
            foreach ($processors as $processor) {
                $logger->pushProcessor($processor);
            }
            $consoleHandler = new FormattedPsrHandler(StdioLogger::create($loop)->withHideLevel(true));
            $consoleHandler->setFormatter(new ColoredLineFormatter(
                null,
                '[%datetime%] %channel%.%level_name%: %message%',
                'Y-m-d H:i:s.u',
                true,
                false
            ));
            $logger->pushHandler($consoleHandler);
            foreach ($handlers as $handler) {
                $logger->pushHandler($handler);
            }

            return $logger;
        })->
            parameter('name', get('config.app.name'))->
            parameter('version', get('config.app.version'))->
            parameter('handlers', get('config.logger.handlers'))->
            parameter('processors', get('config.logger.processors')),
    ];
})();
