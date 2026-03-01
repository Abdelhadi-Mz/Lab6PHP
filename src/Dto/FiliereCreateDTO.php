<?php
declare(strict_types=1);

namespace App\Dto;

class FiliereCreateDTO
{
    private $code;    // string
    private $libelle; // string

    public function __construct(string $code, string $libelle)
    {
        $this->code = $code;
        $this->libelle = $libelle;
    }
    public function getCode(): string { return $this->code; }
    public function getLibelle(): string { return $this->libelle; }
}