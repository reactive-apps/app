<?php declare(strict_types=1);

namespace ReactiveApps;

use Composed\Package;
use function Composed\packages;
use function igorw\get_in;

final class ConfigurationLocator
{
    public static function locate(): iterable
    {
        foreach (self::locations() as $path) {
            yield from require $path;
        }
    }

    public static function locations(): iterable
    {
        /** @var Package $package */
        foreach (packages(true) as $package) {
            $config = $package->getConfig('extra');

            if ($config === null) {
                continue;
            }

            $commands = get_in(
                $config,
                [
                    'reactive-apps',
                    'config',
                ]
            );

            if ($commands === null) {
                continue;
            }

            foreach ($commands as $namespace => $path) {
                yield $package->getPath($path);
            }
        }
    }
}
