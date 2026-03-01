<?php
declare(strict_types=1);

namespace App\Controller;

class Response
{
    private $success; // bool
    private $data;    // mixed|null
    private $error;   // string|null

    public function __construct(bool $success, $data = null, ?string $error = null)
    {
        $this->success = $success; $this->data = $data; $this->error = $error;
    }
    public function isSuccess(): bool { return $this->success; }
    public function getData() { return $this->data; }
    public function getError(): ?string { return $this->error; }
}