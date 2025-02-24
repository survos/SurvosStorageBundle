<?php

namespace Survos\StorageBundle\Command;

use League\Flysystem\DirectoryAttributes;
use Survos\StorageBundle\Event\DirectoryListingEvent;
use Survos\StorageBundle\Message\DirectoryListingMessage;
use Survos\StorageBundle\Service\StorageService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Bytes;
use Zenstruck\Console\Attribute\Argument;
use Zenstruck\Console\Attribute\Option;
use Zenstruck\Console\InvokableServiceCommand;
use Zenstruck\Console\IO;
use Zenstruck\Console\RunsCommands;
use Zenstruck\Console\RunsProcesses;

#[AsCommand('storage:iterate', 'iterate and dispatch an event though storage directories')]
final class IterateCommand extends InvokableServiceCommand
{
    use RunsCommands;
    use RunsProcesses;

    public function __construct(
        private readonly StorageService $storageService,
        private MessageBusInterface $messageBus,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        IO                                                                                          $io,
        #[Argument(description: 'path name within zone')] string        $path='',
        #[Argument(name: 'zone', description: 'zone id, e.g. default.storage')] string        $zoneId='',
        #[Option] bool $recursive = false,
        #[Option(description: "Dispatch a DirectoryListingMessage event")] bool $dispatch = false,

    ): int
    {
        if (!$dispatch) {
            $this->io()->warning('use --dispatch to do something besides list');
        }
        $table = new Table($io);
        $storage = $this->storageService->getZone($zoneId);

        if ($dispatch) {
            $message = new DirectoryListingMessage($zoneId, 'dir', $path);
            $this->messageBus->dispatch($message);
            $io->success("$zoneId $path dispatched");
        }
        return self::SUCCESS;
        dd();

        // recursive is for debugging only, not the message
        $iterator = $storage->listContents($path, $recursive);
        $table->setHeaderTitle($zoneId . "/" . $path);
        $table->setHeaders(['type', 'path']);
        /** @var DirectoryAttributes $file */
        foreach ($iterator as $file) {
            if ($dispatch) {
                $message = new DirectoryListingMessage($zoneId, $file->type(),
                    $file->path(), $file->visibility(), $file->lastModified(),
                );
                $this->messageBus->dispatch($message);
            }
            $table->addRow([$file->type(), $file->path()]);
        }
        $table->render();
        return self::SUCCESS;
    }

}
