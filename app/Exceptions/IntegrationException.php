<?php

namespace App\Exceptions;


class IntegrationException extends \Exception
{
    protected int $httpStatus;
    protected ?array $errors;

    public function __construct(string $message, int $httpStatus = 500, ?array $errors = null)
    {
        parent::__construct($message);
        $this->httpStatus = $httpStatus;
        $this->errors = $errors;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
