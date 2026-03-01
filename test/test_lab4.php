<?php
declare(strict_types=1);

// Autoload minimal (si pas déjà en bootstrap)
spl_autoload_register(function(string $class){
    $prefix = 'App\\'; $base = __DIR__ . '/../src/'; $len = strlen($prefix);
    if (strncmp($class, $prefix, $len) !== 0) return;
    $rel = substr($class, $len);
    $file = $base . str_replace('\\', DIRECTORY_SEPARATOR, $rel) . '.php';
    if (is_file($file)) require $file;
});

require __DIR__ . '/../bootstrap.php'; // si disponible (facultatif), utile pour checkpoints

use App\Container\AppFactory;
use App\Controller\Response;

function printResponse(string $label, Response $res): void {
    echo "[RESPONSE] $label => success=" . ($res->isSuccess() ? 'true' : 'false');
    if ($res->isSuccess()) {
        echo ' data=' . json_encode($res->getData(), JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        echo ' error=' . $res->getError() . PHP_EOL;
    }
}

$ctrl = AppFactory::createController();

// 1) OK: création filière + création étudiant (transaction combinée)
$r1 = $ctrl->handle([
    'action' => 'create_filiere_then_student',
    'code' => 'info', 'libelle' => 'Informatique',
    'cne' => 'CNE9001', 'nom' => 'El Amrani', 'prenom' => 'Yassine', 'email' => 'yassine.elamrani@example.com'
]);
printResponse('Create Filiere + Etudiant', $r1);

// 2) Erreur: email interdit (mailinator) — garder mailinator pour tester l’erreur
$r2 = $ctrl->handle([
    'action' => 'create_etudiant',
    'cne' => 'CNE9002', 'nom' => 'Bennani', 'prenom' => 'Salma', 'email' => 'salma.bennani@mailinator.com',
    'filiere_id' => 1
]);
printResponse('Email interdit', $r2);

// 3) Erreur: CNE invalide — garder un CNE qui ne respecte pas le format attendu
$r3 = $ctrl->handle([
    'action' => 'create_etudiant',
    'cne' => 'ZZZ0000', 'nom' => 'Ouardi', 'prenom' => 'Hajar', 'email' => 'hajar.ouardi@example.com',
    'filiere_id' => 1
]);
printResponse('CNE invalide', $r3);

// 4) Erreur: suppression filière avec étudiants
$r4 = $ctrl->handle(['action' => 'delete_filiere', 'id' => 1]);
printResponse('Suppression filière interdite', $r4);