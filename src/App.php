<?php declare(strict_types=1);

namespace ReactiveApps;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Event\Boot;
use ReactiveApps\Event\PreBoot;
use ReactiveApps\Event\Shutdown;
use Silly\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use WyriHaximus\PSR3\CallableThrowableLogger\CallableThrowableLogger;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;

final class App
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

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
     * @param LoopInterface            $loop
     * @param EventDispatcherInterface $eventDispatcher
     * @param Application              $application
     * @param OutputInterface          $output
     * @param LoggerInterface          $logger
     */
    public function __construct(LoopInterface $loop, EventDispatcherInterface $eventDispatcher, Application $application, OutputInterface $output, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->eventDispatcher = $eventDispatcher;
        $this->application = $application;
        $this->output = $output;
        $this->logger = new ContextLogger($logger, [], 'app');
    }

    public function boot(array $argv)
    {
        if ($this->booted === true) {
            $this->logger->emergency('Can\'t be booted twice');

            return;
        }

        $this->eventDispatcher->dispatch(new PreBoot());

        $this->booted = true;
        $this->logger->debug('Booting');

        $this->eventDispatcher->dispatch(new Boot());

        $exitCode = null;
        $this->loop->futureTick(function () use ($argv, &$exitCode): void {
            $this->logger->debug('Running');
        });
        $this->logger->debug('Starting loop');

        $exitCode = ExitCode::FAILURE;
        try {
            $exitCode = $this->application->run(new ArgvInput($argv), $this->output);
            $this->eventDispatcher->dispatch(new Shutdown());
        } catch (\Throwable $et) {
            CallableThrowableLogger::create($this->logger)($et);
        }

        $this->logger->debug('Execution completed with exit code: ' . $exitCode);

        return $exitCode;
    }
}
