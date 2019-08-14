<?php declare(strict_types=1);

namespace ReactiveApps\Command;

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
     * @param OutputInterface $output
     * @param Application     $app
     */
    public function __construct(OutputInterface $output, Application $app)
    {
        $listCommand = new ListCommand('list');
        $listCommand->setApplication($app);
        $this->listCommand = $listCommand;
        $this->output = $output;
    }

    public function __invoke()
    {
        $this->listCommand->run(new ArgvInput(), $this->output);

        return true;
    }
}
