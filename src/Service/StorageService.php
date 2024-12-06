<?php

namespace Survos\StorageBundle\Service;

use League\Flysystem\Filesystem;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StorageService
{
    public function __construct(
        #[AutowireIterator('flysystem.storage')] private $storageZones,
        private array                                    $config = [],
        private array                                    $adapters = []
    )
    {
        return;
        dd($this->adapters);
        foreach ($this->storageZones as $storageZone) {
            $adapter = $this->getPrivateProperty($storageZone, 'adapter');
            dump($adapter);
        }
        dd($storageZones);
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

    public function getZones(): array
    {
        $zones = [];
        foreach (iterator_to_array($this->storageZones) as $idx=>$flysystem) {
            $zones[$this->adapters[$idx]] = $flysystem;
        }
        return $zones;
    }

    public function getZone(string $code): Filesystem
    {
        return $this->getZones()[$code];
    }



    // this is the map from index to code, it assumes the order is the same.
    public function addAdapter(string $code, int $index)
    {
        $this->adapters[$index] = $code;
    }
    private function getPrivateProperty(mixed $object, string $property): mixed
    {
        $property  = (new \ReflectionClass($object))->getProperty($property);
        return $property->getValue($object);
    }

    public function getStorageZones(): iterable
    {
        return $this->storageZones;

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
        string  $fileName, // the filename on storage
        mixed   $body, // content to write
        ?string $storageZoneName = null,
        string  $path = '',
        array   $headers = [],
    )
    {

        $ret = $this->getEdgeApi(writeAccess: true)->uploadFile(
            $storageZoneName,
            $fileName,
            $body,
            $path,
            $headers,
        );
        return $ret;
    }


}
