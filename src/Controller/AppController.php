<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\FiliereCreateDTO;
use App\Dto\EtudiantCreateDTO;
use App\Dto\EtudiantUpdateDTO;
use App\Exception\BusinessException;
use App\Service\FiliereService;
use App\Service\EtudiantService;

class AppController
{
    private $filiereService;  // filiere
    private $etudiantService; // EtudiantService

    public function __construct(FiliereService $filiereService, EtudiantService $etudiantService)
    {
        $this->filiereService = $filiereService;
        $this->etudiantService = $etudiantService;
    }

    /** @param array $request ex: ['teeeeeeeest*/
    public function handle(array $request): Response
    {
        try {
            switch ($request['action'] ?? '') {
                case 'create_filiere':
                    $dto = new FiliereCreateDTO((string)$request['code'], (string)$request['libelle']);
                    $id = $this->filiereService->createFiliere($dto);
                    return new Response(true, ['id' => $id]);
                case 'delete_filiere':
                    $id = (int)$request['id'];
                    $ok = $this->filiereService->deleteFiliere($id);
                    return new Response(true, ['deleted' => $ok]);
                case 'create_etudiant':
                    $dto = new EtudiantCreateDTO(
                        (string)$request['cne'],
                        (string)$request['nom'],
                        (string)$request['prenom'],
                        (string)$request['email'],
                        (int)$request['filiere_id']
                    );
                    $id = $this->etudiantService->create($dto);
                    return new Response(true, ['id' => $id]);
                case 'update_etudiant':
                    $dto = new EtudiantUpdateDTO(
                        (int)$request['id'],
                        (string)$request['nom'],
                        (string)$request['prenom'],
                        (string)$request['email'],
                        (int)$request['filiere_id']
                    );//plag
                    $ok = $this->etudiantService->update($dto);
                    return new Response(true, ['updated' => $ok]);
                case 'create_filiere_then_student':
                    $fDto = new FiliereCreateDTO((string)$request['code'], (string)$request['libelle']);
                    $eDto = new EtudiantCreateDTO(
                        (string)$request['cne'], (string)$request['nom'], (string)$request['prenom'], (string)$request['email'], 0
                    );
                    $ids = $this->etudiantService->createFiliereThenStudentTransaction($fDto, $eDto);
                    return new Response(true, $ids);
                default:
                    return new Response(false, null, 'action inconnue');
            }//test
        } catch (BusinessException $bx) {
            return new Response(false, null, 'business_error: ' . $bx->getMessage());
        } catch (\PDOException $px) {
            return new Response(false, null, 'pdo_error: ' . $px->getMessage());
        } catch (\Throwable $tx) {
            return new Response(false, null, 'unexpected_error: ' . $tx->getMessage());
        }
    }
}
