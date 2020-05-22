<?php

namespace Galafeno\Lingo\Console\Commands;

use Illuminate\Console\Command;

class GenerateLingo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:lingo {name} {--base_url=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Lingo class';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stub = $this->getCompiledStub();
        $path = base_path('app') . "/Lingos/";

        if (!is_dir($path)) {
            mkdir($path);
        }

        file_put_contents("{$path}{$this->argument('name')}Lingo.php", $stub);
        $this->info("Lingo Class {$this->argument('name')} was created!");
    }

    public function getCompiledStub()
    {
        $name = $this->argument('name');
        $base_url = $this->option('base_url') ?? 'http://service.rest/api';

        $stub = file_get_contents( __DIR__ . '/../Stubs/lingo.stub');
        $stub = str_replace('{{NAME}}', $name, $stub);
        $stub = str_replace('{{BASE_URL}}', $base_url, $stub);

        return $stub;
    }
}
