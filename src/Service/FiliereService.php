<?php
declare(strict_types=1);

namespace App\Service;

use App\Dao\FiliereDao;
use App\Dao\EtudiantDao;
use App\Dto\FiliereCreateDTO;
use App\Entity\Filiere;
use App\Exception\BusinessException;
use App\Log\Logger;
use PDO;

class FiliereService
{
    private $filiereDao;  
    private $etudiantDao;  
    private $pdo;          
    private $logger;       

    public function __construct(FiliereDao $filiereDao, EtudiantDao $etudiantDao, PDO $pdo, Logger $logger)
    {
        $this->filiereDao = $filiereDao;
        $this->etudiantDao = $etudiantDao;
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    public function createFiliere(FiliereCreateDTO $dto): int
    {
        $code = trim($dto->getCode());
        $lib  = trim($dto->getLibelle());
        if ($code === '' || $lib === '') {
            throw new BusinessException('code et libellé sont requis');
        }
        if (strlen($code) > 16) {
            throw new BusinessException('Filiere.code ne doit pas dépasser 16 caractères');
        }
        $code = strtoupper($code);
       
        $entity = new Filiere(null, $code, $lib);
        return $this->filiereDao->insert($entity);
    }

    public function deleteFiliere(int $id): bool
    {
        if ($id <= 0) { throw new BusinessException('id filière invalide'); }
       
        $count = $this->etudiantDao->countByFiliereId($id);
        if ($count > 0) {
            throw new BusinessException('Suppression filière interdite: des étudiants y sont rattachés');
        }
        return $this->filiereDao->delete($id);
    }
}
