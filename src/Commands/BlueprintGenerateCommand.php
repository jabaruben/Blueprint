<?php

namespace PHPJuice\Blueprint\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BlueprintGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blueprint:generate
                         {--blueprint= : Crud blueprint from a json file.}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a full crud including controller, model, views & migrations.';

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
        if (! $this->option('blueprint')) {
            $this->error('must provide a blueprint file to generate a crud');
            $this->info('run php artisan blueprint:create {name} to generate a file');

            return 0;
        }
        try {
            // get and decode json file
            $this->blueprint = json_decode(File::get($this->option('blueprint')));
        } catch (\Exception $e) {
            $this->error('provided json file is not valide! check for errors');
        }

        // genreate all the scaffolding
        $this->createMigration()
             ->createModel()
             ->createController()
             ->createRequest()
             ->createResource()
             ->createRoute();

        // For optimizing the class loader
        if (\App::VERSION() < '5.6') {
            $this->callSilent('optimize');
        }
    }

    /**
     * creates a resource.
     * @return $this
     */
    protected function createResource()
    {
        $args = [
            'name' => $this->blueprint->modelName,
            '--namespace' =>  $this->blueprint->namespace,
            '--force' => true,
        ];
        $this->call('blueprint:resource', $args);

        return $this;
    }

    /**
     * creates a request.
     * @return $this
     */
    protected function createRequest()
    {
        $args = [
            'name' => $this->blueprint->modelName,
            '--namespace' =>  $this->blueprint->namespace,
            '--force' => true,
        ];
        $this->call('blueprint:request', $args);

        return $this;
    }

    /**
     * creates a migration file.
     * @return $this
     */
    protected function createMigration()
    {
        $this->call('blueprint:migration', [
            'name' => $this->blueprint->tableName,
            '--schema' =>  json_encode($this->blueprint->schema),
        ]);

        return $this;
    }

    /**
     * creates a controller.
     * @return $this
     */
    protected function createController()
    {
        $args = [
            'name' =>$this->blueprint->controllerName,
            '--model-name' => $this->blueprint->modelName,
            '--namespace' => $this->blueprint->namespace,
            '--pagination' => 10,
            '--force' => true,
        ];
        if ((bool) $this->blueprint->isAPI) {
            // creates eather an api or default controller
            $this->call('blueprint:controller:api', $args);

            return $this;
        }
        //$this->call('blueprint:controller', $args);
        return $this;
    }

    /**
     * creates a Model.
     * @return $this
     */
    protected function createModel()
    {
        $args = [
            'name' => $this->blueprint->model->name,
            '--table' => $this->blueprint->tableName,
            '--namespace' => $this->blueprint->namespace,
            '--force' => true,
        ];
        $this->call('blueprint:model', $args);

        return $this;
    }

    /**
     * creates a route.
     * @return $this
     */
    protected function createRoute()
    {
        // Updating the Http/routes.php file
        $routeFile = app_path('Http/routes.php');

        if (\App::VERSION() >= '5.3') {
            $routeFile = base_path('routes/web.php');
        }

        if (file_exists($routeFile) && isset($this->blueprint->routeName)) {
            $isAdded = File::append($routeFile, "\n".implode("\n", $this->addAPIRoute()));

            if ($isAdded) {
                $this->info('Crud/Resource route added to '.$routeFile);
            } else {
                $this->info('Unable to add the route to '.$routeFile);
            }
        } else {
            $this->info('no route option is provided');
        }

        return $this;
    }

    /**
     * Add routes.
     *
     * @return  array
     */
    protected function addAPIRoute()
    {
        $controller = 'API\\'.$this->blueprint->namespace.'\\'.$this->blueprint->controllerName;

        return ["Route::resource('".$this->blueprint->routeName."', '".$controller."');"];
    }
}
