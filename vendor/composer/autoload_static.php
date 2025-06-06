<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita55beeed69c4e37fca491b6c6d94f963
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Enad\\CountryData\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Enad\\CountryData\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInita55beeed69c4e37fca491b6c6d94f963::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita55beeed69c4e37fca491b6c6d94f963::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita55beeed69c4e37fca491b6c6d94f963::$classMap;

        }, null, ClassLoader::class);
    }
}
