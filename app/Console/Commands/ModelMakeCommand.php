<?php

namespace Firstwap\SmsApiDashboard\Console\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand as Command;

class ModelMakeCommand extends Command
{
	
    /**
     * Get the default namespace for the class.
     *
     * @codeCoverageIgnore
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return "{$rootNamespace}\Entities";
    }
}