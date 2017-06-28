<?php
$production = app()->environment() == 'production';
return [
    //css добавленный как \LaraCss::addInline('.label{color:red}');
    'inline'   => [
        //собирать в билд (true|false)
        'build' => (bool)env('LARAKIT_STATIC_CSS_INLINE_BUILD', $production),
        //минимизировать (true|false)
        'min'   => (bool)env('LARAKIT_STATIC_CSS_INLINE_MIN', $production),
    ],
    //css добавленный как \LaraCss::add('http://site.ru/!/static/css/styles.css');
    'external' => [
        //собирать в билд (true|false)
        'build' => (bool)env('LARAKIT_STATIC_CSS_EXTERNAL_BUILD', $production),
        //минимизировать (true|false)
        'min'   => (bool)env('LARAKIT_STATIC_CSS_EXTERNAL_MIN', $production),
    ],
];