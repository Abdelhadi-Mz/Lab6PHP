<?php
declare(strict_types=1);

namespace App\Service;

use App\Dao\FiliereDao;
use App\Dao\EtudiantDao;
use App\Dto\EtudiantCreateDTO;
use App\Dto\EtudiantUpdateDTO;
use App\Entity\Etudiant;
use App\Exception\BusinessException;
use App\Log\Logger;
use PDO;

class EtudiantService
{
    private $etudiantDao; // EtudiantDao
    private $filiereDao;  // FiliereDao
    private $pdo;         // PDO
    private $logger;      // Logger

    /** @var string[] */
    private $forbiddenEmailDomains = ['mailinator.com', 'temp-mail.org'];

    public function __construct(EtudiantDao $etudiantDao, FiliereDao $filiereDao, PDO $pdo, Logger $logger)
    {
        $this->etudiantDao = $etudiantDao;
        $this->filiereDao = $filiereDao;
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    public function create(EtudiantCreateDTO $dto): int
    {
        $this->validateEmail($dto->getEmail());
        $this->validateCne($dto->getCne());
        if ($this->filiereDao->findById($dto->getFiliereId()) === null) {
            throw new BusinessException('filiere_id inexistant');
        }
        $entity = new Etudiant(
            null,
            $dto->getCne(),
            $dto->getNom(),
            $dto->getPrenom(),
            $dto->getEmail(),
            $dto->getFiliereId()
        );
        return $this->etudiantDao->insert($entity);
    }

    public function update(EtudiantUpdateDTO $dto): bool
    {
        if ($dto->getId() <= 0) { throw new BusinessException('id étudiant invalide'); }
        $this->validateEmail($dto->getEmail());
        $this->validateCne($dto->getNom() === '' ? 'CNE0000' : 'CNE0000'); // non utilisé ici; cne n'est pas modifié
        if ($this->filiereDao->findById($dto->getFiliereId()) === null) {
            throw new BusinessException('filiere_id inexistant');
        }
        $found = $this->etudiantDao->findById($dto->getId());
        if ($found === null) { throw new BusinessException('Etudiant introuvable'); }
        // Recréer pour simplicité (ou mettre à jour les champs sur l’entité existante)
        $entity = new Etudiant(
            $dto->getId(),
            $found->getCne(), // cne inchangé
            $dto->getNom(),
            $dto->getPrenom(),
            $dto->getEmail(),
            $dto->getFiliereId()
        );
        return $this->etudiantDao->update($entity);
    }

    /** Cas d’usage transactionnel: créer une filière puis un étudiant rattaché. */
    public function createFiliereThenStudentTransaction(
        \App\Dto\FiliereCreateDTO $fDto,
        \App\Dto\EtudiantCreateDTO $eDto
    ): array {
        $this->pdo->beginTransaction();
        try {
            $fid = (new \App\Service\FiliereService($this->filiereDao, $this->etudiantDao, $this->pdo, $this->logger))
                ->createFiliere($fDto);
            // forcer etudiant à pointer la filière créée
            $eDtoFixed = new \App\Dto\EtudiantCreateDTO(
                $eDto->getCne(),
                $eDto->getNom(),
                $eDto->getPrenom(),
                $eDto->getEmail(),
                $fid
            );
            $eid = $this->create($eDtoFixed);
            $this->pdo->commit();
            return ['filiere_id' => $fid, 'etudiant_id' => $eid];
        } catch (\Throwable $ex) {
            if ($this->pdo->inTransaction()) { $this->pdo->rollBack(); }
            if ($ex instanceof \PDOException) {
                $this->logger->error('Transaction failed: ' . $ex->getMessage(), ['method' => __METHOD__]);
            }
            // Propager en BusinessException si besoin
            if (!($ex instanceof BusinessException)) {
                throw new BusinessException('Echec transactionnel: ' . $ex->getMessage());
            }
            throw $ex;
        }
    }

    private function validateEmail(string $email): void
    {
        $email = trim($email);
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new BusinessException('email invalide');
        }
        $domain = strtolower(substr($email, strrpos($email, '@') + 1));
        foreach ($this->forbiddenEmailDomains as $bad) {
            if ($domain === $bad) {
                throw new BusinessException('domaine email interdit: ' . $bad);
            }
        }
    }

    private function validateCne(string $cne): void
    {
        if (!preg_match('/^CNE\d{4}$/', $cne)) {
            throw new BusinessException('CNE invalide: format attendu CNE####');
        }
    }
}