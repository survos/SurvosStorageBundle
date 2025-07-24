<?php

namespace Survos\StorageBundle\Command;

use Survos\StorageBundle\Service\StorageService;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('storage:list', 'list storage files')]
final class StorageListCommand extends Command
{

    public function __construct(
        private readonly StorageService $storageService,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle                                                                                          $io,
        #[Argument(description: 'path name within zone')] string        $path='',
        #[Argument(name: 'zone', description: 'zone id, e.g. default.storage')] string        $zoneId='',
        #[Option] ?bool $recursive = null,

    ): int
    {
        // zone is old, from bunny.  @todo: see flysystem
        $storage = $this->storageService->getZone($zoneId);
        $iterator = $storage->listContents($path, $recursive);
        foreach ($iterator as $file) {
            dd($file);
        }

        $adapters = $this->storageService->getAdapters();
        $table = new Table($io);
        $table->setHeaderTitle($zoneId . "/" . $path);
        $headers = ['Name', 'Class','root'];
        $table->setHeaders($headers);
        foreach ($adapters as $adapter) {
            $row = [
                $adapter->getName(),
                $adapter->getClass(),
                $adapter->getRootLocation()??$adapter->getBucket(),
            ];
//            $row['Id'] = "<href=https://dash.storage.net/storage/$id/file-manager>$id</>";

            $table->addRow($row);
        }
        $table->render();
        return self::SUCCESS;
    }




}
