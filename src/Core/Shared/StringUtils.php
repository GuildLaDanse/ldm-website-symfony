<?php

declare(strict_types=1);

namespace App\Core\Shared;

class StringUtils
{
    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    public static function startsWith($haystack, $needle): bool
    {
        return (strpos($haystack, $needle) === 0);
    }

    /**
     * @param $haystack
     * @param $needle
     *
     * @return bool
     */
    public static function endsWith($haystack, $needle): bool
    {
        $length = strlen($needle);

        if ($length === 0)
        {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}