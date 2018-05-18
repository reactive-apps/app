<?php declare(strict_types=1);

namespace ReactiveApps\Command;

use React\EventLoop\LoopInterface;
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
     * @param ListCommand $listCommand
     * @param OutputInterface $output
     */
    public function __construct(ListCommand $listCommand, OutputInterface $output, Application $app, LoopInterface $loop)
    {
        $listCommand->setApplication($app);
        $this->listCommand = $listCommand;
        $this->output = $output;

        /**
         * Since this is a non-event-loop using command stop it after the first tick
         */
        $loop->futureTick(function () use ($loop) {
            $loop->stop();
        });
    }

    public function __invoke()
    {
        $this->listCommand->run(new ArgvInput(), $this->output);
    }
}
