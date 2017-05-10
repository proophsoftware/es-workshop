<?php
declare(strict_types = 1);

namespace Prooph\WorkshopTest\Model;


use Prooph\Workshop\Model\Configuration;
use Prooph\Workshop\Model\Configuration\Node;
use Prooph\Workshop\Model\Event\NewConfigWasStarted;
use Prooph\WorkshopTest\BasicTestCase;
use Ramsey\Uuid\Uuid;

final class ConfigurationTest extends BasicTestCase
{
    /**
     * @test
     */
    public function it_starts_a_new_config_with_start_and_end_node()
    {
        $startNode = Node::asStartNode();
        $endNode = Node::asEndNode();
        $configurationId = Uuid::uuid4();

        $configuration = Configuration::startNewConfig($configurationId, $startNode, $endNode);

        $events = $this->extractRecordedEvents($configuration);

        self::assertCount(1, $events);

        /** @var NewConfigWasStarted $event */
        $event = $events[0];

        self::assertInstanceOf(NewConfigWasStarted::class, $event);
        self::assertEquals($configurationId->toString(), $event->configurationId()->toString());
        self::assertTrue($event->startNode()->isStartNode());
        self::assertTrue($event->endNode()->isEndNode());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_no_start_node_is_passed()
    {
        $startNode = Node::asNode();
        $endNode = Node::asEndNode();
        $configurationId = Uuid::uuid4();

        self::expectExceptionMessage('Node ist not a start node');

        $configuration = Configuration::startNewConfig($configurationId, $startNode, $endNode);
    }
}
