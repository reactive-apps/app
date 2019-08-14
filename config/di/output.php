<?php declare(strict_types=1);

use Clue\React\Stdio\Stdio;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WyriHaximus\React\Symfony\Console\StdioOutput;

return (function () {
    return [
        OutputInterface::class => function (LoopInterface $loop) {
            return new StdioOutput(new Stdio($loop), StdioOutput::VERBOSITY_NORMAL, true);
        },
    ];
})();
