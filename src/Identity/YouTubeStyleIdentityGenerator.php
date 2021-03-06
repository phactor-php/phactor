<?php

namespace Phactor\Identity;

/**
 * Generates a youtube style id matching regex: [0-9A-Za-z-_]{11}
 */
class YouTubeStyleIdentityGenerator implements Generator
{
    public function generateIdentity(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(22)), '+/', '-_'), '=');
    }
}
