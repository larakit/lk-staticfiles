<?php

namespace Larakit\StaticFiles;

use Illuminate\Support\Arr;
use Symfony\Component\Console\Output\OutputInterface;

class Package {

    protected $css        = [];
    protected $js         = [];
    protected $require    = [];
    protected $package;
    protected $include    = [];
    protected $exclude    = [];
    protected $is_used    = false;
    protected $source_dir = null;

    /**
     * Список именованных наборов
     * @var array
     */
    protected $scopes = [];

    function __construct($package) {
        $this->package = $package;
    }

    /**
     * Добавить пакет, от которого есть зависимость
     *
     * @param $package
     *
     * @return $this
     */
    function usePackage($package) {
        $this->require[$package] = $package;

        return $this;
    }

    /**
     * Добавить внешний JS
     *
     * @param      $js
     * @param null $condition
     * @param bool $no_build
     *
     * @return $this
     */
    function js($js, $condition = null, $no_build = false) {
        $this->js[$js] = compact('condition', 'no_build');

        return $this;
    }

    /**
     * Добавить внешний CSS
     *
     * @param      $css
     * @param null $media
     * @param null $condition
     * @param bool $no_build
     *
     * @return $this
     */
    function css($css, $media = null, $condition = null, $no_build = false) {
        $this->css[$css] = compact('media', 'condition', 'no_build');

        return $this;
    }

    /**
     * Добавить JS из пакета
     *
     * @param      $js
     * @param null $condition
     * @param bool $no_build
     *
     * @return Package
     */
    function jsPackage($js, $condition = null, $no_build = false) {
        return $this->js('/packages/' . $this->package . '/' . $js, $condition, $no_build);
    }

    /**
     * Добавить CSS из пакета
     *
     * @param      $css
     * @param null $media
     * @param null $condition
     * @param bool $no_build
     *
     * @return Package
     */
    function cssPackage($css, $media = null, $condition = null, $no_build = false) {
        return $this->css('/packages/' . $this->package . '/' . $css, $media, $condition, $no_build);
    }

    /**
     * Добавить именованный набор внутри пакета
     *
     * Manager::package('larakit/sf-flot')
     *          ->scopeInit('pie', ['/packages/larakit/sf-flot/js/jquery.flot.pie.js']);
     *
     * @param       $scope
     * @param array $js
     * @param array $css
     *
     * @return Package
     */
    function scopeInit($scope, $js = [], $css = []) {
        $this->scopes[$scope] = compact('js', 'css');

        return $this;
    }

    /**
     * Подключить именованный набор
     *
     * Manager::package('larakit/sf-flot')
     *          ->scope('pie');
     * В итоге вместе с базовыми стилями и скриптами пакета "sf-flot" подключатся скрипты и стили набора pie
     *
     * @param $scope
     *
     * @return Package
     */
    function scope($scope) {
        $styles  = (array) Arr::get($this->scopes, $scope . '.css', []);
        $scripts = (array) Arr::get($this->scopes, $scope . '.js', []);
        foreach($styles as $style) {
            $this->css($style);
        }
        foreach($scripts as $script) {
            $this->js($script);
        }

        return $this;
    }

    /**
     * Очистить список подключаемых CSS в пакете
     * для задания своего
     * @return $this
     */
    function clearCss() {
        $this->css = [];

        return $this;
    }

    /**
     * Очистить список подключаемых JS в пакете
     * для задания своего
     * @return $this
     */
    function clearJs() {
        $this->js = [];

        return $this;
    }

    /**
     * Установить поддиректорию внутри пакета (public/dist),
     * из которой надо сделать выкладку в /packages/<package>/*
     *
     * @param $dir
     *
     * @return $this
     */
    function setSourceDir($dir) {
        $this->source_dir = rtrim(rtrim($dir, '/'), '\\');

        return $this;
    }

    /**
     * Перезаписать список роутов, где пакет должен быть выключен
     *
     * @param $exclude
     *
     * @return $this
     */
    function setExclude($exclude) {
        $this->exclude = (array) $exclude;

        return $this;
    }

    /**
     * Добавить список роутов, где пакет должен быть выключен
     *
     * @param $exclude string|array
     *
     * @return $this
     */
    function addExclude($exclude) {
        $exclude = func_get_args();
        foreach($exclude as $v) {
            $this->exclude = array_merge($this->exclude, (array) $v);
        }

        return $this;
    }

    /**
     * Перезаписать список роутов, где пакет должен быть включен
     *
     * @param $include
     *
     * @return $this
     */
    function setInclude($include) {
        $this->include = (array) $include;

        return $this;
    }

    /**
     * Добавить список роутов, где пакет должен быть включен
     *
     * @param $include string|array
     *
     * @return $this
     */
    function addInclude($include) {
        $include = func_get_args();
        foreach($include as $v) {
            $this->include = array_merge($this->include, (array) $v);
        }

        return $this;
    }

    /**
     * Включение пакета с учетом правил использования include/exclude
     * @return bool
     */
    function on() {
        if($this->is_used) {
            return true;
        }
        $route   = \Route::currentRouteName();
        $exclude = self::maxIs($this->exclude, $route);
        $include = self::maxIs($this->include, $route);
        if($exclude > $include || true === $exclude) {
            // исключаем
        } else {
            // подключаем
            //сперва подключим на страницу зависимости
            foreach((array) $this->require as $require) {
                Manager::package($require)
                       ->on();
            }

            //затем подключим CSS
            foreach($this->css as $url => $item) {
                $condition = Arr::get($item, 'condition', null);
                $media     = Arr::get($item, 'media', null);
                $no_build  = (bool) Arr::get($item, 'no_build', false);
                Css::instance()
                   ->add($url, $media, $condition, $no_build);
            }
            //затем подключим JS
            foreach($this->js as $url => $item) {
                $condition = Arr::get($item, 'condition', null);
                $no_build  = (bool) Arr::get($item, 'no_build', false);
                Js::instance()
                  ->add($url, $condition, $no_build);
            }
        }
        $this->is_used = true;

        return true;
    }

    /**
     * @param $input_array
     * @param $search
     *
     * @return bool|int
     */
    static protected function maxIs($input_array, $search) {
        $ret = 0;
        foreach((array) $input_array as $input) {
            $is = self::is($input, $search);
            if(true === $is) {
                return true;
            }
            $ret = max($is, $ret);
        }

        return $ret;
    }

    /**
     * @param $search
     * @param $current
     *
     * @return bool|int
     */
    static protected function is($search, $current) {
        if($search == $current) {
            return true;
        }
        if('*' == $search) {
            return 1;
        }

        $pattern = preg_quote($search, '#');
        $pattern = str_replace('\*', '.*', $pattern) . '\z';
        $match   = (bool) preg_match('#^' . $pattern . '#', $current);
        if($match) {
            return mb_strlen($search);
        }

        return false;

    }

    /**
     * Выкладка пакета
     *
     * @return bool
     */
    function deploy($output = null) {
        if(is_null($this->source_dir)) {
            return false;
        }
        if($output) {
            $styled = '<comment>Package "' . $this->package . '"</comment> deployed in <error>"/packages/' . $this->package . '"</error>';
            $output->writeln($styled);
        }
        \File::copyDirectory(base_path('vendor/' . $this->package . '/' . $this->source_dir), public_path('packages/' . $this->package));

        return true;
    }

}