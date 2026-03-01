<?php
declare(strict_types=1);

namespace App\Entity;

class Filiere
{
    private $id;      // int|null (auto)
    private $code;    // string unique
    private $libelle; // string

    public function __construct(?int $id, string $code, string $libelle)
    {
        $this->id = $id;
        $this->setCode($code);
        $this->setLibelle($libelle);
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): void
    {
        $code = trim($code);
        if ($code === '') { throw new \InvalidArgumentException('code requis'); }
        $this->code = $code;
    }

    public function getLibelle(): string { return $this->libelle; }
    public function setLibelle(string $libelle): void
    {
        $libelle = trim($libelle);
        if ($libelle === '') { throw new \InvalidArgumentException('libellé requis'); }
        $this->libelle = $libelle;
    }
}