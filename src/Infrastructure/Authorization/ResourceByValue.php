<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;

class ResourceByValue extends ResourceReference
{
    /**
     * @var mixed
     */
    private $resourceValue;

    public function __construct($resourceType, $resourceValue)
    {
        parent::__construct($resourceType);

        $this->resourceValue = $resourceValue;
    }

    /**
     * @return mixed
     */
    public function getResourceValue()
    {
        return $this->resourceValue;
    }
}