<?php

namespace PHPJuice\Blueprint\Commands;

use Illuminate\Console\GeneratorCommand;

class BlueprintMigrationFkCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blueprint:migration:fk
                        {name : The name of migration table.}
                        {--keys= : foreign keys.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'ForeignMigration';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $indent = '            ';

    /**
     * The schema of the class being generated.
     *
     * @var string
     */
    protected $keys;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../Stubs/foreign_migration.stub';
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
        $datePrefix = date('Y_m_d_His', strtotime('+ 5 seconds'));

        return database_path('/migrations/').$datePrefix.'_add_fk_to_'.$name.'_table.php';
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
        // get stub file
        $stub = $this->files->get($this->getStub());
        $tableName = $this->argument('name');
        // genreate table name
        $className = $this->generateClassName($name);
        // get schema
        $this->keys = $this->option('keys');
        // replace the tableName in the stub
        $this->replaceTableName($stub, $tableName);
        // replace the key names in the stub
        $this->replaceForeignKeyNames($stub);
        // replace the actual foreignKeys in the stub
        $this->replaceForeignKeys($stub);

        return $this->replaceClass($stub, $className);
    }

    /**
     * Replace the tableName for the given stub.
     *
     * @param  string  $stub
     * @param  string  $tableName
     *
     * @return $this
     */
    protected function replaceTableName(&$stub, $tableName)
    {
        $stub = str_replace('{{tableName}}', $tableName, $stub);

        return $this;
    }

    /**
     * Replace the primary key for the given stub.
     *
     * @param  string  $stub
     * @param  array  $foreignKeyNames
     *
     * @return $this
     */
    protected function replaceForeignKeyNames(&$stub)
    {
        $fkStr = '';
        foreach ($this->getForeignKeyNames() as $name) {
            $fkStr .= sprintf("\n\t\t\t'%s',", $name);
        }
        $fkStr = rtrim($fkStr, ',')."\n\t\t";
        $stub = str_replace('{{foreignKeyNames}}', $fkStr, $stub);

        return $this;
    }

    /**
     * Generate a classname based on tableName.
     *
     * @param  string  $tableName
     *
     * @return string
     */
    protected function generateClassName($tableName)
    {
        return 'AddFkTo'.str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))).'Table';
    }

    /**
     * Gets Foreign key names from schema.
     *
     * @return string
     */
    protected function getForeignKeyNames()
    {
        $keys = [];
        foreach ($this->keys as $key) {
            array_push($keys, $key['column']);
        }

        return $keys;
    }

    protected function replaceForeignKeys(&$stub)
    {
        $format = "\$table->unsignedInteger('%1\$s');
        \$table->foreign('%1\$s')
            ->references('%2\$s')
            ->on('%3\$s')
            ->onUpdate('%4\$s')
            ->onDelete('%5\$s');";
        $str = '';
        foreach ($this->keys as $key) {
            $str .= sprintf($format,
                $key['column'],
                $key['references'],
                $key['on'],
                $key['onUpdate'],
                $key['onDelete']
            );
            $str .= "\n\t\t";
        }
        $stub = str_replace('{{foreignKeys}}', $str, $stub);

        return $this;
    }
}
