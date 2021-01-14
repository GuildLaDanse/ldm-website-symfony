<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\DTO;

use App\Core\Modules\Common\AccountReference;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("none")
 */
class SignUp
{
    /**
     * @Serializer\SerializedName("id")
     *
     * @var int
     */
    protected int $id;

    /**
     * @Serializer\SerializedName("accountRef")
     *
     * @var AccountReference
     */
    protected AccountReference $account;

    /**
     * @Serializer\SerializedName("type")
     *
     * @var string
     */
    protected string $type;

    /**
     * @Serializer\SerializedName("roles")
     *
     * @var array
     */
    protected array $roles;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return SignUp
     */
    public function setId(int $id): SignUp
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AccountReference
     */
    public function getAccount(): AccountReference
    {
        return $this->account;
    }

    /**
     * @param AccountReference $account
     *
     * @return SignUp
     */
    public function setAccount(AccountReference $account): SignUp
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return SignUp
     */
    public function setType($type): SignUp
    {
        $this->type = $type;
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
     * @return SignUp
     */
    public function setRoles($roles): SignUp
    {
        $this->roles = $roles;
        return $this;
    }
}
