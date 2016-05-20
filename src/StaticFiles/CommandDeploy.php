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
        Manager::deploy($this->getOutput());
        $this->info('Статика успешно выложена!');
        //обновим хэш
        $file_config      = config_path('larakit/lk-staticfiles/version.php');
        if(!file_exists(dirname($file_config))){
            mkdir(dirname($file_config),0777, true);
        }
        file_put_contents($file_config, '<?php return "' . date('YmdHis') . '";');
        $this->info('Обновлен хэш статики ');

    }

}
