[![Total Downloads](https://poser.pugx.org/larakit/lk-staticfiles/d/total.svg)](https://packagist.org/packages/larakit/lk-staticfiles)
[![Latest Stable Version](https://poser.pugx.org/larakit/lk-staticfiles/v/stable.svg)](https://packagist.org/packages/larakit/lk-staticfiles)
[![Latest Unstable Version](https://poser.pugx.org/larakit/lk-staticfiles/v/unstable.svg)](https://packagist.org/packages/larakit/lk-staticfiles)
[![License](https://poser.pugx.org/larakit/lk-staticfiles/license.svg)](https://packagist.org/packages/larakit/lk-staticfiles)
[![License](https://poser.pugx.org/larakit/lk-staticfiles/license.svg)](https://packagist.org/packages/larakit/lk-staticfiles)
[![Gitter](https://badges.gitter.im/larakit/lk-staticfiles.svg)](https://gitter.im/larakit/lk-staticfiles?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=body_badge)
#[Larakit Staticfiles] Библиотека для управления статикой 
(сборка в один файл, сжатие JS/CSS, добавление хэша в URL для сброса кэша браузера)

Модуль был портирован с модуля для фреймворка Kohana, который служил мне верой и правдой более 5 лет ( и модуль, и Kohana :) )
(подробнее я описывал его тут https://habrahabr.ru/post/112852/)

Примечание: будем считать, что проект Laravel у вас уже создан

###1. Файл с правилами подключения статики

Для порядка добавим подключаемые стили и скрипты вынесем в отдельный файл, например 
~~~bash
./app/Http/staticfiles.php
~~~

Затем в файле 
~~~bash
./app/Http/routes.php
~~~

подключим его 

~~~php
<?php

Route::get('/', function () {
    return view('welcome');
});

require app_path('Http/staticfiles.php');
~~~

###2. Установим пакет

~~~bash
$composer require larakit/lk-staticfiles
~~~

Далее нужно либо воспользоваться рекомендациями пакета https://github.com/larakit/lk-boot и произвести две правки

~~~bash
./app/Http/Kernel.php
~~~

и 

~~~bash
./config/app.php
~~~

либо руками зарегистрировать в 

~~~bash
./config/app.php
~~~

сервис-провайдер "Larakit\StaticFiles\LarakitServiceProvider"

~~~php
<?php

return [
   ...
   'providers' => [
      ...,
      Larakit\StaticFiles\LarakitServiceProvider::class
   ],
   ...
];
~~~

Проверим что все хорошо, для этого наберем в консоли команду:

~~~bash
php artisan | grep larastatic
~~~

Если вы видите текст:

~~~bash
larastatic
  larastatic:deploy    Выложить статику из зарегистрированных пакетов в DOCUMENT_ROOT
~~~

поздравляю, пакет был корректно установлен и инициализирован!

###3. Заполним staticfiles.php инструкциями по подключению CSS

Для начала посмотрим содержимое по-умолчанию главной страницы после установки фреймворка 

<img src="https://habrastorage.org/files/08c/17e/58c/08c17e58cecd427d9f9b27b3b49a90b2.png" />

и постараемся воспроизвести его при помощи данного пакета

~~~php
<?php
\Larakit\StaticFiles\Css::instance()
    ->add('https://fonts.googleapis.com/css?family=Lato:100')
    ->addInline('
        html, body {
            height: 100%;
        }
        
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            display: table;
            font-weight: 100;
            font-family: "Lato";
        }
        
        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }
        
        .content {
            text-align: center;
            display: inline-block;
        }
        
        .title {
            font-size: 96px;
        }    
    ');
~~~

Чтобы добавленные стили и скрипты вставились во все страницы сайта надо в вашем шаблоне прописать вызов

<img src="https://habrastorage.org/files/5e5/11b/8c0/5e511b8c064b459485ad9ebbb665495a.png" />

Получилось!

Примечание: результаты выполнения
~~~php
\Larakit\StaticFiles\Css::instance()
    ->add('"https://fonts.googleapis.com/css?family=Lato:100')
    ->addInline('
        html, body {
            height: 100%;
        }
     ');
~~~        
и 
~~~php
\Larakit\StaticFiles\Css::instance()
    ->add('"https://fonts.googleapis.com/css?family=Lato:100');
    
\Larakit\StaticFiles\Css::instance()
    ->addInline('
        html, body {
            height: 100%;
        }
     ');
~~~     
будут одинаковы.


###4. Работа с JS
Она не намного сложнее работы с JS. 

Только помимо двух методов добавляющих стили по ссылке или методом inline-вставки как в CSS, здесь есть еще два метода:
- добавление onLoad
- добавление Noscript

Чтобы продемонстрировать функционал JS-менеджера - дополним пример: сделаем так, чтобы 
- после загрузки страницы вылетал алерт "привет!"
- загружалась яндекс карта
- при отключенном javascript показывался текст "Упс, включи JS!"

Напомню, все это мы делаем в файле
~~~bash
./app/Http/staticfiles.php
~~~
Итак, дополним его согласно новым требованиям

~~~php
\Larakit\StaticFiles\Js::instance()
    //подключим jQuery
    ->add('https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js')
    //подключим API яндекс карт
    ->add('//api-maps.yandex.ru/2.1/?lang=ru_RU')
    ->addInline('
        var myMap;
        // Дождёмся загрузки API и готовности DOM.
        ymaps.ready(init);
        function init () {
            // Создание экземпляра карты и его привязка к контейнеру с
            // заданным id ("map").
            myMap = new ymaps.Map("map", {
                // При инициализации карты обязательно нужно указать
                // её центр и коэффициент масштабирования.
                center: [55.76, 37.64], // Москва
                zoom: 10
            }, {
                searchControlProvider: "yandex#search"
            });
        }    
    ')
    //добавим привествие при загрузке страницы
    ->addOnload('alert("привет!")')
    //добавим сообщение при выключенном JS
    ->addNoscript('Упс, включи JS!');
//добавим необходиеы стили для карты
\Larakit\StaticFiles\Css::instance()
    ->addInline('
        #map {
            width: 100%;
            height: 200px;
        }   
    ');
~~~

Дополним и сам шаблон:
- добавим вызов функции вставки скриптов
- добавим контейнер для карты

<img src="https://habrastorage.org/files/223/aa8/614/223aa86141344708aa1a45e64350f7e0.png" />

Обновляем страницу:

1) alert при загрузке страницы вылетел
<img src="https://habrastorage.org/files/adc/ef1/040/adcef10404784adab5bb354cfc4a364a.png" />

2) Яндекс.карта появилась
<img src="https://habrastorage.org/files/092/0e4/cc0/0920e4cc08184f40bd515b17298d6abb.png" />

3) отключаем javascript в браузере и перезагружаем страницу - справа вверху видим надпись "Упс, включи JS!"
<img src="https://habrastorage.org/files/9fe/c00/36d/9fec0036d9cb4b248c64a8e820a31df9.png" />

Смотрим как были добавлены скрипты:
<img src="https://habrastorage.org/files/f0c/cca/1cd/f0ccca1cd9e84f52a7caad91a39cc366.png" />

###5. Способы распространения статики
Их всего три:
1) использование внешних ресурсов (простое подключение)
2) использование статики из DOCUMENT_ROOT (простое подключение)
3) использование статики из vendor-пакетов или node_modules (требует выкладки в DOCUMENT_ROOT)

Так вот для третьего пункта и существует команда, которая производит выкладку пакетов в директорию

~~~bash
./public/packages/...
~~~

запускается она так:

~~~bash
php artisan larastatic:deploy 
~~~
и после ее запуска будет произведена выкладка статики для каждого зарегистрированного пакета

# Как использовать готовые пакеты со статикой:
- заходите на github.com и вписываете в поле поиска "lk-staticfiles"
- находите пакет, например, https://github.com/larakit/sf-bootstrap
- внутри него уже есть подробнейшая документация по использованию
- подключаете его в композере
- и все!

он сам пропишется где надо и будет работать.

Понимаете? Никаких тебе танцев в написанием gulp/grunt-правил для каждого проекта.
Просто прописал в composer записимость, он установился и автоматически подключился на страницу.

Причем с возможностью отключения/включения на определенных роутах по маске (см. ниже)

###6. Встроенные возможности по клиентской оптимизации
 
В пакете предусмотрены следующие возможности:
- минимизация CSS [inline] - по умолчанию включена только на окружении "production"
- минимизация CSS [external] - по умолчанию включена только на окружении "production"
- минимизация JS [inline] - по умолчанию включена только на окружении "production"
- минимизация JS [external] - по умолчанию включена только на окружении "production"
- минимизация JS [onload] - по умолчанию включена только на окружении "production"
- сборка всех CSS в один билд-файл [inline] - по умолчанию включена только на окружении "production"
- сборка всех CSS в один билд-файл [external] - по умолчанию включена только на окружении "production"
- сборка всех JS в один билд-файл [inline] - по умолчанию включена только на окружении "production"
- сборка всех JS в один билд-файл [external] - по умолчанию включена только на окружении "production"
- сборка всех JS в один билд-файл [onload] - по умолчанию включена только на окружении "production"
- изменение хоста, с которого раздается статика - по умолчанию пустое значение, т.е. текущий домен

А теперь рассмотрим все по отдельности: для этого добавим еще стили и скрипты, размещенные внутри проекта
- /js/js.js
- /css/css.css

~~~php
<?php
\Larakit\StaticFiles\Css::instance()
    ->add('https://fonts.googleapis.com/css?family=Lato:100')
    ->add('/css/css.css')
    ->add('/css/css2.css')
    ->add('/css/css3.css')
    ->add('/css/css4.css')
    ->add('/css/css5.css')
    ->addInline('
        html, body {
            height: 100%;
        }
        
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            display: table;
            font-weight: 100;
            font-family: "Lato";
        }
        
        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }
        
        .content {
            text-align: center;
            display: inline-block;
        }
        
        .title {
            font-size: 96px;
        }    
    ');
\Larakit\StaticFiles\Js::instance()
    //подключим jQuery
    ->add('https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js',null,true)
    //подключим API яндекс карт
    ->add('//api-maps.yandex.ru/2.1/?lang=ru_RU',null,true)
    ->add('/js/js.js')
    ->add('/js/js2.js')
    ->add('/js/js3.js')
    ->add('/js/js4.js')
    ->add('/js/js5.js')
    ->addInline('
        var myMap;
        // Дождёмся загрузки API и готовности DOM.
        ymaps.ready(init);
        function init () {
            // Создание экземпляра карты и его привязка к контейнеру с
            // заданным id ("map").
            myMap = new ymaps.Map("map", {
                // При инициализации карты обязательно нужно указать
                // её центр и коэффициент масштабирования.
                center: [55.76, 37.64], // Москва
                zoom: 10
            }, {
                searchControlProvider: "yandex#search"
            });
        }    
    ')
    //добавим привествие при загрузке страницы
    ->addOnload('alert("привет!")')
    //добавим сообщение при выключенном JS
    ->addNoscript('Упс, включи JS!');
//добавим необходимые стили для карты
\Larakit\StaticFiles\Css::instance()
    ->addInline('
        #map {
            width: 100%;
            height: 200px;
        }   
    ');
~~~

**/js/js.js**
**/js/js2.js**
**/js/js3.js**
**/js/js4.js**
**/js/js5.js**
~~~js
confirm(
    "Выполнился скрипт js.js?"
);

~~~
**/css/css.css**
**/css/css2.css**
**/css/css3.css**
**/css/css4.css**
**/css/css5.css**
~~~css
body{
    color : #EB6251;;
}
~~~

Сохраним показания YSlow перед началом оптимизации

Так выглядели подключенные стили до оптимизации 

<img src="https://habrastorage.org/files/f8e/36d/dbe/f8e36ddbe389462e9a7a476fac10cf64.png"/>

А так скрипты

<img src="https://habrastorage.org/files/c3b/766/98b/c3b76698b7d24a24aec1522a1ee30475.png"/>



###6.1 Включение сборки CSS и JS в билд-файлы
Для этого в **.env** пропишем:

~~~
# включим сборку CSS [inline] в один файл
larastatic.css.inline.build=1

# включим сборку CSS [подключенные по ссылке] в один файл
larastatic.css.external.build=1

# включим сборку CSS [inline] в один файл
larastatic.js.inline.build=1

# включим сборку CSS [подключенные по ссылке] в один файл
larastatic.js.external.build=1

# включим сборку CSS [onload] в один файл
larastatic.js.onload.build=1
~~~

Теперь код нашей странички стал намного компактнее:

<img src="https://habrastorage.org/files/885/5e0/b2d/8855e0b2d4c8414f8048ca98d81e0d18.png"/>

###6.3 Исключение из билд-файла некоторых CSS и JS
Мы видим, что в билды попали файлы, которые не надо собирать в билды, а значит они должны отдаваться именно из того места, откуда были подключены:

Установим для них параметр no_build в true:
- в CSS это четвертый параметр
- в JS это третий параметр
~~~php
\Larakit\StaticFiles\Css::instance()
    ->add(
        //внешний стиль
        'https://fonts.googleapis.com/css?family=Lato:100',
        //media - условие использования, например "all" или "print"
        null, 
        //условие подключения, например "if IE 6"
        null,
        //no_build
        true
    )
    ...
;

\Larakit\StaticFiles\Js::instance()
    //подключим jQuery
    ->add(
        'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js',
        //условие подключения, например "if IE 6"
        null,
        //no_build
        true
    )
    //подключим API яндекс карт
    ->add(
        '//api-maps.yandex.ru/2.1/?lang=ru_RU',
        //условие подключения, например "if IE 6"
        null,
        //no_build
        true
    )
    ...
;
~~~

Результат:

<img src="https://habrastorage.org/files/c40/ca3/672/c40ca367207f494cbd5092a25fc83b21.png"/>

###6.2 Включение минимизации CSS и JS
Для этого в **.env** пропишем:

~~~
larastatic.css.inline.min=1
larastatic.css.external.min=1
~~~


Добавим в .env еще инструкций:
~~~
larastatic.css.inline.min=1
larastatic.css.external.min=1
larastatic.js.inline.min=1
larastatic.js.external.min=1
larastatic.js.onload.min=1
~~~

Выложим статику еще раз, 

~~~
php artisan larastatic:deploy
~~~

чтобы были перегенерированы ссылки на подключаемую статику для сброса кэша браузеров 

Продемонстрируем результаты работы на примере CSS

**Было**

<img src="https://habrastorage.org/files/c52/76a/2a0/c5276a2a0b4147ef935e0e556b39b9fd.png"/>

**Стало**

<img src="https://habrastorage.org/files/eec/677/c1a/eec677c1a4104b4ca16f572b91300614.png"/>

Продемонстрируем результаты работы на примере CSS

**Было**

<img src="https://habrastorage.org/files/353/d42/40d/353d4240dd114ee798f40d8c5b2daf68.png"/>

**Стало**

<img src="https://habrastorage.org/files/9f3/a3e/549/9f3a3e549bcf4f898f98dac4996389f7.png"/>

###6.3 Отдача статики с другого домена

Имитируем перенос статики на CDN, для этого создадим домен "st1.staticfiles" и сделаем его алиасом для домена "staticfiles".

Затем добавим в .env еще инструкций:
~~~
larastatic.host=http://st1.staticfiles
~~~

Результат:
<img src="https://habrastorage.org/files/e3a/0a2/c54/e3a0a2c540cc48b0af769c7bd61e5d42.png"/>

Смотрим показания YSlow перед началом оптимизации

<img src="https://habrastorage.org/files/ec7/025/f40/ec7025f4000a4163a6b881fa5fee5a0c.png" />

А теперь снова запускаем YSlow для проверки результатов нашей оптимизации:

<img src="https://habrastorage.org/files/2b3/ac1/963/2b3ac19639ee4c90899ae4df3221b7d3.png"/>

####Т.е. удалось поднять Overall performance score c 80 до 93

Смотрим показания YSlow перед началом оптимизации

<img src="https://habrastorage.org/files/0c6/1a5/df1/0c61a5df1c66486fb4d3f1a4766672d8.png" />

А теперь снова запускаем YSlow для проверки результатов нашей оптимизации:

<img src="https://habrastorage.org/files/373/924/b91/373924b91bb8477a967304526320bcbd.png"/>

Результат был бы еще выше, если бы мы использовали полноценные большевесные стили и скрипты в большом количестве, а не несколько пустышек, взятых для примеров.

По-моему, не плохо для коробочного решения делающего это автоматически.

###7. Создание собственных пакетов

Ну и, пожалуй, самое главное: возможность оформлять свои пакеты.
Сделаем два вида пакетов:
- с использованием CDN
- с распространением статики из пакета

Рекомендации:
- (для удобного поиска на packagist) обязательно вписывайте следующие теги "laravel, lk-staticfiles"
- (для удобного поиска на github) в Description репозитория на github вписывайте префикс "[Larakit][lk-staticfiles] "
<img src="https://habrastorage.org/files/8db/fb6/63e/8dbfb663e4954ba2bb7b4301e2c1ae50.png"/>

###7.1 Создание собственных пакетов с использованием CDN

Создаем composer.json
~~~json
{
    "name": "larakit/sf-bootstrap",
    "description": "sf-bootstrap",
    "keywords": [
        "larakit",
        "laravel",
        "laravel 5",
        "bootstrap",
        "lk-staticfiles"
    ],      
    "license": "MIT",
    "version": "3.3.6",
    "require": {
        "larakit/sf-jquery": "*"
    },
    "autoload": {
        "files": [
            "init.php"
        ]
    }
}
~~~
В разделе require укажите статические пакеты, от которых зависит ваш пакет (например, если вам нужен jQuery), а если таких нет впишите зависимость от текущего пакета

~~~json
{
    "require":{
            "larakit/lk-staticfiles":"*"
    },
}
~~~

В автоподключаемый файл **init.php** впишите инструкции по подключению и снова укажите зависимости от пакетов, чтобы они были подключены до создаваемого пакета

~~~php
<?php
\Larakit\StaticFiles\Manager::package('larakit/sf-bootstrap')
    ->usePackage('larakit/sf-jquery')
    ->js('//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/js/bootstrap.min.js')
    ->css('//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.min.css');
~~~

Собственно все!
Для использования вам достаточно прописать в композер проекта "require": "<vendor>/sf-ваш_пакет"

###7.2 Создание собственных пакетов с распространением статики из пакета
Точно также создаем **composer.json** и **init.php**. Единственное отличие: в **init.php** прописываем директорию со статикой, откуда будет производится ее выкладка в DOCUMENT_ROOT из vendor.

~~~php

<?php
\Larakit\StaticFiles\Manager::package('larakit/sf-larakit-js')
    
    //из этого относительного пути внутри пакета будет произведена выкладка
    ->setSourceDir('public')
    
    //обязательно подключить указанный зависимый пакет ПЕРЕД текущим пакетом
    ->usePackage('larakit/sf-jquery')
    
    //искать JS внутри директории SOurceDir
    ->jsPackage('js/larakit.js');
~~~

это означает, что при выкладке будет взято содержимое директории 
~~~
./vendor/larakit/sf-larakit-js/public
~~~
и выложено в 
~~~
./public/packages/larakit/sf-larakit-js/
~~~

Сама выкладка напомню производится путем запуска команды:
~~~
php artisan larastatic:deploy
~~~

Эта команда выполняет следующие действия:
- выкладывает в **./public/packages/** все зарегистрированные файлы из vendor пакетов
- обновляет хэш статики, чтобы сбросился кэш браузера (изменяется URL подключаемых стилей и скриптов)

###8. Правила включения/выключения пакетов
В процессе работы может возникнуть такая необходимость как точечное отключение или включение используемых пакетов.
Например, нам надо отключить пакет "larakit/sf-jquery" в админке.
Так как мы делали все роуты админки с префиксом "admin.", то у нас есть сейчас три роута:
- admin
- admin.users
- admin.news
- admin.pages

Инструкциию для управления подключением пакетов пишем в файле **./app/Http/staticfiles.php**:

####8.1 Отключение пакета jQuery только на главной странице админки
~~~php
\Larakit\StaticFiles\Manager::package('larakit/sf-jquery')
    ->setExclude('admin');
~~~

####8.2 Отключение пакета jQuery только на внутренних страницах админки
~~~php
\Larakit\StaticFiles\Manager::package('larakit/sf-jquery')
    ->setExclude('admin.*');
~~~

####8.3 Отключение пакета jQuery во всей админке
~~~php
\Larakit\StaticFiles\Manager::package('larakit/sf-jquery')
    ->setExclude('admin*');
~~~

####8.4 Отключение пакета jQuery во всей админке, кроме страницы управления пользователями
~~~php
\Larakit\StaticFiles\Manager::package('larakit/sf-jquery')
    ->setExclude('admin*')
    ->setInclude('admin.users');
~~~

####8.5 Отключение пакета jQuery на всем сайте, кроме страницы управления пользователями
~~~php
\Larakit\StaticFiles\Manager::package('larakit/sf-jquery')
    ->setExclude('*')
    ->setInclude('admin.users');
~~~

Указанные правила можно менять местами, будет применено правило, наиболее точно описывающее текущий роут, для которого производится попытка автоподключения пакетов статики.

###...
###Profit!
