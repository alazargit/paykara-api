<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2ebf2230b20b9266ac443e09cc2cf762
{
    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PayKara' => 
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit2ebf2230b20b9266ac443e09cc2cf762::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit2ebf2230b20b9266ac443e09cc2cf762::$classMap;

        }, null, ClassLoader::class);
    }
}
