<?php
namespace Larakit\StaticFiles;

class CommandConditions extends \Illuminate\Console\Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'larakit:sf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Посмотреть условия подключения JS/CSS';

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
        $static_files = app_path('Http/staticfiles.php');
        if(file_exists($static_files)) {
            require_once $static_files;
        }
        $header = [
            'Package',
            'Exclude',
            'Include',
            'Required',
        ];
        $rows   = [];
        foreach(\Larakit\StaticFiles\Manager::packages() as $package_name => $p) {
            $rows[] = [
                $package_name,
                implode(', ', $p->getExclude()),
                implode(', ', $p->getInclude()),
                implode(', ', $p->getRequired()),
            ];
        }
        $this->table($header, $rows);
    }

}
