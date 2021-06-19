<?php

namespace Phactor\Message\Dispatcher\Authorise;

class AccessDenied extends \RuntimeException
{
    public static function userNotAllowed(string $userId, array $userRoles, string $messageClass, array $allowedRoles)
    {
        $message = sprintf(
            'User %s with roles (%s) not allowed to send %s (Allowed roles: %s)',
            $userId,
            \implode(',', $userRoles),
            $messageClass,
            \implode(',', $allowedRoles)
        );

        return new self($message);
    }
}
