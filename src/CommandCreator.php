<?php declare(strict_types=1);

namespace ReactiveApps;

use Psr\Container\ContainerInterface;
use Recoil\Kernel;
use Recoil\React\ReactKernel;
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
     * @var Kernel
     */
    private $recoil;

    /**
     * @param LoopInterface      $loop
     * @param ContainerInterface $container
     */
    public function __construct(LoopInterface $loop, ContainerInterface $container)
    {
        $this->loop = $loop;
        $this->container = $container;
        $this->recoil = ReactKernel::create($loop);
    }

    public function create(string $class): array
    {
        $container = $this->container;
        $recoil = $this->recoil;
        $command = (new ReflectionClass($class))->getConstant('COMMAND');
        $parameters = [];
        foreach ((new BetterReflection())->classReflector()->reflect($class)->getMethod('__invoke')->getParameters() as $parameter) {
            $parameters[] = ((string)$parameter->getType()) . ' ' . ($parameter->isVariadic() ? '...' : '') . '$' . $parameter->getName();
        }

        $eval = 'return function (' . implode(', ', $parameters) . ') use ($class, $container, $recoil) {
            $command = $container->get($class);
            $args = func_get_args();
            $recoil->execute(function () use ($command, $args) {
                yield $command(...$args);
            });
        };';

        //echo $eval;
        $callable = eval($eval);

        return [$command, $callable];
    }
}
