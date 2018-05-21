<?php declare(strict_types=1);

namespace ReactiveApps;

use React\EventLoop\LoopInterface;
use ReactiveApps\Rx\Shutdown;
use Silly\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var bool
     */
    private $booted = false;

    /**
     * @param LoopInterface $loop
     * @param Shutdown $shutdown
     * @param Application $application
     * @param OutputInterface $output
     */
    public function __construct(LoopInterface $loop, Shutdown $shutdown, Application $application, OutputInterface $output)
    {
        $this->loop = $loop;
        $this->shutdown = $shutdown;
        $this->application = $application;
        $this->output = $output;
    }

    public function boot(array $argv)
    {
        if ($this->booted === true) {
            return;
        }
        $this->booted = true;

        $this->setUpSignals();

        $this->loop->futureTick(function () use ($argv) {
            try {
                $this->application->run(new ArgvInput($argv), $this->output);
            } catch (\Throwable $et) {
                echo (string)$et;
            }
        });

        $this->loop->run();
    }

    private function setUpSignals()
    {
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
