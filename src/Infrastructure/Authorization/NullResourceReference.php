<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;

class NullResourceReference extends ResourceReference
{
    public function __construct()
    {
        parent::__construct('NullResourceReference');
    }

    public function getResourceType()
    {
        return "NullResourceReference";
    }

    public function getResourceId()
    {
        return "NullResourceReference";
    }
}