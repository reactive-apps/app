<?php declare(strict_types=1);

namespace ReactiveApps;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Rx\Shutdown;
use Silly\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use WyriHaximus\PSR3\CallableThrowableLogger\CallableThrowableLogger;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;

final class App
{
    private const SIGNALS = [
        SIGTERM,
        SIGINT,
    ];

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var Shutdown
     */
    private $shutdown;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $booted = false;

    /**
     * @param LoopInterface $loop
     * @param Shutdown $shutdown
     * @param Application $application
     * @param OutputInterface $output
     * @param LoggerInterface $logger
     */
    public function __construct(LoopInterface $loop, Shutdown $shutdown, Application $application, OutputInterface $output, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->shutdown = $shutdown;
        $this->application = $application;
        $this->output = $output;
        $this->logger = new ContextLogger($logger, [], 'app');

        $this->shutdown->subscribe(null, null, function () {
            $this->logger->notice('Shutdown issued');
        });
    }

    public function boot(array $argv)
    {
        if ($this->booted === true) {
            $this->logger->emergency('Can\'t be booted twice');
            return;
        }
        $this->booted = true;
        $this->logger->debug('Booting');

        $this->setUpSignals();

        $exitCode = null;
        $this->loop->futureTick(function () use ($argv, &$exitCode) {
            $this->logger->debug('Running');
            try {
                $exitCode = $this->application->run(new ArgvInput($argv), $this->output);
            } catch (\Throwable $et) {
                echo (string)$et;
                CallableThrowableLogger::create($this->logger)($et);
            }
        });

        $this->logger->debug('Starting loop');
        $this->loop->run();
        $this->logger->debug('Execution completed with exit code: ' . $exitCode);

        return $exitCode;
    }

    private function setUpSignals()
    {
        $this->logger->debug('Setting up signals');

        $handler = function () {
            $this->shutdown->onCompleted();
        };

        foreach (self::SIGNALS as $signal) {
            $this->loop->addSignal($signal, $handler);
        }

        $this->shutdown->subscribe(null, null, function () use ($handler) {
            foreach (self::SIGNALS as $signal) {
                $this->loop->removeSignal($signal, $handler);
            }
        });
    }
}
