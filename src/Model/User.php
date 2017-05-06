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
    /**
     * @var UserId
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    public static function register(UserId $userId, string $name, string $email): User
    {
        $self = new self();

        $self->recordThat(UserWasRegistered::occur($userId->toString(), [
            'name' => $name,
            'email' => $email
        ]));

        return $self;
    }

    public function changeName(string $newName): void
    {
        $this->recordThat(UsernameWasChanged::occur($this->id->toString(), [
            'oldName' => $this->name,
            'newName' => $newName
        ]));
    }

    protected function aggregateId(): string
    {
        return $this->id->toString();
    }

    /**
     * Apply given event
     */
    protected function apply(AggregateChanged $event): void
    {
        $method = 'on' . $event->messageName();

        $this->{$method}($event);
    }

    private function onUserWasRegistered(UserWasRegistered $event): void
    {
        $this->id = $event->userId();
        $this->name = $event->name();
        $this->email = $event->email();
    }

    private function onUsernameWasChanged(UsernameWasChanged $event): void
    {
        $this->name = $event->newName();
    }
}
