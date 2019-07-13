<?php declare(strict_types=1);

namespace ReactiveApps\Command;

use React\EventLoop\LoopInterface;
use ReactiveApps\Rx\Shutdown;
use Silly\Application;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

class Index implements Command
{
    const COMMAND = 'list';

    /**
     * @var ListCommand
     */
    private $listCommand;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Shutdown
     */
    private $shutdown;

    /**
     * @param Shutdown        $shutdown
     * @param OutputInterface $output
     * @param Application     $app
     * @param LoopInterface   $loop
     */
    public function __construct(Shutdown $shutdown, OutputInterface $output, Application $app, LoopInterface $loop)
    {
        $listCommand = new ListCommand('list');
        $listCommand->setApplication($app);
        $this->listCommand = $listCommand;
        $this->output = $output;
        $this->shutdown = $shutdown;
        $loop->futureTick(function (): void {
            $this->shutdown->onCompleted();
        });
    }

    public function __invoke(): void
    {
        $this->listCommand->run(new ArgvInput(), $this->output);
    }
}
