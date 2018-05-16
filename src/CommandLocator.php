<?php declare(strict_types=1);

namespace ReactiveApps;

use Composed\Package;
use function Composed\packages;
use function igorw\get_in;

final class CommandLocator
{
    public static function locate(): iterable
    {
        foreach (self::locations() as $path => $namespacePrefix) {
            $directory = new \RecursiveDirectoryIterator($path);
            $directory = new \RecursiveIteratorIterator($directory);
            foreach ($directory as $fileinfo) {
                if (!$fileinfo->isFile()) {
                    continue;
                }
                $fileName = $path . str_replace('/', '\\', $fileinfo->getFilename());
                $class = $namespacePrefix . '\\' . substr(substr($fileName, strlen($path)), 0, -4);
                if (class_exists($class) && !(new \ReflectionClass($class))->isInterface()) {
                    yield $class;
                }
            }
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
                    'command',
                ]
            );

            if ($commands === null) {
                continue;
            }

            foreach ($commands as $namespace => $path) {
                yield $package->getPath($path) => $namespace;
            }
        }
    }
}
