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
~~~
./app/Http/staticfiles.php
~~~

Затем в файле 
~~~
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
~~~
$composer require larakit/lk-staticfiles
~~~
Далее нужно либо воспользоваться рекомендациями пакета https://github.com/larakit/lk-boot и произвести две правки
~~~
./app/Http/Kernel.php
~~~
и 
~~~
./config/app.php
~~~
либо руками зарегистрировать в 
~~~
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
~~
Проверим что все хорошо, для этого наберем в консоли команду:
~~~
berdnikov@redak34:/mnt/D/domains/staticfiles$ php artisan | grep larastatic
~~~
если вы видите вывод:
~~~
 larastatic
  larastatic:deploy    Выложить статику из зарегистрированных пакетов в DOCUMENT_ROOT
~~~
поздравляю, пакет был корректно установлен и инициализирован!

###3. Заполним staticfiles.php инструкциями по подключению CSS

Для начала посмотрим содержимое по-умолчанию главной страницы после установки фреймворка 
<img src="https://habrastorage.org/files/08c/17e/58c/08c17e58cecd427d9f9b27b3b49a90b2.png" />
и постараемся воспроизвести его при помощи данного пакета
~~~
<?php
\Larakit\StaticFiles\Css::instance()
    ->add('"https://fonts.googleapis.com/css?family=Lato:100')
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
~~~
./app/Http/staticfiles.php
~~~
Итак, дополним его согласно новым требованиям
~~~
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
~~~
./public/packages/...
~~~
запускается она так:
~~~
php artisan latastatic:deploy 
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

###7. Создание собственных пакетов

# Возможности и рекомендации
Пакет умеет собирать в один файл и минимизировать статику. Все билды версифицированы, что исключает кеширование на стороне клиента.<br />
Для режима разработки отключите сборку билдов, а на продакшн-сервере включите. <br />
Этим вы значительно уменьшите количество выполняемых к серверу запросов для получения статики.<br />
Для изменения дефолтных настроек модуля необходимо опубликовать их:
~~~
php artisan config:publish larakit/lk-staticfiles
~~~
Настройки окажутся окажутся в директории **app/config/packages/larakit/lk-staticfiles/** и станут доступными для переопределения.
