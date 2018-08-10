<?php

namespace Carnage\Phactor\Auth;

use Carnage\Phactor\Message\Bus;
use Carnage\Phactor\Message\DomainMessage;
use Carnage\Phactor\Message\Handler;

class AuthorisationDelegator implements Bus
{
    private $wrappedBus;
    private $rbac;
    private $currentUser;

    public function __construct(Bus $wrappedBus, array $rbac, User $currentUser)
    {
        $this->wrappedBus = $wrappedBus;
        $this->rbac = $rbac;
        $this->currentUser = $currentUser;
    }

    public function handle(DomainMessage $message): void
    {
        $this->checkAuth($message->getMessage());
        $message = $this->addMetadata($message);
        $this->wrappedBus->handle($message);
    }

    public function subscribe(string $identifier, Handler $handler): void
    {
        $this->wrappedBus->subscribe($identifier, $handler);
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