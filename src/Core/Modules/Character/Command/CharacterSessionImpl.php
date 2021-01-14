<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command;

use App\Domain\Entity\CharacterOrigin\CharacterSource;
use App\Domain\Entity\CharacterOrigin\CharacterSyncSession;
use App\Core\Modules\Character\CharacterSession;
use App\Core\Modules\Character\InvalidSessionStateException;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CharacterSessionImpl implements CharacterSession
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * @var CharacterSource
     */
    private CharacterSource $characterSource;

    /**
     * @var CharacterSyncSession
     */
    private CharacterSyncSession $syncSession;

    /**
     * @var string
     */
    private string $sessionState;

    /**
     * @var array
     */
    private array $logMessages;

    /**
     * CharacterSessionImpl constructor.
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry $doctrine
     */
    public function __construct(LoggerInterface $logger, EventDispatcherInterface $eventDispatcher, ManagerRegistry $doctrine)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
    }

    /**
     * @return CharacterSource
     */
    public function getCharacterSource(): CharacterSource
    {
        return $this->characterSource;
    }

    /**
     * @param CharacterSource $characterSource
     *
     * @return CharacterSessionImpl
     *
     * @throws InvalidSessionStateException
     */
    public function startSession(CharacterSource $characterSource) : CharacterSessionImpl
    {
        if ($this->sessionState != 'CONSTRUCTED')
        {
            throw new InvalidSessionStateException(
                "Session is not in state CONSTRUCTED but in state " . $this->sessionState
            );
        }

        $this->characterSource = $characterSource;

        $em = $this->doctrine->getManager();

        $this->syncSession = new CharacterSyncSession();
        $this->syncSession->setFromTime(new DateTime());
        $this->syncSession->setCharacterSource($characterSource);

        $em->persist($this->syncSession);
        $em->flush();

        $this->sessionState = 'STARTED';

        return $this;
    }

    /**
     * @return CharacterSessionImpl
     *
     * @throws InvalidSessionStateException
     */
    public function endSession() : CharacterSessionImpl
    {
        if ($this->sessionState != 'STARTED')
        {
            throw new InvalidSessionStateException(
                "Session is not in state STARTED but in state " . $this->sessionState
            );
        }

        $em = $this->doctrine->getManager();

        $this->syncSession->setEndTime(new DateTime());
        $this->syncSession->setLog(json_encode($this->logMessages));

        $em->flush();

        $this->sessionState = 'ENDED';

        return $this;
    }

    /**
     * @param string $message
     *
     * @return CharacterSession
     *
     * @throws InvalidSessionStateException
     */
    public function addMessage(string $message) : CharacterSession
    {
        if ($this->sessionState != 'STARTED')
        {
            throw new InvalidSessionStateException(
                "Session is not in state STARTED but in state " . $this->sessionState
            );
        }

        $this->logMessages[] = $message;

        return $this;
    }
}