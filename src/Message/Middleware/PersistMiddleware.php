<?php
declare(strict_types=1);

namespace App\Message\Middleware;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Symfony\Messenger\ContextStamp;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Stamp\ErrorDetailsStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

final readonly class PersistMiddleware implements MiddlewareInterface
{
    public function __construct(
        private PersistProcessor $persistProcessor,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (0 !== count($envelope->all(ReceivedStamp::class))) {
            return $stack->next()->handle($envelope, $stack);
        }

        $data = $envelope->getMessage();
        $contextStamp = $envelope->last(ContextStamp::class);
        if (!$contextStamp instanceof ContextStamp || !$data instanceof ResourceInterface) {
            return $stack->next()->handle($envelope, $stack);
        }

        try {
            $this->persistProcessor->process($data, new HttpOperation());
        } catch (\Throwable $e) {
            $envelope = $envelope->with(
                new ErrorDetailsStamp($e::class, $e->getCode(), $e->getMessage())
            );
        }

        // Continue the middleware stack
        return $stack->next()->handle($envelope, $stack);
    }
}
