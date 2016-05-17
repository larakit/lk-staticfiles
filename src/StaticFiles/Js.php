<?php
namespace Larakit\StaticFiles;

use \Illuminate\Support\Arr;

class Js extends File {

    /* внешние подключаемые скрипты */
    public $js_external = [];
    /* инлайн скрипты */
    public $js_inline = [];
    /* скрипты, которые должны быть выполнены при загрузке странице */
    public $js_onload = [];
    /* вставки в <noscript> */
    public $noscript = [];

    function clearAll() {
        return $this->clearExternal()
                    ->clearInline()
                    ->clearOnload();
    }

    function clearExternal() {
        $this->js_external = [];

        return $this;
    }

    function clearInline() {
        $this->js_inline = [];

        return $this;
    }

    function clearOnload() {
        $this->js_onload = [];

        return $this;
    }

    /**
     * @return Js
     */

    static function instance() {
        static $js;
        if(!isset($js)) {
            $js = new Js();
        }

        return $js;
    }

    /**
     * Подключение внешнего скрипта, реально лежащего в корне сайта
     *
     * @param string $js
     */
    function add($js, $condition = null, $no_build = false) {
        if(!$js) {
            return $this;
        }
        //если начинается с / значит надо в урл добавить хост
        $host = config('larakit.lk-staticfiles.host');
        if(mb_strpos($js, '/') === 0 && mb_strpos($js, '/', 1) !== 1) {
            $js = $host . $js;
        }

        $this->js_external[$js] = [
            'condition' => $condition,
            'no_build'  => $no_build,
        ];

        return $this;
    }

    /**
     * Добавление куска инлайн джаваскрипта
     *
     * @param <type> $js
     * @param mixed $id - уникальный флаг куска кода, чтобы можно
     *                  было добавлять в цикле и не бояться дублей
     */
    function addInline($js, $id = null) {
        if($id) {
            $this->js_inline[$id] = $js;
        } else {
            $this->js_inline[] = $js;
        }

        return $this;
    }

    /**
     * Добавление кода, который должен выполниться при загрузке страницы
     *
     * @param string $js
     * @param mixed  $id - уникальный флаг куска кода, чтобы можно
     *                   было добавлять в цикле и не бояться дублей
     */
    function addOnload($js, $id = null) {
        if($id) {
            $this->js_onload[$id] = $js;
        } else {
            $this->js_onload[] = $js;
        }

        return $this;
    }

    /**
     * Добавление HTML-кода, который должен быть вставлен в случае отключенного JS
     *
     * @param       $code
     * @param mixed $id  - уникальный флаг куска кода, чтобы можно
     *                   было добавлять в цикле и не бояться дублей
     *
     * @return $this
     */
    function addNoscript($code, $id = null) {
        if($id) {
            $this->noscript[$id] = $code;
        } else {
            $this->noscript[] = $code;
        }

        return $this;
    }

    /**
     * Использовать во View для вставки вызова всех скриптов
     * @return string
     */
    function __toString() {
        try {
            Manager::init();

            return trim($this->getExternal() . PHP_EOL . $this->getInline() . PHP_EOL . $this->getOnload() . $this->getNoscript());
        }
        catch(\Exception $e) {
            dd($e);
        }
    }

    function getLink($js, $condition = null, $is_need_hash = true) {
        $sign = (mb_strpos($js, '?') !== false) ? '&' : '?';
        $hash      = config('larakit.lk-staticfiles.version');
        $need_hash = (mb_strpos($js, $hash) === false);

        return ($condition ? '<!--[' . $condition . ']>' : '') . '<script type="text/javascript" ' . "" . 'src="' . $js . ($is_need_hash && $need_hash ? $sign . $hash : '') . '"></script>' . ($condition ? '<![endif]-->' : '');
    }

    function getNoscript() {
        $ret = [];
        if(count($this->noscript)){
            $ret[] = '<noscript>';
            foreach($this->noscript as $noscript){
                $ret[] = $noscript;
            }
            $ret[] = '</noscript>';
        }
        return implode(PHP_EOL, $ret);
    }

