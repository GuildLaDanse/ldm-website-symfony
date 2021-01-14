<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\DTO;

use App\Core\Modules\Common\IntegerReference;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("none")
 */
class PostSignUp
{
    /**
     * @Serializer\Type(IntegerReference::class)
     * @Serializer\SerializedName("accountReference")
     *
     * @Assert\NotNull()
     *
     * @var IntegerReference
     */
    private IntegerReference $accountReference;

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
     * @return IntegerReference
     */
    public function getAccountReference(): IntegerReference
    {
        return $this->accountReference;
    }

    /**
     * @param IntegerReference $accountReference
     *
     * @return PostSignUp
     */
    public function setAccountReference(IntegerReference $accountReference): PostSignUp
    {
        $this->accountReference = $accountReference;
        return $this;
    }

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
     * @return PostSignUp
     */
    public function setSignUpType(string $signUpType): PostSignUp
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
     * @return PostSignUp
     */
    public function setRoles(array $roles): PostSignUp
    {
        $this->roles = $roles;
        return $this;
    }
}