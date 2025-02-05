<?php
declare(strict_types=1);

namespace App\Message\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async', handles: ResourceInterface::class)]
class AsyncApiHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ResourceInterface $message): void
    {
        //here you can send status back because entity is already persisted
    }
}
