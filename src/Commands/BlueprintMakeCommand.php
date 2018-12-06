<?php

namespace PHPJuice\Blueprint\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BlueprintMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blueprint:make
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
        if ($this->runningWithDatabase()) {
            //$this->runInDatabaseMode();
        }
        $this->runInFilesMode();
    }

    /**
     * return the formated curd name.
     */
    protected function runInFilesMode()
    {
        // generates blueprints directory if not exists
        $this->generateBlueprintsDir();
        // check if the crud name already exists
        if ($this->alreadyExists()) {
            $this->error('crud already exists, make sure to check your blueprints folder!');

            return 0;
        }
        // get the stub
        $stub = File::get($this->getStub());
        $this->replacePlaceholders($stub);
        $this->generateBlueprintFile($stub);
    }

    /**
     * Replaces all the placeholders in blueprint stub file.
     * @param string $stub blueprint stub file
     * @return $this
     */
    protected function replacePlaceholders(&$stub)
    {
        $crudName = $this->getCrudName();
        $crudNamespace = str_plural($crudName);
        $controllerName = str_plural($crudName).'Controller';
        $isAPI = ($this->option('api')) ? 'true' : 'false';
        $tableName = str_plural(snake_case($crudName));
        $routeName = str_plural(snake_case($crudName, '-'));

        // replace crud placeholders
        $stub = str_replace('{{crud.name}}', $crudName, $stub);
        $stub = str_replace('{{crud.namespace}}', $crudNamespace, $stub);
        $stub = str_replace('{{crud.isApi}}', $isAPI, $stub);

        // replace controller placeholders
        $stub = str_replace('{{controller.name}}', $controllerName, $stub);

        // replace model placeholders
        $stub = str_replace('{{model.name}}', $crudName, $stub);

        // replace table placeholders
        $stub = str_replace('{{table.name}}', $tableName, $stub);

        // replace route placeholders
        $stub = str_replace('{{route.name}}', $routeName, $stub);
        $stub = str_replace('{{route.url}}', $routeName, $stub);

        return $this;
    }

    /**
     * Determine if the crud already exists.
     *
     * @return bool
     */
    protected function alreadyExists()
    {
        $crudName = $this->getCrudName();
        foreach (File::files($this->getBlueprintsDirectory()) as $file) {
            try {
                $fileContent = json_decode(\File::get($file));
                if (is_null($fileContent)) {
                    throw new \Exception(json_last_error());
                }
                if ($fileContent->crudName === $crudName) {
                    return true;
                }
            } catch (\Exception $e) {
                throw new \Exception('not valide json file in your blueprints folder');
            }
        }

        return false;
    }

    protected function runningWithDatabase()
    {
        return false;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../Stubs/blueprint.stub';
    }

    /**
     * Get the blueprint file destination path.
     *
     * @param  string $name
     * @return string
     */
    protected function getBlueprintPath($name = null)
    {
        return $this->getBlueprintsDirectory().date('Y_m_d_His').
            '_create_'.$name.'_crud_blueprint.json';
    }

    /**
     * Get the blueprints path.
     *
     * @param  string $name
     * @return string
     */
    protected function getBlueprintsDirectory()
    {
        return database_path().'/blueprints/';
    }

    /**
     * return the formated curd name.
     * @return string curd name
     */
    protected function getCrudName()
    {
        return  str_singular(preg_replace('/crud$/i', '', $this->argument('name')));
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function generateBlueprintsDir()
    {
        // make blueprints directory
        if (! File::isDirectory($this->getBlueprintsDirectory())) {
            File::makeDirectory($this->getBlueprintsDirectory(), 0755, true);
        }
    }

    /**
     * Genreate the blueprint json file.
     *
     * @param string $stub file contents
     * @return string
     */
    protected function generateBlueprintFile($stub)
    {
        $crudName = $this->getCrudName();
        $fileName = snake_case($crudName, '_');
        // get blueprint path
        $path = $this->getBlueprintPath($fileName);
        if (File::put($path, $stub)) {
            $this->info('blueprint file created successfully under:');
            $this->info($path);
        } else {
            $this->error('Error creating the blueprint file!');
        }
    }
}
