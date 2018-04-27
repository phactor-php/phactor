<?php

namespace Carnage\Phactor\Identity;

/**
 * Generates a youtube style id matching regex: [0-9A-Za-z-_]{11}
 */
class YouTubeStyleIdentityGenerator implements GeneratorInterface
{
    public function generateIdentity()
    {
        return rtrim(strtr(base64_encode(random_bytes(22)), '+/', '-_'), '=');
    }
}