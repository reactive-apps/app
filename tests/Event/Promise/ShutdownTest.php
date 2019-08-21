<?php declare(strict_types=1);

namespace ReactiveApps\Tests;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReactiveApps\Event\Promise\Shutdown as ShutdownPromise;
use ReactiveApps\Event\Shutdown as ShutdownEvent;
use WyriHaximus\Broadcast\Dispatcher;

/**
 * @internal
 */
final class ShutdownTest extends TestCase
{
    /**
     * @test
     */
    public function promise(): void
    {
        $one = false;
        $two = false;

        $shutdownPromise = new ShutdownPromise();

        $dispatcher = new Dispatcher(new class($shutdownPromise) implements ListenerProviderInterface {
            private $shutdownPromise;

            public function __construct($shutdownPromise)
            {
                $this->shutdownPromise = $shutdownPromise;
            }

            public function getListenersForEvent(object $event): iterable
            {
                yield $this->shutdownPromise;
            }
        });

        $shutdownPromise->then(function () use (&$one): void {
            $one = true;
        });

        self::assertFalse($one);
        self::assertFalse($two);

        $dispatcher->dispatch(new ShutdownEvent());

        self::assertTrue($one);
        self::assertFalse($two);

        $shutdownPromise->then(function () use (&$two): void {
            $two = true;
        });

        self::assertTrue($one);
        self::assertTrue($two);
    }
}
