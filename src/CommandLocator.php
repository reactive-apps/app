<?php declare(strict_types=1);

namespace ReactiveApps;

use function WyriHaximus\get_in_packages_composer_with_path;

final class CommandLocator
{
    public static function locate(): iterable
    {
        foreach (get_in_packages_composer_with_path('extra.reactive-apps.command') as $path => $namespacePrefix) {
            $directory = new \RecursiveDirectoryIterator($path);
            $directory = new \RecursiveIteratorIterator($directory);
            foreach ($directory as $fileinfo) {
                if (!$fileinfo->isFile()) {
                    continue;
                }
                $fileName = $path . \str_replace('/', '\\', $fileinfo->getFilename());
                $class = $namespacePrefix . '\\' . \substr(\substr($fileName, \strlen($path)), 0, -4);
                if (\class_exists($class) && !(new \ReflectionClass($class))->isInterface()) {
                    yield $class;
                }
            }
        }
    }
}
