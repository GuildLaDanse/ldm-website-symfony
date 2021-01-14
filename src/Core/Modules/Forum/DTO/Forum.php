<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Forum\DTO;

use JMS\Serializer\Annotation\SerializedName;

class Forum
{
    /**
     * @SerializedName("id")
     *
     * @var string
     */
    protected string $id;

    /**
     * @SerializedName("name")
     *
     * @var string
     */
    protected string $name;

    /**
     * @SerializedName("description")
     *
     * @var string
     */
    protected string $description;

    /**
     * @SerializedName("topics")
     *
     * @var array
     */
    protected array $topicEntries;

    /**
     * Forum constructor.
     * @param string $id
     * @param string $name
     * @param string $description
     * @param array $topicEntries
     */
    public function __construct($id,
                                $name,
                                $description,
                                array $topicEntries = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->topicEntries = $topicEntries;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getTopicEntries()
    {
        return $this->topicEntries;
    }
}