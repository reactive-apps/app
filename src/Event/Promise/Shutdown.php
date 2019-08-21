<?php declare(strict_types=1);

namespace ReactiveApps\Event\Promise;

use React\Promise\PromiseInterface;

final class Shutdown implements PromiseInterface
{
    /** @var bool  */
    private $fulfilled = false;

    /** @var callable[]  */
    private $onFulfillQueue = [];

    public function __invoke(): void
    {
        $this->fulfilled = true;

        foreach ($this->onFulfillQueue as $onFulfilled) {
            $onFulfilled(true);
        }
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null): void
    {
        if ($this->fulfilled === false && $onFulfilled !== null) {
            $this->onFulfillQueue[] = $onFulfilled;

            return;
        }

        if ($this->fulfilled === true) {
            $onFulfilled(true);
        }
    }
}
