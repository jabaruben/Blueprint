<?php

namespace PHPJuice\Blueprint\Commands;

use Illuminate\Console\GeneratorCommand;
use PHPJuice\Blueprint\Traits\HasJsonInput;

class APIControllerCommand extends GeneratorCommand
{
    use HasJsonInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blueprint:controller:api
                            {name : The name of the controller.}
                            {--blueprint= : blueprint from a json file.}
                            {--force : Overwrite already existing controller.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new api controller.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * The blueprint of class being generated.
     *
     * @var string
     */
    protected $blueprint;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../Stubs/controller-api.stub';
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
        $this->blueprint = $this->handleJsonInput('blueprint');

        return $rootNamespace.'\\Http\\Controllers\\API\\'.$this->getControllerNamespace();
    }

    protected function getControllerNamespace()
    {
        return isset($this->blueprint->controller->namespace) ? $this->blueprint->controller->namespace : $this->blueprint->crud->namespace;
    }

    protected function getModelNamespace()
    {
        return isset($this->blueprint->model->namespace) ? $this->blueprint->model->namespace : $this->blueprint->crud->namespace;
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
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceModelName($stub)
            ->replaceModelNameSingular($stub)
            ->replaceModelNamespace($stub)
            ->replaceModelNamespaceSegments($stub)
            ->replacePaginationNumber($stub)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the modelName for the given stub.
     *
     * @param  string  $stub
     *
     * @return $this
     */
    protected function replaceModelName(&$stub)
    {
        $stub = str_replace('{{modelName}}', $this->blueprint->model->name, $stub);

        return $this;
    }

    /**
     * Replace the modelNameSingular for the given stub.
     *
     * @param  string  $stub
     *
     * @return $this
     */
    protected function replaceModelNameSingular(&$stub)
    {
        $modelNameSingular = str_singular(strtolower($this->blueprint->model->name));
        $stub = str_replace('{{modelNameSingular}}', $modelNameSingular, $stub);

        return $this;
    }

    /**
     * Replace the modelNamespace for the given stub.
     *
     * @param  string  $stub
     *
     * @return $this
     */
    protected function replaceModelNamespace(&$stub)
    {
        $stub = str_replace('{{modelNamespace}}', $this->getModelNamespace(), $stub);

        return $this;
    }

    /**
     * Replace the modelNamespace segments for the given stub.
     *
     * @param $stub
     *
     * @return $this
     */
    protected function replaceModelNamespaceSegments(&$stub)
    {
        $modelNamespace = $this->getModelNamespace();
        $modelSegments = explode('\\', $modelNamespace);
        foreach ($modelSegments as $key => $segment) {
            $stub = str_replace('{{modelNamespace['.$key.']}}', $segment, $stub);
        }
        $stub = preg_replace('{{modelNamespace\[\d*\]}}', '', $stub);

        return $this;
    }

    /**
     * Replace the pagination placeholder for the given stub.
     *
     * @param $stub
     * @param $perPage
     *
     * @return $this
     */
    protected function replacePaginationNumber(&$stub)
    {
        $perPage = intval($this->blueprint->controller->pagination);
        $stub = str_replace('{{pagination}}', $perPage, $stub);

        return $this;
    }
}
