<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita8b8f92c9a9459ec18ed2469f38afd49
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Tickets\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Tickets\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita8b8f92c9a9459ec18ed2469f38afd49::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita8b8f92c9a9459ec18ed2469f38afd49::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita8b8f92c9a9459ec18ed2469f38afd49::$classMap;

        }, null, ClassLoader::class);
    }
}
