<?php declare(strict_types=1);

use Monolog\ErrorHandler;
use Psr\Log\LoggerInterface;

return (function () {
    return [
        ErrorHandler::class => function (LoggerInterface $logger) {
            return ErrorHandler::register($logger);
        },
    ];
})();