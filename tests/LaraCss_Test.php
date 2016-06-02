<?php

class LaraCss_Test extends TestCase {

    /**
     * @var \Larakit\StaticFiles\Css
     */
    protected static $css;

    protected function setUp() {
        $this->createApplication();
        self::$css = \Larakit\StaticFiles\Css::instance();
        self::$css->clearAll();
        Config::set('larakit.lk-staticfiles.version', 'hash');
    }

    public function testAddExternal() {
        self::$css->add('http://ya.ru/css.css', 'print', 'if IE7', true);
        //в сгенерированном виде
        $expected   = [];
        $expected[] = "        <!--[if IE7]><link media=\"print\" type=\"text/css\" rel=\"stylesheet\" href=\"http://ya.ru/css.css?hash\" /><![endif]-->";
        $expected[] = '';
       // dd(self::$css->getExternal());
        $this->assertEquals(implode(PHP_EOL, $expected), self::$css->getExternal());
    }

    public function testAddInline() {
        self::$css->addInline('h1{
            font-size:30px
        }');
        $expected   = [];
        $expected[] = '<style type="text/css">';
        $expected[] = 'h1{';
        $expected[] = '            font-size:30px';
        $expected[] = '        }';
        $expected[] = '</style>';

        //dd(self::$css->getInline());
        $this->assertEquals(implode(PHP_EOL, $expected), self::$css->getInline());
    }


}