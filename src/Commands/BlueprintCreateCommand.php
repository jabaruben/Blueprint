<?php

namespace PHPJuice\Blueprint\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BlueprintCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blueprint:create
                            {name : name of the crud Ex: Post .}
                            {--api}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a crud blueprint json file under databases/blueprints';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // create default namings
        $isAPI = $this->option('api');
        $crudName = preg_replace('/crud$/i', '', $this->argument('name'));
        $crudName = str_singular($crudName);
        $modelName = str_singular($crudName);
        $controllerName = str_plural($crudName).'Controller';
        $tableName = str_plural(snake_case($crudName));
        $routeName = str_plural(snake_case($crudName, '-'));
        $fileName = snake_case($crudName, '_');

        $stub = __DIR__.'/../Stubs/blueprint.stub';

        // make blueprints directory
        if (!File::isDirectory($this->getBlueprintsDirectory())) {
            File::makeDirectory($this->getBlueprintsDirectory(), 0755, true);
        }

        // get blueprint path
        $path = $this->getBlueprintPath($fileName);
        // replace placholders
        $content = str_replace('{{crudName}}', $crudName, File::get($stub) );
        $content = str_replace('{{modelName}}', $modelName, $content  );
        $content = str_replace('{{controllerName}}', $controllerName, $content  );
        $content = str_replace('{{tableName}}', $tableName, $content  );
        $content = str_replace('{{routeName}}', $routeName, $content  );
        $content = str_replace('{{isAPI}}', $isAPI, $content );

        if ( File::put($path, $content ) ) {
            $this->info('blueprint file created successfully under:');
            $this->info($path);
        } else {
            $this->info('Error creating the blueprint file!');
        }
    }

    /**
     * Get the destination class path.
     *
     * @param  string $name
     * @return string
     */
    protected function getBlueprintPath($name = null)
    {
        return $this->getBlueprintsDirectory() . date('Y_m_d_His') .
            '_create_' . $name . '_crud_blueprint.json';
    }

    /**
     * Get the blueprints path.
     *
     * @param  string $name
     * @return string
     */
    protected function getBlueprintsDirectory()
    {
        return database_path() . "/blueprints/";
    }
}
