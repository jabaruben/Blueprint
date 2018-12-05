<?php

namespace PHPJuice\Blueprint\Traits;

/**
 * has a json input option
 */
trait HasJsonInput
{
    protected function handleJsonInput($input){
        // handle json input
        if (! $this->option($input)) {
            $this->error("must provide a {$input} option generate file");
            return 0;
        }
        try {
            // get and decode json file
            $content = json_decode($this->option($input));
            if (is_null($content)) { throw new \Exception( json_last_error() ); }
            return $content;
        } catch (\Exception $e) {
            $this->error('provided json is not valide! check for errors');
        }
    }
}

