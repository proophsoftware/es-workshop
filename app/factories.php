<?php

declare(strict_types=1);

use Prooph\Workshop\Infrastructure;

$factories = [];

$factories['pdoConnection'] = function (): PDO {
    static $connection;

    if(!$connection) {
        $connection = new PDO(getenv('PDO_DSN'), getenv('PDO_USER'), getenv('PDO_PWD'));
    }
    return $connection;
};

$factories['mongoConnection'] = function (): Infrastructure\MongoDb\MongoConnection {
    static $mongoConnection;

    if(!$mongoConnection) {
        $client = new \MongoDB\Client(getenv('MONGO_SERVER'));
        $mongoConnection = new Infrastructure\MongoDb\MongoConnection($client, getenv('MONGO_DB_NAME'));
    }
    return $mongoConnection;
};

$factories['eventStore'] = function () use ($factories): \Prooph\EventStore\EventStore {
    static $eventStore = null;
    if (null === $eventStore) {
        $eventStore = new \Prooph\EventStore\Pdo\PostgresEventStore(
            new \Prooph\Common\Messaging\FQCNMessageFactory(),
            $factories['pdoConnection'](),
            new \Prooph\EventStore\Pdo\PersistenceStrategy\PostgresSingleStreamStrategy()
        );
    }
    return $eventStore;
};

$factories['snapshotStore'] = function () use ($factories): \Prooph\SnapshotStore\SnapshotStore {
    $mongoConnection = $factories['mongoConnection']();
    /** @var Infrastructure\MongoDb\MongoConnection $mongoConnection */
    return new Prooph\SnapshotStore\MongoDb\MongoDbSnapshotStore($mongoConnection->client(), $mongoConnection->dbName());
};

$factories['messageFactory'] = function () use($factories): \Prooph\Common\Messaging\MessageFactory {
    static $messageFactory;

    if(!$messageFactory) {
        $messageFactory = new class() implements \Prooph\Common\Messaging\MessageFactory {
            private $fqcnMessageFactory;

            public function __construct()
            {
                $this->fqcnMessageFactory = new \Prooph\Common\Messaging\FQCNMessageFactory();;
            }

            public function createMessageFromArray(string $messageName, array $messageData): \Prooph\Common\Messaging\Message
            {
                $fqcn = Infrastructure\Util\MessageName::toFQCN($messageName);

                return $this->fqcnMessageFactory->createMessageFromArray($fqcn, $messageData);
            }
        };
    }

    return $messageFactory;
};

$factories['messageHandler'] = [

];

$factories['commandBus'] = function () use($factories): \Prooph\ServiceBus\CommandBus {
    static $commandBus;

    if(!$commandBus) {
        $commandBus = new \Prooph\ServiceBus\CommandBus();

        $commandBus->attach(
            \Prooph\ServiceBus\MessageBus::EVENT_DISPATCH,
            function(\Prooph\Common\Event\ActionEvent $dispatchEvent) use($factories): void {
                $messageName = $dispatchEvent->getParam(\Prooph\ServiceBus\MessageBus::EVENT_PARAM_MESSAGE_NAME);

                $fqcn = Infrastructure\Util\MessageName::toFQCN($messageName);

                if(!isset($factories['messageHandler'][$fqcn])) {
                    throw new \RuntimeException('No handler defined for message: ' . $messageName);
                }

                $handler = $factories['messageHandler'][$fqcn]();

                $dispatchEvent->setParam(\Prooph\ServiceBus\MessageBus::EVENT_PARAM_MESSAGE_HANDLER, $handler);
            },
            \Prooph\ServiceBus\MessageBus::PRIORITY_LOCATE_HANDLER
        );
    }

    return $commandBus;
};

$factories['eventBus'] = function () use($factories): \Prooph\ServiceBus\EventBus {
    static $eventBus;

    if(!$eventBus) {
        $eventBus = new \Prooph\ServiceBus\EventBus();
        $eventBus->attach(
            \Prooph\ServiceBus\MessageBus::EVENT_DISPATCH,
            function(\Prooph\Common\Event\ActionEvent $dispatchEvent) use($factories): void {
                $messageName = $dispatchEvent->getParam(\Prooph\ServiceBus\MessageBus::EVENT_PARAM_MESSAGE_NAME);

                $fqcn = Infrastructure\Util\MessageName::toFQCN($messageName);

                if(!isset($factories['messageHandler'][$fqcn])) {
                    throw new \RuntimeException('No handler defined for message: ' . $messageName);
                }

                $listeners = $factories['messageHandler'][$fqcn]();

                if(!is_array($listeners)) {
                    $listeners = [$listeners];
                }

                $dispatchEvent->setParam(\Prooph\ServiceBus\EventBus::EVENT_PARAM_EVENT_LISTENERS, $listeners);
            },
            \Prooph\ServiceBus\MessageBus::PRIORITY_LOCATE_HANDLER
        );
    }

    return $eventBus;
};

$factories['http'] = [
    \Prooph\Workshop\Http\Home::class => function() use ($factories): \Prooph\Workshop\Http\Home {
        return new \Prooph\Workshop\Http\Home();
    },
    \Prooph\Workshop\Http\MessageBox::class => function() use ($factories): \Prooph\Workshop\Http\MessageBox {
        return new \Prooph\Workshop\Http\MessageBox(
            $factories['commandBus'](),
            $factories['eventBus'](),
            $factories['messageFactory']()
        );
    }
];

$factories['logger'] = function () use ($factories): \Psr\Log\LoggerInterface {
    static $logger;

    if(!$logger) {
        $streamHandler = new \Monolog\Handler\StreamHandler('php://stderr');

        $logger = new \Monolog\Logger([$streamHandler]);
    }

    return $logger;
};

return $factories;
