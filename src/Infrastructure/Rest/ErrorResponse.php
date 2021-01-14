<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Rest;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

/**
 * @ExclusionPolicy("none")
 */
class ErrorResponse
{
    /**
     * @var int
     *
     * @Type("string")
     * @SerializedName("errorCode")
     */
    private int $errorCode;

    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("errorMessage")
     */
    private string $errorMessage;

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     * @return ErrorResponse
     */
    public function setErrorCode(int $errorCode): ErrorResponse
    {
        $this->errorCode = $errorCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     * @return ErrorResponse
     */
    public function setErrorMessage(string $errorMessage): ErrorResponse
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }
}