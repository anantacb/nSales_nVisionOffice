<?php

namespace App\Services\Make;

use Exception;
use Illuminate\Filesystem\Filesystem;

class MakeRepositoryService
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
     * @param string $model
     * @param string $type
     * @return array
     * @throws Exception
     */
    public function create(string $model, string $type = 'office'): array
    {
        $paths = $this->getPaths($model, $type);

        foreach ($paths as $path) {
            if ($this->files->exists($path)) {
                throw new Exception("File : {$path} already exits");
            }

            $isInterface = str_contains($path, "{$model}RepositoryInterface");

            $stub = $this->getStub($type, $isInterface);

            $this->files->ensureDirectoryExists(dirname($path));

            $this->files->put(
                $path, $this->populateStub($stub, $this->getStubVariables($model, $type))
            );
        }

        return $paths;
    }

    protected function getPaths(string $model, $type): array
    {
        if ($type == 'office') {
            $path = 'App/Repositories/Eloquent/Office';
        } else {
            $path = 'App/Repositories/Eloquent/Company';
        }

        return [
            base_path($path) . "/$model/{$model}Repository.php",
            base_path($path) . "/$model/{$model}RepositoryInterface.php",
        ];
    }

    protected function getStub($type, $isInterface = false): string
    {
        if ($type == 'office') {
            if ($isInterface) {
                $stub = $this->stubPath() . '/repositoryInterface.office.stub';
            } else {
                $stub = $this->stubPath() . '/repository.office.stub';
            }
        } else {
            if ($isInterface) {
                $stub = $this->stubPath() . '/repositoryInterface.company.stub';
            } else {
                $stub = $this->stubPath() . '/repository.company.stub';
            }
        }
        return $stub;
    }

    public function stubPath(): string
    {
        return base_path() . '/stubs/repository';
    }

    protected function populateStub(string $stub, $stubVariables = []): string
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        return $contents;
    }

    public function getStubVariables($model, $type): array
    {
        if ($type == 'office') {
            $namespace = "App\\Repositories\\Eloquent\\Office\\$model";
        } else {
            $namespace = "App\\Repositories\\Eloquent\\Company\\$model";
        }

        return [
            'NAMESPACE' => $namespace,
            'MODEL' => $model
        ];
    }
}
