<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4e34e47cc92464c2c82778535f37b115
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MenuEditorAdminify\\Inc\\' => 23,
            'MenuEditorAdminify\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MenuEditorAdminify\\Inc\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Inc',
        ),
        'MenuEditorAdminify\\' => 
        array (
            0 => __DIR__ . '/../..' . '/menu-editor-adminify',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4e34e47cc92464c2c82778535f37b115::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4e34e47cc92464c2c82778535f37b115::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4e34e47cc92464c2c82778535f37b115::$classMap;

        }, null, ClassLoader::class);
    }
}
