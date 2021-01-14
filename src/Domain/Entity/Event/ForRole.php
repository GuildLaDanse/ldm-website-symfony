<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Event;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event\ForRoleRepository")
 * @ORM\Table(name="ForRole")
 */
class ForRole
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable=false)
     */
    protected string $role;

    /**
     * @var SignUp
     *
     * @ORM\ManyToOne(targetEntity="SignUp", inversedBy="roles")
     * @ORM\JoinColumn(name="signUpId", referencedColumnName="id", nullable=false)
     */
    protected SignUp $signUp;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return ForRole
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set signUp
     *
     * @param SignUp $signUp
     * @return ForRole
     */
    public function setSignUp(SignUp $signUp = null)
    {
        $this->signUp = $signUp;

        return $this;
    }

    /**
     * Get signUp
     *
     * @return SignUp
     */
    public function getSignUp()
    {
        return $this->signUp;
    }
}
