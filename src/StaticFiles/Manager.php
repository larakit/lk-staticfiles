<?php

namespace Larakit\StaticFiles;

class Manager {

    static protected $packages = [];
    static protected $is_init  = false;

    static function packages() {
        return self::$packages;
    }

    /**
     * @param      $package
     *
     * @return Package
     */
    static function package($package) {
        if(!isset(self::$packages[$package])) {
            self::$packages[$package] = new Package($package);
        }

        return self::$packages[$package];
    }

    static function init() {
        if(self::$is_init) {
            return true;
        }
        $static_files = app_path('Http/staticfiles.php');
        if(file_exists($static_files)) {
            require_once $static_files;
        }
        foreach(self::$packages as $package_name => $package) {
            /** @var $package Package */
            $package->on();
        }

        return (self::$is_init = true);
    }

    static function deploy($output = null) {
        foreach(self::$packages as $package_name => $package) {
            /** @var $package Package */
            $package->deploy($output);
        }
    }
}