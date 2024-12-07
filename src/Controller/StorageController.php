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

    #[Route('/zones', name: 'survos_storage_zones', methods: ['GET'])]
    #[Template('@SurvosStorage/zones.html.twig')]
    public function zones(
    ): Response|array
    {

        $this->checkSimpleDatatablesInstalled();
        return [
            'adapters' => $this->storageService->getAdapters(),
            'zones' => $this->storageService->getZones()];
    }

    #[Route('/{zoneName}}/{path}/{fileName}/download', name: 'survos_storage_download', methods: ['GET'], requirements: ['path'=> ".+"])]
    #[Template('@SurvosStorage/zone.html.twig')]
    public function download(string $zoneName, string $path, string $fileName): Response
    {
        $response = $this->storageService->downloadFile($fileName,$path,$zoneName);
        return new Response($response); // eh
    }

    #[Route('/show/{zoneId}/{path}', name: 'survos_storage_view', methods: ['GET'], requirements: ['path'=> ".+"])]
    #[Template('@SurvosStorage/show.html.twig')]
    public function show(string $zoneId, string $path): Response|array
    {
        $service = $this->storageService->getZone($zoneId);
        $file  = [
            'name' => $path,
            'size' => $service->fileSize($path),
        ];
        return ['file' => $file];
        $response = $this->storageService->downloadFile($fileName,$path,$zoneName);
        return new Response($response); // eh
    }

    #[Route('/{zoneId}/{path}', name: 'survos_storage_zone', methods: ['GET'],
        requirements: [
            'zoneId'=>'[0-9a-z_.]*',
            'path'=>'.+'
        ]
    )]
    #[Template('@SurvosStorage/zone.html.twig')]
    public function zone(
        string $zoneId,
        ?string $path='/'
    ): Response|array
    {
        $storage = $this->storageService->getZone($zoneId);
        $this->checkSimpleDatatablesInstalled();
        $files = $storage->listContents($path, false);
//        dd(iterator_to_array($files));
//        $edgeStorageApi = $this->storageService->getEdgeApi($zoneName);
//        $list = $edgeStorageApi->listFiles(
//            storageZoneName: $zoneName,
//            path: $path
//        );
        return [
            'zoneId' => $zoneId,
            'path' => $path,
            'files' => iterator_to_array($files)
        ];
    }
}
