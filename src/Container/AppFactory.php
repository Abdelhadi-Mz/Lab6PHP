<?php
declare(strict_types=1);

namespace App\Container;

use App\Controller\AppController;
use App\Database\DBConnection;
use App\Dao\FiliereDao;
use App\Dao\EtudiantDao;
use App\Log\Logger;
use App\Service\FiliereService;
use App\Service\EtudiantService;

class AppFactory
{
    public static function createController(): AppController
    {
        $logger = new Logger(__DIR__ . '/../../logs/pdo_errors.log');
        \App\Database\DBConnection::init($logger);
        $pdo = DBConnection::get();

        $filiereDao = new FiliereDao($logger);
        $etudiantDao = new EtudiantDao($logger);

        $filiereService = new FiliereService($filiereDao, $etudiantDao, $pdo, $logger);
        $etudiantService = new EtudiantService($etudiantDao, $filiereDao, $pdo, $logger);

        return new AppController($filiereService, $etudiantService);
    }
}