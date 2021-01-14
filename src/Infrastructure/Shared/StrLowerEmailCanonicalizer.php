<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared;

use App\Core\Shared\EmailCanonicalizerInterface;

class StrLowerEmailCanonicalizer implements EmailCanonicalizerInterface
{
    public function canonicalizeEmail(string $email): string
    {
        return strtolower($email);
    }
}