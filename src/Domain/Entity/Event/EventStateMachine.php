<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Event;

use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachine;

class EventStateMachine
{
    public const PENDING     = 'Pending';
    public const CONFIRMED   = 'Confirmed';
    public const CANCELLED   = 'Cancelled';
    public const NOTHAPPENED = 'NotHappened';
    public const HAPPENED    = 'Happened';
    public const DELETED     = 'Deleted';
    public const ARCHIVED    = 'Archived';

    public const TR_CONFIRM              = 'confirm';
    public const TR_CANCEL               = 'cancel';
    public const TR_CONFIRM_HAPPENED     = 'confirmHappened';
    public const TR_CONFIRM_NOT_HAPPENED = 'confirmNotHappened';
    public const TR_ARCHIVE              = 'archive';
    public const TR_DELETE               = 'delete';

    /**
     * @param $object
     * 
     * @return StateMachine
     */
    public static function create($object): StateMachine
    {
        $sm = new StateMachine($object);

        // Define states
        $sm->addState(new State(self::PENDING, StateInterface::TYPE_INITIAL));
        $sm->addState(self::CONFIRMED);
        $sm->addState(self::CANCELLED);
        $sm->addState(self::NOTHAPPENED);
        $sm->addState(self::HAPPENED);
        $sm->addState(new State(self::DELETED, StateInterface::TYPE_FINAL));
        $sm->addState(new State(self::ARCHIVED, StateInterface::TYPE_FINAL));

        // Define transitions
        $sm->addTransition(self::TR_CONFIRM,              self::PENDING,     self::CONFIRMED);
        $sm->addTransition(self::TR_CANCEL,               self::PENDING,     self::CANCELLED);
        $sm->addTransition(self::TR_CANCEL,               self::CONFIRMED,   self::CANCELLED);
        $sm->addTransition(self::TR_CONFIRM_HAPPENED,     self::CONFIRMED,   self::HAPPENED);
        $sm->addTransition(self::TR_CONFIRM_NOT_HAPPENED, self::CONFIRMED,   self::NOTHAPPENED);
        $sm->addTransition(self::TR_ARCHIVE,              self::PENDING,     self::ARCHIVED);
        $sm->addTransition(self::TR_ARCHIVE,              self::CANCELLED,   self::ARCHIVED);
        $sm->addTransition(self::TR_ARCHIVE,              self::NOTHAPPENED, self::ARCHIVED);
        $sm->addTransition(self::TR_ARCHIVE,              self::HAPPENED,    self::ARCHIVED);
        $sm->addTransition(self::TR_DELETE,               self::PENDING,     self::DELETED);

        return $sm;
    }
}