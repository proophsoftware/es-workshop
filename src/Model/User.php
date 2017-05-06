<?php
declare(strict_types = 1);

namespace Prooph\Workshop\Model;

use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use Prooph\Workshop\Model\Event\UsernameWasChanged;
use Prooph\Workshop\Model\Event\UserWasRegistered;
use Prooph\Workshop\Model\User\UserId;

final class User extends AggregateRoot
{

    protected function aggregateId(): string
    {
        // TODO: Implement aggregateId() method.
    }

    /**
     * Apply given event
     */
    protected function apply(AggregateChanged $event): void
    {
        // TODO: Implement apply() method.
    }
}
