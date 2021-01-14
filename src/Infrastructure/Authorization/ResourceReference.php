<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;

abstract class ResourceReference
{
    /**
     * @var string
     */
    private string $resourceType;

    public function __construct($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }
}