<?php declare(strict_types=1);

namespace ReactiveApps\Listener;

use React\EventLoop\LoopInterface;

final class Output
{
    /** @var LoopInterface */
    private $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function __invoke(): void
    {
        // Remove STD* streams from loop on shutdown
        $this->loop->addTimer(1.3, function (): void {
            $this->loop->removeReadStream(\STDIN);
            $this->loop->removeWriteStream(\STDOUT);
            $this->loop->removeWriteStream(\STDERR);
        });
    }
}
