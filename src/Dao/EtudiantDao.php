<?php
declare(strict_types=1);

namespace App\Dao;

use PDO; use PDOException;
use App\Entity\Etudiant;
use App\Database\DBConnection;
use App\Log\Logger;

class EtudiantDao
{
    /** @var Logger */
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function insert(Etudiant $e): int
    {
        $sql = 'INSERT INTO etudiant(cne, nom, prenom, email, filiere_id) VALUES(:cne, :nom, :prenom, :email, :filiere_id)';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':cne', $e->getCne(), PDO::PARAM_STR);
            $stmt->bindValue(':nom', $e->getNom(), PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $e->getPrenom(), PDO::PARAM_STR);
            $stmt->bindValue(':email', $e->getEmail(), PDO::PARAM_STR);
            $stmt->bindValue(':filiere_id', $e->getFiliereId(), PDO::PARAM_INT);
            $stmt->execute();
            $id = (int)DBConnection::get()->lastInsertId();
            $e->setId($id);
            return $id;
        } catch (PDOException $ex) {
            $this->logger->error($ex->getMessage(), [
                'method' => __METHOD__, 'sql' => $sql, 'cne' => $e->getCne(), 'email' => $e->getEmail()
            ]);
            throw $ex;
        }
    }

    public function update(Etudiant $e): bool
    {
        $sql = 'UPDATE etudiant SET cne=:cne, nom=:nom, prenom=:prenom, email=:email, filiere_id=:filiere_id WHERE id=:id';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->prepare($sql);
            $id = $e->getId(); $cne = $e->getCne(); $nom=$e->getNom(); $prenom=$e->getPrenom(); $email=$e->getEmail(); $fid=$e->getFiliereId();
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':cne', $cne, PDO::PARAM_STR);
            $stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':filiere_id', $fid, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $ex) {
            $this->logger->error($ex->getMessage(), ['method' => __METHOD__, 'sql' => $sql, 'id' => $e->getId()]);
            throw $ex;
        }
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM etudiant WHERE id = :id';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $ex) {
            $this->logger->error($ex->getMessage(), ['method' => __METHOD__, 'sql' => $sql, 'id' => $id]);
            throw $ex;
        }
    }

    public function findById(int $id): ?Etudiant
    {
        $sql = 'SELECT id, cne, nom, prenom, email, filiere_id FROM etudiant WHERE id = :id';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            if (!$row) { return null; }
            return new Etudiant((int)$row['id'], (string)$row['cne'], (string)$row['nom'], (string)$row['prenom'], (string)$row['email'], (int)$row['filiere_id']);
        } catch (PDOException $ex) {
            $this->logger->error($ex->getMessage(), ['method' => __METHOD__, 'sql' => $sql, 'id' => $id]);
            throw $ex;
        }
    }

    public function findAll(): array
    {
        $sql = 'SELECT id, cne, nom, prenom, email, filiere_id FROM etudiant ORDER BY id ASC';
        try {
            $pdo = DBConnection::get();
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            $out = [];
            foreach ($rows as $r) {
                $out[] = new Etudiant((int)$r['id'], (string)$r['cne'], (string)$r['nom'], (string)$r['prenom'], (string)$r['email'], (int)$r['filiere_id']);
            }
            return $out;
        } catch (PDOException $ex) {
            $this->logger->error($ex->getMessage(), ['method' => __METHOD__, 'sql' => $sql]);
            throw $ex;
        }
    }
    public function countByFiliereId(int $filiereId): int
    {
        $sql = 'SELECT COUNT(*) AS c FROM etudiant WHERE filiere_id = :fid';
        try {
            $pdo = \App\Database\DBConnection::get();
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':fid', $filiereId, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ? (int)$row['c'] : 0;
        } catch (\PDOException $ex) {
            $this->logger->error($ex->getMessage(), ['method' => __METHOD__, 'sql' => $sql, 'fid' => $filiereId]);
            throw $ex;
        }
    }
}
