<?php declare(strict_types=1);

namespace ReactiveApps;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Recoil\Kernel;
use ReflectionClass;
use Roave\BetterReflection\BetterReflection;
use WyriHaximus\Recoil\InfiniteCaller;
use WyriHaximus\Recoil\PromiseCoroutineWrapper;

final class CommandCreator
{
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
     * @param Kernel             $kernel
     * @param LoggerInterface    $logger
     * @param ContainerInterface $container
     */
    public function __construct(Kernel $kernel, LoggerInterface $logger, ContainerInterface $container)
    {
        $this->recoil = $kernel;
        $this->logger = $logger;
        $this->container = $container;
    }

    public function create(string $class): array
    {
        $container = $this->container;
        $recoil = $this->recoil;
        $logger = $this->logger;
        $coroutineWrapper = PromiseCoroutineWrapper::createFromQueueCaller(new InfiniteCaller($recoil));

        $command = (new ReflectionClass($class))->getConstant('COMMAND');
        $parameters = [];
        foreach ((new BetterReflection())->classReflector()->reflect($class)->getMethod('__invoke')->getParameters() as $parameter) {
            $parameters[] = ((string)$parameter->getType()) . ' ' . ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
        }

        $eval = 'return function (' . \implode(', ', $parameters) . ') use ($class, $container, $recoil, $logger) {
            $command = $container->get($class);
            $args = func_get_args();
            return $coroutineWrapper->call(function () use ($command, $args, $logger) {
                try {
                    return (yield $command(...$args));
                } catch (\Throwable $et) {
                    \WyriHaximus\PSR3\CallableThrowableLogger\CallableThrowableLogger::create($logger)($et);
                    return false;
                }
            });
        };';

        $callable = eval($eval);

        return [$command, $callable];
    }
}
