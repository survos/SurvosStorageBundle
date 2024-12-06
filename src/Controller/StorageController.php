<?php

namespace Survos\StorageBundle\Controller;

use League\Flysystem\FilesystemOperator;
use Survos\StorageBundle\Service\StorageService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StorageController extends AbstractController
{
    public function __construct(
        private StorageService $storageService,
        #[AutowireIterator('flysystem.storage')] $storages,
        private $simpleDatatablesInstalled = false
    )
    {

    }

    private function checkSimpleDatatablesInstalled()
    {
        if (! $this->simpleDatatablesInstalled) {
            throw new \LogicException("This page requires SimpleDatatables\n composer req survos/simple-datatables-bundle");
        }
    }

    #[Route('/flysystem_default', name: 'flysystem_browse_default')]
    public function flysystem(
        FilesystemOperator $defaultStorage): Response
    {

    }

    #[Route('/zones', name: 'survos_storage_zones', methods: ['GET'])]
    #[Template('@SurvosStorage/zones.html.twig')]
    public function zones(
    ): Response|array
    {
        $this->checkSimpleDatatablesInstalled();
        $baseApi = $this->storageService->getBaseApi();
        return ['zones' => $baseApi->listStorageZones()->getContents()];
    }

    #[Route('/{zoneName}}/{path}/{fileName}', name: 'survos_storage_download', methods: ['GET'], requirements: ['path'=> ".+"])]
    #[Template('@SurvosStorage/zone.html.twig')]
    public function download(string $zoneName, string $path, string $fileName): Response
    {

        $response = $this->storageService->downloadFile($fileName,$path,$zoneName);
        return new Response($response); // eh
    }


    #[Route('/{zoneName}/{id}/{path}', name: 'survos_storage_zone', methods: ['GET'])]
    #[Template('@SurvosStorage/zone.html.twig')]
    public function zone(
        string $zoneName,
        string $id,
        ?string $path='/'
    ): Response|array
    {
        $this->checkSimpleDatatablesInstalled();
        $edgeStorageApi = $this->storageService->getEdgeApi($zoneName);
        $list = $edgeStorageApi->listFiles(
            storageZoneName: $zoneName,
            path: $path
        );
        return [
            'zoneName' => $zoneName,
            'path' => $path,
            'files' => $list->getContents()
        ];
    }
}
