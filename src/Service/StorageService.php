<?php

namespace Survos\StorageBundle\Service;

use Aws\S3\S3ClientInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use Psr\Http\Client\ClientInterface;
use Survos\StorageBundle\Model\Adapter;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StorageService
{
    /**
     * @param iterable $storageZones <Filesystem[]>
     * @param array $config
     * @param array $zoneMap
     */
    public function __construct(
        #[AutowireIterator('flysystem.storage')] private iterable $storageZones, // no key!
        private array                                    $config = [],
        private array $zoneMap = [] // maps code to zone index
    )
    {
        return;
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

    public function getAdapter(string $storageZone): FilesystemAdapter
    {
        $storageZone = $this->getZone($storageZone);
        $adapter = $this->getPrivateProperty($storageZone, 'adapter');
        return $adapter;
    }

    public function getClient(FilesystemAdapter $adapter): ClientInterface|S3ClientInterface
    {
        return $this->getprivateProperty($adapter, 'client');
    }

    public function getBucket(FilesystemAdapter $adapter): string
    {
        return $this->getprivateProperty($adapter, 'bucket');
    }

    public function getZones(): array
    {
        $zones = [];
        foreach (iterator_to_array($this->storageZones) as $idx=>$flysystem) {
            $zones[$this->zoneMap[$idx]] = $flysystem;
        }
        return $zones;
    }

    public function getAdapters(): array
    {
        $adapters = [];
        foreach ($this->storageZones as $idx => $flysystem) {
            $flysystemAdapter = $this->getPrivateProperty($flysystem, 'adapter');
            // now map the adapter private properties
            $adapter = new Adapter(
                $this->zoneMap[$idx],
                $flysystemAdapter::class,
                $this->getPrivateProperty($flysystemAdapter, 'rootLocation'),
                $this->getPrivateProperty($flysystemAdapter, 'bucket')
            );


            $adapters[$this->zoneMap[$idx]] = $adapter;
        }
        return $adapters;
    }

    public function getZone(string $code): Filesystem
    {
        return $this->getZones()[$code];
    }



    // this is the map from index to code, it assumes the order is the same.
    public function addAdapter(string $code, int $index)
    {
        $this->zoneMap[$index] = $code;
    }
    private function getPrivateProperty(mixed $object, string $property): mixed
    {
        $reflection = new \ReflectionClass($object);
        if ($reflection->hasProperty($property)) {
            return $reflection->getProperty($property)->getValue($object);
        } else {
            return null;
        }
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
        $adapter = $this->getZone($storageZone);
        dd($path, $filename);
        if (!$adapter->has($filename)) {

        }
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
