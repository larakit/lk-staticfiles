<?php
namespace Larakit\StaticFiles;

class CommandDeploy extends \Illuminate\Console\Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'larakit:sf:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Выложить статику из зарегистрированных пакетов в DOCUMENT_ROOT';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->info(str_repeat('#',80));
        $this->info('# larakit:sf:deploy');
        $this->info(str_repeat('#',80));
        Manager::deploy($this->getOutput());
        $this->info('Статика успешно выложена!');
        //обновим хэш
        $file_config = config_path('larakit/lk-staticfiles/version.php');
        if(!file_exists(dirname($file_config))) {
            mkdir(dirname($file_config), 0777, true);
        }
        file_put_contents($file_config, '<?php return "' . date('YmdHis') . '";');
        //обновим ng-зависимости
        if(count(Package::$ng_modules)) {
            $file_ng = public_path('!/angular-larakit.js');
            if(!file_exists(dirname($file_ng))) {
                mkdir(dirname($file_ng), 0777, true);
            }
            $js = <<<JS
(function () {
angular
.module('larakit', 
%s);
})();
JS;
            file_put_contents($file_ng, sprintf($js,json_encode(array_keys(Package::$ng_modules), JSON_PRETTY_PRINT)));
        }
        $this->info('Обновлен хэш статики ');

    }

}
