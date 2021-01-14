<?php

declare(strict_types=1);

namespace App\Core\Shared;

interface EmailCanonicalizerInterface
{
    public function canonicalizeEmail(string $email) : string;
}