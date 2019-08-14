<?php declare(strict_types=1);

namespace ReactiveApps\Listener;

use Monolog\Handler\PsrHandler;
use Monolog\Logger as Monolog;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

final class Logger
{
    /** @var Monolog */
    private $logger;

    public function __construct(Monolog $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(): void
    {
        $this->logger->setHandlers([
            new PsrHandler(
                new ConsoleLogger(
                    new ConsoleOutput(
                        ConsoleOutput::VERBOSITY_DEBUG,
                        true
                    )
                )
            ),
        ]);
    }
}
