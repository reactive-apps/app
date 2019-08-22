<?php declare(strict_types=1);

namespace ReactiveApps\Command;

use Psr\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\CommandLocator;
use ReactiveApps\ExitCode;
use Recoil\Kernel;
use ReflectionClass;
use function React\Promise\all;
use function React\Promise\resolve;
use WyriHaximus\Recoil\InfiniteCaller;
use WyriHaximus\Recoil\PromiseCoroutineWrapper;

class Multi implements Command
{
    const COMMAND = 'multi c*';

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var Kernel
     */
    private $recoil;

    /**
     * @var LoopInterface
     */
    private $logger;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param LoopInterface $loop
     * @param Kernel $recoil
     * @param LoopInterface $logger
     * @param ContainerInterface $container
     */
    public function __construct(LoopInterface $loop, Kernel $recoil, LoopInterface $logger, ContainerInterface $container)
    {
        $this->loop = $loop;
        $this->recoil = $recoil;
        $this->logger = $logger;
        $this->container = $container;
    }

    public function __invoke(array $c)
    {
        $coroutineWrapper = PromiseCoroutineWrapper::createFromQueueCaller(new InfiniteCaller($this->recoil));
        $promises = [];

        foreach (CommandLocator::locate() as $class) {
            if (!\in_array((new ReflectionClass($class))->getConstant('COMMAND'), $c, true)) {
                continue;
            }

            try {
                $command = $this->container->get($class);
                $promises[$class] = $coroutineWrapper->call(function () use ($command) {
                    return (yield $command());
                });
            } catch (\Throwable $throwable) {
                echo $throwable;
            }
        }

        return yield all($promises)->then(function ($exitCodes) {
            foreach ($exitCodes as $exitCode) {
                if ($exitCode !== ExitCode::SUCCESS) {
                    return $exitCode;
                }
            }

            ExitCode::SUCCESS;
        });
    }
}
