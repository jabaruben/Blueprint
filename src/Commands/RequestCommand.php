<?php

namespace PHPJuice\Blueprint\Commands;

use Illuminate\Console\GeneratorCommand;
use PHPJuice\Blueprint\Traits\HasJsonInput;

class RequestCommand extends GeneratorCommand
{
    use HasJsonInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blueprint:request
                            {name : The name of the resource.}
                            {--namespace= : The namespace of the resource.}
                            {--validations= : validation rules to be validated against input.}
                            {--force : Overwrite already existing controller.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../Stubs/request.stub';
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
        return $rootNamespace.'\\Http\\Requests\\'.($this->option('namespace') ? $this->option('namespace') : '');
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name);
        $name = str_replace('\\', DIRECTORY_SEPARATOR, $name);

        return app_path().DIRECTORY_SEPARATOR.$name.'Request.php';
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
                    ->replaceValidations($stub)
                    ->replaceClass($stub, $name.'Request');
    }

    /**
     * Replace the validation for the given stub.
     *
     * @param  string  $stub
     *
     * @return $this
     */
    protected function replaceValidations(&$stub)
    {
        $validations = $this->handleJsonInput('validations');
        $validationsStr = '';
        foreach ($validations as $validation) {
            $validationsStr .= sprintf("\n          '%s' => '%s',", $validation->field, $validation->rules);
        }
        $stub = str_replace('{{validations}}', $validationsStr, $stub);

        return $this;
    }
}
