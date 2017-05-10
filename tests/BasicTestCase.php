<?php
declare(strict_types = 1);

namespace Prooph\WorkshopTest;

use PHPUnit\Framework\TestCase;
use Prooph\EventSourcing\AggregateRoot;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\Workshop\Model\Event\DomainEvent;

class BasicTestCase extends TestCase
{
    /**
     * @param AggregateRoot $aggregateRoot
     * @return DomainEvent[]
     */
    protected function extractRecordedEvents(AggregateRoot $aggregateRoot): array
    {
        $aggregateRootTranslator = new AggregateTranslator();

        return $aggregateRootTranslator->extractPendingStreamEvents($aggregateRoot);
    }
}
