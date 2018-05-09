<?php

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

return (function () {
    return [
        LoopInterface::class => function () {
            return Factory::create();
        },
    ];
})();
