<?php

namespace PHPJuice\Blueprint\Commands;

use Illuminate\Console\GeneratorCommand;

class Generator extends GeneratorCommand
{
    /**
     * The blueprint of class being generated.
     *
     * @var string
     */
    protected $blueprint;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $this->option('blueprint')) {
            $this->error('must provide a blueprint for this genrator to work!');

            return 0;
        }
        $this->blueprint = $this->handleJson($this->option('blueprint'));
        parent::handle();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $name = strtolower($this->type).'.stub';

        return __DIR__.'/../Stubs/'.$name;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        switch ($this->type) {
            case 'Model':
                $rootNamespace .= '\\Models\\';
                break;
            case 'Request':
                $rootNamespace .= '\\Http\\Requests\\';
                break;
            case 'Resource':
                $rootNamespace .= '\\Http\\Resources\\';
                break;
            case 'ApiController':
                $rootNamespace .= '\\Http\\Controllers\\API\\';
                break;
            case 'Controller':
                $rootNamespace .= '\\Http\\Controllers\\';
                break;
            default:
                break;
        }

        return $rootNamespace.$this->getCrudNamespace();
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        if ($this->option('force')) {
            return false;
        }

        return parent::alreadyExists($rawName);
    }

    /**
     * Handles json decoding.
     *
     * @param  string  $json
     * @return string
     *
     * @throws \Exception
     */
    protected function handleJson($jsonStr)
    {
        try {
            // get and decode json file
            $content = json_decode($jsonStr);
            if (is_null($content)) {
                throw new \Exception(json_last_error());
            }

            return $content;
        } catch (\Exception $e) {
            $this->error('provided json is not valide! check for errors');
        }
    }

    /**
     * Gets the crud namespace.
     *
     * @return string
     */
    protected function getCrudNamespace()
    {
        return $this->blueprint->crud->namespace;
    }

    /**
     * Gets the crud name.
     *
     * @return string
     */
    protected function getCrudName()
    {
        return $this->blueprint->crud->name;
    }

    /**
     * Gets the model name.
     *
     * @return string
     */
    protected function getModelName()
    {
        return $this->blueprint->model->name;
    }

    /**
     * Gets the controller name.
     *
     * @return string
     */
    protected function getControllerName()
    {
        return $this->blueprint->controller->name;
    }

    /**
     * Gets the table name.
     *
     * @return string
     */
    protected function getTableName()
    {
        return $this->blueprint->table->name;
    }

    /**
     * Gets the route name.
     *
     * @return string
     */
    protected function getRouteName()
    {
        return $this->blueprint->route->name;
    }

    /**
     * Is a restfull api crud.
     *
     * @return bool
     */
    protected function isApi()
    {
        return $this->blueprint->crud->isApi;
    }
}
