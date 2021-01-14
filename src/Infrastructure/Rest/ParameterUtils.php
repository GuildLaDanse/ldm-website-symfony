<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Rest;


use App\Infrastructure\Modules\ServiceException;
use Symfony\Component\HttpFoundation\Response;

final class ParameterUtils
{
    /**
     * @param $value
     * @param $parameterName
     *
     * @throws ServiceException
     */
    public static function isGuidOrThrow($value, $parameterName): void
    {
        if ((string)$value === $value)
        {
            return;
        }

        $valueLength = strlen($value);

        if ($valueLength >= 32 && $valueLength <= 36)
        {
            return;
        }

        throw new ServiceException(
            sprintf('Value for %s should be a valid guid', $parameterName),
            Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param $value
     * @param $parameterName
     *
     * @throws ServiceException
     */
    public static function isIntegerOrThrow($value, $parameterName): void
    {
        if ((string)(int)$value === $value)
        {
            return;
        }

        throw new ServiceException(
            sprintf('Value for %s should be an integer', $parameterName),
            Response::HTTP_BAD_REQUEST);
    }
}