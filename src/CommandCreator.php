<?php

namespace ReactiveApps;

use Psr\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use ReactiveApps\Command\Command;
use ReflectionClass;
use Roave\BetterReflection\BetterReflection;

final class CommandCreator
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param LoopInterface $loop
     * @param ContainerInterface $container
     */
    public function __construct(LoopInterface $loop, ContainerInterface $container)
    {
        $this->loop = $loop;
        $this->container = $container;
    }

    public function create(string $class): array
    {
        $container = $this->container;
        $loop = $this->loop;
        $command = (new ReflectionClass($class))->getConstant('COMMAND');
        $parameters = [];
        foreach ((new BetterReflection())->classReflector()->reflect($class)->getMethod('__invoke')->getParameters() as $parameter) {
            $parameters[] = ((string)$parameter->getType()) . ' ' . ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
        }

        $eval = 'return function (' . implode(', ', $parameters) . ') use ($class, $container, $loop) {
            $container->get($class)(...func_get_args());
            $loop->run();
        };';

        //echo $eval;
        $callable = eval($eval);

        return [$command, $callable];
    }
}
