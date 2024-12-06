<?php

namespace Survos\StorageBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StorageService
{
    public function __construct(
        #[AutowireIterator('flysystem.storage')] $storages,
        private array $zones = []
    ) {
        dd($storages);
// Create a StorageClient using any HTTP client implementing "Psr\Http\Client\ClientInterface".
        if (!$this->storageZone) {
            $this->storageZone = $this->config['storage_zone'];
        }
        foreach ($this->config['zones'] as $zoneData) {
            if (!array_key_exists('name', $zoneData)) {
                throw new \LogicException($this->storageZone . " is not defined in config/packages/survos_storage.yaml");
            }
            $this->zones[$zoneData['name']] = $zoneData;
        }
    }

    public function getConfig(): array
    {
        return $this->config;
    }


    public function downloadFile(string $filename, string $path, ?string $storageZone = null): StorageClientResponseInterface
    {
        $ret = $this->getEdgeApi()->downloadFile(
            storageZoneName: $storageZone ?? $this->getStorageZone(),
            path: $path,
            fileName: $filename,
        );

        return $ret;
    }

    public function uploadFile(
        string $fileName, // the filename on storage
        mixed $body, // content to write
        ?string $storageZoneName = null,
        string $path = '',
        array $headers = [],
    ) {

        $ret = $this->getEdgeApi(writeAccess: true)->uploadFile(
            $storageZoneName,
            $fileName,
            $body,
            $path,
            $headers,
        );
        return $ret;
    }


    public function getZones(): array
    {
        return $this->config['zones'];
    }
}
