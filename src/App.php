<?php declare(strict_types=1);

namespace ReactiveApps;

use React\EventLoop\LoopInterface;
use ReactiveApps\Rx\Shutdown;
use Silly\Application;

final class App
{
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
     * @var bool
     */
    private $booted = false;

    /**
     * @param LoopInterface $loop
     * @param Shutdown $shutdown
     * @param Application $application
     */
    public function __construct(LoopInterface $loop, Shutdown $shutdown, Application $application)
    {
        $this->loop = $loop;
        $this->shutdown = $shutdown;
        $this->application = $application;
    }

    public function boot()
    {
        if ($this->booted === true) {
            return;
        }
        $this->booted = true;

        $this->loop->addSignal(SIGTERM, [$this->shutdown, 'onConplete']);

        //$this->application->run(null, $output);
        $this->application->run(null, null);
    }
}
