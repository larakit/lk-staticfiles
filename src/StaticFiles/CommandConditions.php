<?php
namespace Larakit\StaticFiles;

class CommandConditions extends \Illuminate\Console\Command {
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'larakit:show:sf';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Показать условия подключения JS/CSS';
    
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
        $static_files = base_path('bootstrap/staticfiles.php');
        if(file_exists($static_files)) {
            require_once $static_files;
        }
        
        $header = [
            'Package',
            'Exclude',
            'Include',
        ];
        $rows   = [];
        foreach(\Larakit\StaticFiles\Manager::packages() as $package_name => $p) {
            $rows[] = [
                $package_name,
                implode(', ', $p->getExclude()),
                implode(', ', $p->getInclude()),
            ];
        }
        $this->table($header, $rows);
        $header = [
            'Package',
            'Require',
        ];
        $rows   = [];
        foreach(\Larakit\StaticFiles\Manager::packages() as $package_name => $p) {
            $requires = (array) $p->getRequired();
            if(count($requires)) {
                $rows[] = [
                    $package_name,
                    ''
                ];
                foreach($requires as $require) {
                    $rows[] = [
                        '',
                        $require,
                    ];
                }
            }
        }
        $this->table($header, $rows);
    }
    
}
