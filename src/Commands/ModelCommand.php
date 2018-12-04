<?php

namespace PHPJuice\Blueprint\Commands;

use Illuminate\Console\GeneratorCommand;

class ModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blueprint:model
                            {name : The name of the model.}
                            {--table= : The name of the table.}
                            {--namespace= : namespace.}
                            {--pk=id : name of the primary key.}
                            {--soft-deletes=no : Include soft deletes fields.}
                            {--force : Overwrite already existing model.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../Stubs/model.stub';
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
        return $rootNamespace . '\\Models\\' . ($this->option('namespace') ? $this->option('namespace') : '');
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
        $table = $this->option('table') ?: $this->argument('name');
        $primaryKey = $this->option('pk');
        $softDeletes = $this->option('soft-deletes');
        $ret = $this->replaceNamespace($stub, $name)
            ->replaceTable($stub, $table)
            ->replacePrimaryKey($stub, $primaryKey)
            ->replaceSoftDelete($stub, $softDeletes);
        return $ret->replaceClass($stub, $name);
    }

    /**
     * Replace the table for the given stub.
     *
     * @param  string  $stub
     * @param  string  $table
     *
     * @return $this
     */
    protected function replaceTable(&$stub, $table)
    {
        $stub = str_replace('{{tableName}}', $table, $stub);
        return $this;
    }

    /**
     * Replace the fillable for the given stub.
     *
     * @param  string  $stub
     * @param  string  $fillable
     *
     * @return $this
     */
    protected function replaceFillable(&$stub, $fillable)
    {
        $stub = str_replace('{{fillable}}', $fillable, $stub);
        return $this;
    }

    /**
     * Replace the hidden for the given stub.
     *
     * @param  string  $stub
     * @param  string  $hidden
     *
     * @return $this
     */
    protected function replaceHidden(&$stub, $hidden)
    {
        $stub = str_replace('{{hidden}}', $hidden, $stub);
        return $this;
    }

    /**
     * Replace the primary key for the given stub.
     *
     * @param  string  $stub
     * @param  string  $primaryKey
     *
     * @return $this
     */
    protected function replacePrimaryKey(&$stub, $primaryKey)
    {
        $stub = str_replace('{{primaryKey}}', $primaryKey, $stub);
        return $this;
    }

    /**
     * Replace the (optional) soft deletes part for the given stub.
     *
     * @param  string  $stub
     * @param  string  $replaceSoftDelete
     *
     * @return $this
     */
    protected function replaceSoftDelete(&$stub, $replaceSoftDelete)
    {
        if ($replaceSoftDelete == 'yes') {
            $stub = str_replace('{{softDeletes}}', "use SoftDeletes;\n    ", $stub);
            $stub = str_replace('{{useSoftDeletes}}', "use Illuminate\Database\Eloquent\SoftDeletes;\n", $stub);
        } else {
            $stub = str_replace('{{softDeletes}}', '', $stub);
            $stub = str_replace('{{useSoftDeletes}}', '', $stub);
        }
        return $this;
    }
}
