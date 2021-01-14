<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity;

use App\Domain\Entity\Account\Account;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CalendarExport")
 * @ORM\HasLifecycleCallbacks
 */
class CalendarExport
{
    const REPOSITORY = 'LaDanseDomainBundle:CalendarExport';

    /**
     *
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $exportNew;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $exportAbsence;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected string $secret;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="accountId", referencedColumnName="id", nullable=false)
     */
    protected Account $account;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return CalendarExport
     */
    public function setId(int $id): CalendarExport
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExportNew(): bool
    {
        return $this->exportNew;
    }

    /**
     * @param bool $exportNew
     * @return CalendarExport
     */
    public function setExportNew(bool $exportNew): CalendarExport
    {
        $this->exportNew = $exportNew;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExportAbsence(): bool
    {
        return $this->exportAbsence;
    }

    /**
     * @param bool $exportAbsence
     * @return CalendarExport
     */
    public function setExportAbsence(bool $exportAbsence): CalendarExport
    {
        $this->exportAbsence = $exportAbsence;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @param string $secret
     * @return CalendarExport
     */
    public function setSecret(string $secret): CalendarExport
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     * @return CalendarExport
     */
    public function setAccount(Account $account): CalendarExport
    {
        $this->account = $account;
        return $this;
    }
}
