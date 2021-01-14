<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;

class ResourceById extends ResourceReference
{
    /**
     * @var mixed
     */
    private $resourceId;

    public function __construct($resourceType, $resourceId)
    {
        parent::__construct($resourceType);

        $this->resourceId = $resourceId;
    }

    /**
     * @return mixed
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }
}