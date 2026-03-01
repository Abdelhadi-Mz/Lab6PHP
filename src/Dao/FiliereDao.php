<?php
declare(strict_types=1);

namespace App\Dao;

use PDO; use PDOException;
use App\Entity\Filiere;
use App\Database\DBConnection;
use App\Log\Logger;

class FiliereDao
{
    /** @var Logger */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function insert(Filiere $f): int
    {
        $sql = 'INSERT INTO filiere(code, libelle) VALUES(:code, :libelle)';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->prepare($sql);
            // bindValue: fige la valeur actuelle (idéal pour scalaires connus)
            $stmt->bindValue(':code', $f->getCode(), PDO::PARAM_STR);
            $stmt->bindValue(':libelle', $f->getLibelle(), PDO::PARAM_STR);
            $stmt->execute();
            $id = (int)$pdo->lastInsertId();
            $f->setId($id);
            return $id;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'sql' => $sql]);
            throw $e;
        }
    }

    public function update(Filiere $f): bool
    {
        $sql = 'UPDATE filiere SET code = :code, libelle = :libelle WHERE id = :id';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->prepare($sql);
            // bindParam: lie par référence (utile si la variable peut changer avant execute)
            $id = $f->getId(); $code = $f->getCode(); $lib = $f->getLibelle();
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->bindParam(':libelle', $lib, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'sql' => $sql, 'id' => $f->getId()]);
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM filiere WHERE id = :id';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'sql' => $sql, 'id' => $id]);
            throw $e;
        }
    }

    public function findById(int $id): ?Filiere
    {
        $sql = 'SELECT id, code, libelle FROM filiere WHERE id = :id';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            if (!$row) { return null; }
            return new Filiere((int)$row['id'], (string)$row['code'], (string)$row['libelle']);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'sql' => $sql, 'id' => $id]);
            throw $e;
        }
    }

    public function findAll(): array
    {
        $sql = 'SELECT id, code, libelle FROM filiere ORDER BY id ASC';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            $out = [];
            foreach ($rows as $r) {
                $out[] = new Filiere((int)$r['id'], (string)$r['code'], (string)$r['libelle']);
            }
            return $out;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage(), ['method' => __METHOD__, 'sql' => $sql]);
            throw $e;
        }
    }
}