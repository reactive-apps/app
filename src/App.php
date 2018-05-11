<?php declare(strict_types=1);

namespace ReactiveApps;

use ReactiveApps\CommandCreator;
use ReactiveApps\CommandLocator;
use ReactiveApps\Rx\Shutdown;
use React\EventLoop\LoopInterface;
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
     * @var CommandCreator
     */
    private $commandFactory;

    /**
     * @var bool
     */
    private $booted = false;

    /**
     * @param LoopInterface $loop
     * @param Shutdown $shutdown
     * @param CommandCreator $commandFactory
     */
    public function __construct(LoopInterface $loop, Shutdown $shutdown, CommandCreator $commandFactory)
    {
        $this->loop = $loop;
        $this->shutdown = $shutdown;
        $this->commandFactory = $commandFactory;
    }

    public function boot()
    {
        if ($this->booted === true) {
            return;
        }
        $this->booted = true;

        $this->loop->addSignal(SIGTERM, [$this->shutdown, 'onConplete']);

        $app = new Application('app name', 'dev');
        foreach (CommandLocator::locate() as $class) {
            $app->command(...$this->commandFactory->create($class));
        }
        $this->commandFactory = null;

        //$app->run(null, $output);
        $app->run(null, null);
    }
}
