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
class PutSignUp
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("signUpType")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private string $signUpType;

    /**
     * @Serializer\Type("array<string>")
     * @Serializer\SerializedName("roles")
     *
     * @var array
     */
    private array $roles;

    /**
     * @return string
     */
    public function getSignUpType(): string
    {
        return $this->signUpType;
    }

    /**
     * @param string $signUpType
     *
     * @return PutSignUp
     */
    public function setSignUpType(string $signUpType): PutSignUp
    {
        $this->signUpType = $signUpType;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return PutSignUp
     */
    public function setRoles(array $roles): PutSignUp
    {
        $this->roles = $roles;
        return $this;
    }
}