<?php

if (!function_exists('laracss')) {
    function laracss() {
        return \Larakit\StaticFiles\Css::instance();
    }
}
if (!function_exists('larajs')) {
    function larajs() {
        return \Larakit\StaticFiles\Js::instance();
    }
}

//регистрируем сервис-провайдер
Larakit\Boot::register_provider('Larakit\StaticFiles\LarakitServiceProvider');
if (class_exists('Larakit\Twig')) {
    Larakit\Twig::register_function('larajs', function(){
        return \Larakit\StaticFiles\Js::instance();
    });
    Larakit\Twig::register_function('laracss', function(){
        return \Larakit\StaticFiles\Css::instance();
    });
}