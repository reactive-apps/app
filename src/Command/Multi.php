<?php declare(strict_types=1);

namespace ReactiveApps\Command;

use Psr\Container\ContainerInterface;
use function React\Promise\all;
use function React\Promise\resolve;
use ReactiveApps\CommandLocator;
use ReactiveApps\ExitCode;
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
        $promises = [];

        foreach (CommandLocator::locate() as $class) {
            if (!\in_array((new ReflectionClass($class))->getConstant('COMMAND'), $c, true)) {
                continue;
            }

            $command = $this->container->get($class);
            $promises[] = resolve(yield $command());
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
