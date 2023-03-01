<?php

namespace App\Services\Make;

use Exception;
use Illuminate\Filesystem\Filesystem;

class MakeServiceService
{
    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * Create a new migration creator instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * @param string $service
     * @return array
     * @throws Exception
     */
    public function create(string $service): array
    {
        $paths = $this->getPaths($service);

        foreach ($paths as $path) {
            if ($this->files->exists($path)) {
                throw new Exception("File : $path already exits");
            }

            $isInterface = str_contains($path, "{$service}ServiceInterface");

            $stub = $this->getStub($isInterface);

            $this->files->ensureDirectoryExists(dirname($path));

            $this->files->put(
                $path, $this->populateStub($stub, $this->getStubVariables($service))
            );
        }

        return $paths;
    }

    protected function getPaths(string $service): array
    {
        $path = 'App/Services';

        return [
            base_path($path) . "/$service/{$service}Service.php",
            base_path($path) . "/$service/{$service}ServiceInterface.php"
        ];
    }

    protected function getStub($isInterface = false): string
    {
        if ($isInterface) {
            $stub = $this->stubPath() . '/serviceInterface.stub';
        } else {
            $stub = $this->stubPath() . '/service.stub';
        }
        return $stub;
    }

    public function stubPath(): string
    {
        return base_path() . '/stubs/service';
    }

    protected function populateStub(string $stub, $stubVariables = []): string
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;
    }

    public function getStubVariables($service): array
    {
        $namespace = "App\\Services\\$service";

        return [
            'NAMESPACE' => $namespace,
            'SERVICE_NAME' => $service
        ];
    }
}
