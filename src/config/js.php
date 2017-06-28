<?php
$production = app()->environment() == 'production';
return [
    //js добавленный как \LaraJs::addOnload('alert(123)'); - то что будет выполнено после загрузки страницы
    'onload'   => [
        //собирать в билд (true|false)
        'build' => (bool)env('LARAKIT_STATIC_JS_ONLOAD_BUILD', $production),
        //минимизировать (true|false)
        'min'   => (bool)env('LARAKIT_STATIC_JS_ONLOAD_MIN', $production),
    ],
    //js добавленный как \LaraJs::addInline('function ttt(){ return "ttt"; }');
    'inline'   => [
        //собирать в билд (true|false)
        'build' => (bool)env('LARAKIT_STATIC_JS_INLINE_BUILD', $production),
        //минимизировать (true|false)
        'min'   => (bool)env('LARAKIT_STATIC_JS_INLINE_MIN', $production),
    ],
    //js добавленный как \LaraJs::add('http://site.ru/!/static/js/styles.js');
    'external' => [
        //собирать в билд (true|false)
        'build' => (bool)env('LARAKIT_STATIC_JS_EXTERNAL_BUILD', $production),
        //минимизировать (true|false)
        'min'   => (bool)env('LARAKIT_STATIC_JS_EXTERNAL_MIN', $production),
    ],
];