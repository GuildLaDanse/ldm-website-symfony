<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("none")
 */
class PutEventState
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("state")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private string $state;

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return PutEventState
     */
    public function setState(string $state): PutEventState
    {
        $this->state = $state;
        return $this;
    }
}