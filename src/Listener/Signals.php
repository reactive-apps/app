<?php declare(strict_types=1);

namespace ReactiveApps\Listener;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Event\Shutdown;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;

final class Signals
{
    private const SIGNALS = [
        SIGTERM,
        SIGINT,
    ];

    /** @var LoggerInterface */
    private $logger;

    /** @var LoopInterface */
    private $loop;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param LoggerInterface $logger
     * @param LoopInterface $loop
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(LoggerInterface $logger, LoopInterface $loop, EventDispatcherInterface $eventDispatcher)
    {
        $this->logger = new ContextLogger(
            $logger,
            [
                'listener' => 'signals',
            ],
            'signals'
        );
        $this->loop = $loop;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke()
    {

        $this->logger->debug('Setting up');

        $handler = function ($signal) use (&$handler) {
            switch ($signal) {
                case SIGTERM:
                    $signalName = 'SIGTERM';
                    break;
                case SIGINT:
                    $signalName = 'SIGINT';
                    break;
                default:
                    $signalName = 'unknown signal';
                    break;
            }
            $this->logger->debug('Caught: ' . $signalName);
            $this->eventDispatcher->dispatch(new Shutdown());
            $this->logger->notice('Shutdown issued');


            foreach (self::SIGNALS as $signal) {
                $this->loop->removeSignal($signal, $handler);
            }
        };

        foreach (self::SIGNALS as $signal) {
            $this->loop->addSignal($signal, $handler);
        }
    }
}
