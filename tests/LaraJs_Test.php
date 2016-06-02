<?php

class LaraJs_Test extends TestCase {

    /**
     * @var \Larakit\StaticFiles\Js
     */
    protected static $js;

    protected function setUp() {
        $this->createApplication();
        self::$js = \Larakit\StaticFiles\Js::instance();
        self::$js->clearAll();
        Config::set('larakit.lk-staticfiles.version', 'hash');
    }

    public function testAddExternal() {
        self::$js->add('http://ya.ru/js.js', 'if IE7', true);
        //в сгенерированном виде
        $expected   = [];
        $expected[] = '<!--[if IE7]><script type="text/javascript" src="http://ya.ru/js.js?hash"></script><![endif]-->';
        $expected[] = '';
        $this->assertEquals(implode(PHP_EOL, $expected), self::$js->getExternal());
    }

    public function testAddInline() {
        self::$js->addInline('alert("inline");');
        $expected   = [];
        $expected[] = '<script type="text/javascript">';
        $expected[] = 'alert("inline");';
        $expected[] = '</script>';
        $this->assertEquals(implode(PHP_EOL, $expected), self::$js->getInline());
    }

    public function testAddOnload() {
        $text = "alert(\"onload\");";
        self::$js->addOnload($text);

        $expected   = [];
        $expected[] = '<script type="text/javascript">';
        $expected[] = 'jQuery(document).ready(function(){';
        $expected[] = "\talert(\"onload\");";
        $expected[] = "\t";
        $expected[] = '});';
        $expected[] = '</script>';

        $this->assertEquals(implode(PHP_EOL, $expected), self::$js->getOnload());
    }

    public function testGetNoscript() {
        $text = "Your browser does not support JavaScript!";
        self::$js->addNoscript($text);

        $expected   = [];
        $expected[] = '<noscript>';
        $expected[] = $text;
        $expected[] = '</noscript>';

        $this->assertEquals(implode(PHP_EOL, $expected), self::$js->getNoscript());
    }

}