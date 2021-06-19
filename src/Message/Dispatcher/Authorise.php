<?php

namespace Phactor\Message\Dispatcher;

use Phactor\DomainMessage;
use Phactor\Message\Dispatcher\Authorise\AccessDenied;
use Phactor\Message\Dispatcher\Authorise\Restricted;
use Phactor\Message\Dispatcher\Authorise\User;
use Phactor\Message\Handler;

class Authorise implements Handler
{
    private Handler $wrappedHandler;
    private array $rbac;
    private User $currentUser;

    public function __construct(Handler $wrappedHandler, array $rbac, User $currentUser)
    {
        $this->wrappedHandler = $wrappedHandler;
        $this->rbac = $rbac;
        $this->currentUser = $currentUser;
    }

    public function handle(DomainMessage $message): void
    {
        $this->checkAuth($message->getMessage());
        $message = $this->addMetadata($message);
        $this->wrappedHandler->handle($message);
    }

    private function checkAuth($message):void
    {
        if (!($message instanceof Restricted)) {
            return;
        }

        $allowedRoles = $this->getAllowedRoles($message);

        $userRoles = $this->getUserRoles();

        $check = \array_intersect($userRoles, $allowedRoles);

        if (!empty($check)) {
            return;
        }

        throw AccessDenied::userNotAllowed($this->currentUser->getId(), $userRoles, \get_class($message), $allowedRoles);
    }

    private function getAllowedRoles($message): array
    {
        $messageClass = get_class($message);

        $interfaces = class_implements($messageClass);
        array_unshift($interfaces, $messageClass);

        $allowedRoles = [];

        foreach ($interfaces as $interface) {
            if (isset($this->rbac[$interface])) {
                $allowedRoles = \array_merge($allowedRoles, $this->rbac[$interface]);
            }
        }
        return $allowedRoles;
    }

    private function getUserRoles()
    {
        $roles = $this->currentUser->getRoles();
        if (!\is_array($roles)) {
            $roles = \iterator_to_array($roles);
        }
        return $roles;
    }

    private function addMetadata(DomainMessage $message)
    {
        return $message->withMetadata(
            ['userId' => $this->currentUser->getId()]
        );
    }
}
