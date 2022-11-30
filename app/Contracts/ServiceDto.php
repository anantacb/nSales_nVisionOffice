<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ServiceDto
{
    public string $message;
    public int $statusCode;
    public EloquentCollection|array|null|Collection|Model|LengthAwarePaginator $data;

    /**
     * ServiceResponse constructor.
     * @param $message string
     * @param $statusCode integer
     * @param $data EloquentCollection|array|Collection|null|Model|LengthAwarePaginator
     */
    public function __construct(string $message, int $statusCode, EloquentCollection|Collection|array|null|Model|LengthAwarePaginator $data = null)
    {
        $this->message = $message;
        $this->statusCode = $statusCode;
        $this->data = $data;
    }
}
