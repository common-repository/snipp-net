<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd3796db0f7ca05fa93521f56b60e676e
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd3796db0f7ca05fa93521f56b60e676e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd3796db0f7ca05fa93521f56b60e676e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd3796db0f7ca05fa93521f56b60e676e::$classMap;

        }, null, ClassLoader::class);
    }
}