    /**
     * Только внешние скрипты
     * @return string
     */
    function getExternal() {
        if(!count($this->js_external)) {
            return '';
        }
        //если не надо собирать все в один билд-файл
        if(!config('larakit.lk-staticfiles.js.external.build')) {
            $js_code = '';
            foreach($this->js_external as $js => $_js) {
                $condition = Arr::get($_js, 'condition');
                //если надо подключать все по отдельности
                $js_code .= $this->getLink($js, $condition) . PHP_EOL;
            }

            return $js_code;
        } else {
            $build    = [];
            $no_build = [];
            $js_code  = '';
            foreach($this->js_external as $js => $_js) {
                $condition = Arr::get($_js, 'condition');
                if(Arr::get($_js, 'no_build')) {
                    $no_build[$condition][] = $js;
                } else {
                    $build[$condition][] = $js;
                }
            }
            foreach($no_build as $condition => $jses) {
                $js_code .='<!-- [no build] -->'.PHP_EOL;
                foreach($jses as $url) {
                    $js_code .= $this->getLink($url, $condition, false) . PHP_EOL;
                }
                $js_code .='<!-- [/no build] -->'.PHP_EOL;
            }
            foreach($build as $condition => $js) {
                $build_name = $this->makeFileName($this->js_external, 'js/external' . ($condition ? '/' . $condition : ''), 'js');
                $build_file = $this->buildFile($build_name);
                if(!file_exists($build_file)) {
                    //соберем билд в первый раз
                    $build = [];
                    foreach($js as $url) {
                        $parse = parse_url($url);
                        $host  = Arr::get($parse, 'host');
                        if(!$host) {
                            $_js = file_get_contents(public_path($url));
                        } else {
                            if(mb_substr($url, 0, 2) == '//') {
                                $url = 'http:' . $url;
                            }
                            $_js = file_get_contents($url);
                        }
                        $_js     = $this->prepare($_js, (mb_strpos($url, '.min.') !== false) || config('larakit.lk-staticfiles.js.external.min'));
                        $build[] = "/**********************************************************************" . PHP_EOL;
                        $build[] = '* ' . $url . PHP_EOL;
                        $build[] = "**********************************************************************/" . PHP_EOL;
                        $build[] = $_js . PHP_EOL . PHP_EOL;
                    }
                    //если требуется собирать инлайн скрипты в один внешний файл
                    $this->requireBuild($build_name, implode("", $build));
                }
                $js_code .= $this->getLink($this->buildUrl($build_name), $condition) . PHP_EOL;
            }

            //$build_name = $this->makeFileName($this->js_inline, 'js/onload', 'js');
            return $js_code;
        }
    }

    function requireBuild($build_name, $source) {
        $build_file = $this->buildFile($build_name);
        if(!file_exists($build_file)) {
            if(!file_exists(dirname($build_file))) {
                mkdir(dirname($build_file), 0777, true);
            }
            $this->save($build_file, trim($source));
        }
    }

    function prepare($source, $need_min) {
        if($need_min) {
            $source = \JSMin::minify($source);
        }

        return trim($source);
    }

    /**
     * Только инлайн
     * @return <type>
     */
    function getInline($as_html = true) {
        if(!$as_html) {
            return $this->js_inline;
        }
        if(!count($this->js_inline)) {
            return '';
        }
        $js_code = '';
        foreach($this->js_inline as $js) {
            $js_code .= $this->prepare($js, config('larakit.lk-staticfiles.js.inline.min')) . PHP_EOL;
        }
        $js_code = trim($js_code);
        if(!$js_code) {
            return '';
        }
        if(!config('larakit.lk-staticfiles.js.inline.build')) {
            return '<script type="text/javascript">' . PHP_EOL . $js_code . PHP_EOL . '</script>';
        }
        //если требуется собирать инлайн скрипты в один внешний файл
        $build_name = $this->makeFileName($this->js_inline, 'js/inline', 'js');
        $this->requireBuild($build_name, $js_code);

        return $this->getLink($this->buildUrl($build_name)) . PHP_EOL;
    }

    /**
     * Only onload
     * @return <type>
     */
    function getOnload($as_html = true) {
        if(!$as_html) {
            return $this->js_onload;
        }
        if(!count($this->js_onload)) {
            return '';
        }
        $js = '';
        foreach($this->js_onload as $k => $_js) {
            $js .= trim($_js) . PHP_EOL;
        }
        $js = str_replace(PHP_EOL, PHP_EOL . "\t", $js);
        $js = 'jQuery(document).ready(function(){' . PHP_EOL . "\t" . $js . PHP_EOL . '});';
        $js = $this->prepare($js, config('larakit.lk-staticfiles.js.onload.min'));
        if(!config('larakit.lk-staticfiles.js.onload.build')) {
            $ret = '<script type="text/javascript">' . PHP_EOL . $js . PHP_EOL . '</script>';

            return $ret;
        }
        //if need build onload in one file
        $build_name = $this->makeFileName($this->js_onload, 'js/onload', 'js');
        $this->requireBuild($build_name, $js);

        return $this->getLink($this->buildUrl($build_name)) . PHP_EOL;
    }

}