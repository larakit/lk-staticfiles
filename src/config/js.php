<?php
$production = app()->environment() == 'production';
return [
    //js добавленный как \LaraJs::addOnload('alert(123)'); - то что будет выполнено после загрузки страницы
    'onload'   => [
        //собирать в билд (true|false)
        'build' => (bool)env('larastatic.js.onload.build', $production),
        //минимизировать (true|false)
        'min'   => (bool)env('larastatic.js.onload.min', $production),
    ],
    //js добавленный как \LaraJs::addInline('function ttt(){ return "ttt"; }');
    'inline'   => [
        //собирать в билд (true|false)
        'build' => (bool)env('larastatic.js.inline.build', $production),
        //минимизировать (true|false)
        'min'   => (bool)env('larastatic.js.inline.min', $production),
    ],
    //js добавленный как \LaraJs::add('http://site.ru/!/static/js/styles.js');
    'external' => [
        //собирать в билд (true|false)
        'build' => (bool)env('larastatic.js.external.build', $production),
        //минимизировать (true|false)
        'min'   => (bool)env('larastatic.js.external.min', $production),
    ],
];