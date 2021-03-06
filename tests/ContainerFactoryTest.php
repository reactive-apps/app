<?php declare(strict_types=1);

namespace ReactiveApps\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReactiveApps\ContainerFactory;

/**
 * @internal
 */
final class ContainerFactoryTest extends TestCase
{
    public function testConfig(): void
    {
        /** @var ContainerInterface $container */
        $container = ContainerFactory::create();
        /*var_export([$container]);
                self::assertTrue($container->has('config'));
                self::assertTrue($container->has('config.foo'));
                self::assertTrue($container->has('config.foo.bar'));*/
        self::assertEquals('baz', $container->get('config.foo.bar'));
        self::assertEquals('baz', $container->get('config.foo.bar'));
    }
}
