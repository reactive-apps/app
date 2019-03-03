<?php declare(strict_types=1);

namespace ReactiveApps;

use function WyriHaximus\get_in_packages_composer_path;

final class ConfigurationLocator
{
    public static function locate(): iterable
    {
        yield from self::requires(get_in_packages_composer_path('extra.reactive-apps.config'));
    }

    private static function requires(iterable $files): iterable
    {
        foreach ($files as $file) {
            yield from self::require($file);
        }
    }

    private static function require(string $file): iterable
    {
        if (\strpos($file, '*') !== false) {
            yield from self::requires(\glob($file));

            return;
        }

        yield from require $file;
    }
}
