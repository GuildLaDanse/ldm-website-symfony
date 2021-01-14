<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Activity;

use App\Domain\Entity\Account\Account;
use DateTime;
use JMS\Serializer\SerializerBuilder;
use Symfony\Contracts\EventDispatcher\Event;

class ActivityEvent extends Event
{
    const EVENT_NAME = 'LaDanse.ActivityEvent';

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var DateTime
     */
    protected DateTime $time;

    /**
     * @var Account
     */
    protected Account $actor;

    /**
     * @var mixed
     */
    protected $object;

    public function __construct($type, Account $actor = null, $object = null)
    {
        $this->type = $type;
        $this->time = new DateTime();
        $this->actor = $actor;
        $this->object = $object;
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
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param DateTime $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return Account
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * @param Account $actor
     */
    public function setActor($actor)
    {
        $this->actor = $actor;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * @param object $annotatedObject
     * @return object mixed
     */
    static public function annotatedToSimpleObject($annotatedObject)
    {
        if ($annotatedObject === null)
        {
            return null;
        }
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($annotatedObject, 'json');

        return json_decode($jsonContent);
    }
}