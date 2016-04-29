[![Total Downloads](https://poser.pugx.org/larakit/lk-staticfiles/d/total.svg)](https://packagist.org/packages/larakit/lk-staticfiles)
[![Latest Stable Version](https://poser.pugx.org/larakit/lk-staticfiles/v/stable.svg)](https://packagist.org/packages/larakit/lk-staticfiles)
[![Latest Unstable Version](https://poser.pugx.org/larakit/lk-staticfiles/v/unstable.svg)](https://packagist.org/packages/larakit/lk-staticfiles)
[![License](https://poser.pugx.org/larakit/lk-staticfiles/license.svg)](https://packagist.org/packages/larakit/lk-staticfiles)

#lk-staticfiles!

[![Join the chat at https://gitter.im/larakit/lk-staticfiles](https://badges.gitter.im/larakit/lk-staticfiles.svg)](https://gitter.im/larakit/lk-staticfiles?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
Библиотека для Laravel для управления статикой (сборка в один файл, сжатие JS/CSS, добавление хэша в URL для сброса кэша браузера)

##Step 1 
Подключаемые стили и скрипты вынесем в отдельный файл, для этого в **app/start/global.php** в самый конец добавим строку:
~~~
require app_path() . '/staticfiles.php';
~~~

##Step 2 
Создадим файл **app/staticfiles.php** и заполним его инструкциями по подключению JS/CSS
~~~
<?php
\LaraCss::add('http://fonts.googleapis.com/css?family=Open+Sans:300&subset=cyrillic')
   ->add('http://fonts.googleapis.com/css?family=Oswald:400,700,300')
   ->add('/packages/components/font-awesome/css/font-awesome.css')
   ->add('/packages/components/animate.css/animate.css')
   ->add('/packages/components/jquery-pace/jquery-pace.js')
   ->add('/packages/components/jquery-notific8/jquery.notific8.min.css')
;
\LaraJs::add('/!/build/bootstrap.min.js')
  ->add('/!/static/js/main.js')
;
~~~

##Step 3
Чтобы добавленные стили и скрипты вставились во все страницы сайта надо в вашем шаблоне прописать вызов
~~~
<html>
    <head>
        <title>title</title>
        {{ laracss() }}
    </head>
    <body>
        ...	
        {{ larajs() }} 
    </body>
</html>
~~~

##Step 4
После того как это сделано можете вызвать в консоли процедуру
~~~
php artisan latastatic:deploy 
~~~
которая произведет выкладку статики в public для каждого зарегистрированного пакета

# Как использовать готовые пакеты со статикой:
1) заходите на packagist.org и вписываете в поле поиска название нужного пакета, например jquery, bootstrap, jqueryui, angular, etc...
Скорее всего этот пакет будет у вендора "components"
>Внимание!<br />
>Пакеты подготовленные для работы с модулем помечены тегом "<a href="https://packagist.org/search/?tags=larastatic">larastatic</a>"
<br />

2) вписываете пакет в composer.json проекта
~~~ 
{
    "require": {
        "components/bootstrap": "*",
        "components/jqueryui": "*",
        "components/font-awesome": "*",
        "components/animate.css": "*",
        "components/jquery-pace": "*",
        "components/jquery-notific8": "dev-master",
        "components/jquery": "*"
    }, 
}
~~~ 
3) регистрируете пакет в приложении, чтобы можно было произвести выкладку статики
~~~ 
<?php
larastatic_register("<vendor>/<package>");
~~~ 
>**Внимание!!!**
>Вторым параметром функции регистрации пакета со статикой идет путь к выкладываемым файлам внутри пакета.
>По-умолчанию передается значение true, что означает, что статика будет искаться в поддиректории "public" как это принято в Laravel
>Если же выкладываемая статика находится в корне пакета, как например в "components/bootstrap", то следует вторым параметром передать пустую строку
>~~~ 
><?php
>larastatic_register('components/bootstrap', '');
>~~~ 
>Если же выкладываемая статика находится в какой то поддиректории пакета, как например в "components/animate.css", то следует вторым параметром передать эту поддиректорию
>~~~ 
><?php
>larastatic_register('components/animate.css', 'css');
>~~~ 
>и тогда будет выложена только нужная часть пакета, например, без исходных кодов
<br />

 
# Возможности и рекомендации
Пакет умеет собирать в один файл и минимизировать статику. Все билды версифицированы, что исключает кеширование на стороне клиента.<br />
Для режима разработки отключите сборку билдов, а на продакшн-сервере включите. <br />
Этим вы значительно уменьшите количество выполняемых к серверу запросов для получения статики.<br />
Для изменения дефолтных настроек модуля необходимо опубликовать их:
~~~
php artisan config:publish larakit/lk-staticfiles
~~~
Настройки окажутся окажутся в директории **app/config/packages/larakit/lk-staticfiles/** и станут доступными для переопределения.
