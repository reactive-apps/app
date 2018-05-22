<?php declare(strict_types=1);

use Clue\React\Stdio\Stdio;
use React\EventLoop\LoopInterface;
use ReactiveApps\Rx\Shutdown;
use Symfony\Component\Console\Output\OutputInterface;
use WyriHaximus\React\Symfony\Console\StdioOutput;

return (function () {
    return [
        OutputInterface::class => function (LoopInterface $loop, Shutdown $shutdown) {
            // Remove STD* streams from loop on shutdown
            $shutdown->subscribe(null, null, function () use ($loop) {
                $loop->addTimer(0.3, function () use ($loop) {
                    $loop->removeReadStream(STDIN);
                    $loop->removeWriteStream(STDOUT);
                    $loop->removeWriteStream(STDERR);
                });
            });

            return new StdioOutput(new Stdio($loop), StdioOutput::VERBOSITY_NORMAL, true);
        },
    ];
})();
