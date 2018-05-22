<?php declare(strict_types=1);

namespace ReactiveApps\Command;

use Psr\Container\ContainerInterface;
use ReactiveApps\CommandLocator;
use ReflectionClass;

class Multi implements Command
{
    const COMMAND = 'multi c*';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Multi constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(array $c)
    {
        foreach (CommandLocator::locate() as $class) {
            if (!in_array((new ReflectionClass($class))->getConstant('COMMAND'), $c, true)) {
                continue;
            }

            $command = $this->container->get($class);
            yield $command();
        }
    }
}
