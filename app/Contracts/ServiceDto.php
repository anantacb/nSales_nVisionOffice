<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ServiceDto
{
    public string $message;
    public int $statusCode;
    public array|null|Collection|Model $data;

    /**
     * ServiceResponse constructor.
     * @param $message string
     * @param $statusCode integer
     * @param $data array|Collection|null|Model
     */
    public function __construct(string $message, int $statusCode, Collection|array|null|Model $data = null)
    {
        $this->message = $message;
        $this->statusCode = $statusCode;
        $this->data = $data;
    }
}
