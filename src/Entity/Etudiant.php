<?php
declare(strict_types=1);

namespace App\Entity;

class Etudiant
{
    private $id;        // int|null (auto)
    private $cne;       // string unique
    private $nom;       // string
    private $prenom;    // string
    private $email;     // string unique
    private $filiereId; // int FK -> filiere.id

    public function __construct(?int $id, string $cne, string $nom, string $prenom, string $email, int $filiereId)
    {
        $this->id = $id;
        $this->setCne($cne);
        $this->setNom($nom);
        $this->setPrenom($prenom);
        $this->setEmail($email);
        $this->setFiliereId($filiereId);
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getCne(): string { return $this->cne; }
    public function setCne(string $cne): void
    {
        $cne = trim($cne);
        if ($cne === '') { throw new \InvalidArgumentException('cne requis'); }
        $this->cne = $cne;
    }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void
    {
        $nom = trim($nom);
        if ($nom === '') { throw new \InvalidArgumentException('nom requis'); }
        $this->nom = $nom;
    }

    public function getPrenom(): string { return $this->prenom; }
    public function setPrenom(string $prenom): void
    {
        $prenom = trim($prenom);
        if ($prenom === '') { throw new \InvalidArgumentException('prenom requis'); }
        $this->prenom = $prenom;
    }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void
    {
        $email = trim($email);
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('email invalide');
        }
        $this->email = $email;
    }

    public function getFiliereId(): int { return $this->filiereId; }
    public function setFiliereId(int $filiereId): void
    {
        if ($filiereId <= 0) { throw new \InvalidArgumentException('filiere_id > 0 requis'); }
        $this->filiereId = $filiereId;
    }
}