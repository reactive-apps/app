<?php declare(strict_types=1);

namespace ReactiveApps;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Event\Boot;
use ReactiveApps\Event\PreBoot;
use ReactiveApps\Event\Shutdown;
use Silly\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use WyriHaximus\PSR3\CallableThrowableLogger\CallableThrowableLogger;
use WyriHaximus\PSR3\ContextLogger\ContextLogger;

final class ExitCode
{
    public const SUCCESS = 0;
    public const FAILURE = 1;
}
