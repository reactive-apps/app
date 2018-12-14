<?php declare(strict_types=1);

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Monolog\Processor;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Rx\Shutdown;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use WyriHaximus\Monolog\FormattedPsrHandler\FormattedPsrHandler;
use WyriHaximus\Monolog\Processors\ExceptionClassProcessor;
use WyriHaximus\Monolog\Processors\KeyValueProcessor;
use WyriHaximus\Monolog\Processors\RuntimeProcessor;
use WyriHaximus\Monolog\Processors\ToContextProcessor;
use WyriHaximus\Monolog\Processors\TraceProcessor;
use WyriHaximus\React\PSR3\Stdio\StdioLogger;
use function DI\factory;
use function DI\get;

return (function () {
    return [
        LoggerInterface::class => factory(function (
            LoopInterface $loop,
            Shutdown $shutdown,
            string $name,
            string $version,
            array $handlers = [],
            array $processors = []
        ) {
            $logger = new Logger(strtolower($name));
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
            //$logger->pushHandler(new PsrHandler(LogglyLogger::create($loop, require SECRETS . 'loggly.php'), Logger::INFO));
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
                $logger->pushProcessor($handler);
            }

            $shutdown->subscribe(null, null, function () use ($logger) {
                $logger->setHandlers([
                    new PsrHandler(
                        new ConsoleLogger(
                            new ConsoleOutput(
                                ConsoleOutput::VERBOSITY_DEBUG,
                                true
                            )
                        )
                    ),
                ]);
            });

            return $logger;
        })->
            parameter('name', get('config.app.name'))->
            parameter('version', get('config.app.version'))->
            parameter('handlers', get('config.app.logger.handlers'))->
            parameter('processors', get('config.app.logger.processors')),
    ];
})();
