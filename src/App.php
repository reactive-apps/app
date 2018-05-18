<?php declare(strict_types=1);

namespace ReactiveApps;

use React\EventLoop\LoopInterface;
use ReactiveApps\Rx\Shutdown;
use Silly\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

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

        $this->loop->addSignal(SIGTERM, [$this->shutdown, 'onConplete']);

        $this->application->run(new ArgvInput($argv), $this->output);
    }
}
