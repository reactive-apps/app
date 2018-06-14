<?php declare(strict_types=1);

namespace ReactiveApps;

use function WyriHaximus\get_in_packages_composer_path;

final class ConfigurationLocator
{
    public static function locate(): iterable
    {
        foreach (get_in_packages_composer_path('extra.reactive-apps.config') as $path) {
            yield from require $path;
        }
    }
}
