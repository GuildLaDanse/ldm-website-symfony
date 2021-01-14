<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Character;


use App\Domain\Entity\VersionedEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CharacterClaimVersion")
 */
class ClaimVersion extends VersionedEntity
{
    const REPOSITORY = 'LaDanseDomainBundle:ClaimVersion';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @var Claim
     *
     * @ORM\ManyToOne(targetEntity="Claim")
     * @ORM\JoinColumn(name="claimId", referencedColumnName="id", nullable=false)
     */
    protected Claim $claim;

    /**
     * @var string
     *
     * @ORM\Column(type="text", length=1024, nullable=true)
     */
    protected string $comment;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $raider = false;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getClaim()
    {
        return $this->claim;
    }

    /**
     * @param mixed $claim
     * @return $this
     */
    public function setClaim($claim)
    {
        $this->claim = $claim;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRaider()
    {
        return $this->raider;
    }

    /**
     * @param boolean $raider
     * @return $this
     */
    public function setRaider(bool $raider)
    {
        $this->raider = $raider;
        return $this;
    }
}
