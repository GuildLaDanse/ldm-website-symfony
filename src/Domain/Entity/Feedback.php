<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity;

use App\Domain\Entity\Account\Account;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Feedback")
 * @ORM\HasLifecycleCallbacks
 */
class Feedback
{
    const REPOSITORY = 'LaDanseDomainBundle:Feedback';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected DateTime $postedOn;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected string $feedback;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="postedBy", referencedColumnName="id", nullable=false)
     */
    protected Account $postedBy;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return DateTime
     */
    public function getPostedOn()
    {
        return $this->postedOn;
    }

    /**
     * @param DateTime $postedOn
     */
    public function setPostedOn($postedOn)
    {
        $this->postedOn = $postedOn;
    }

    /**
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param string $feedback
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * @return Account
     */
    public function getPostedBy()
    {
        return $this->postedBy;
    }

    /**
     * @param Account $postedBy
     */
    public function setPostedBy($postedBy)
    {
        $this->postedBy = $postedBy;
    }
}
