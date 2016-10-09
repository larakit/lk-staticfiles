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
        $header = [
            'Package',
            'Exclude',
            'Include',
        ];
        $rows   = [];
        foreach(\Larakit\StaticFiles\Manager::packages() as $package_name => $p) {
            /** @var Package $p */
            foreach($p->getExclude() as $e) {
                $rows[] = [
                    $package_name,
                    $e,
                    '',
                ];
            }
            foreach($p->getInclude() as $i) {
                $rows[] = [
                    $package_name,
                    '',
                    $i,
                ];
            }
        }
        $this->table($header, $rows);
    }

}
