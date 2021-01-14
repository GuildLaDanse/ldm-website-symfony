<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;


use Exception;

class EvaluationCtx
{
    /**
     * @var SubjectReference
     */
    private SubjectReference $subject;

    /**
     * @var string
     */
    private string $action;

    /**
     * @var ResourceReference
     */
    private ResourceReference $resource;

    /**
     * @var ResourceFinder
     */
    private ResourceFinder $resourceFinder;

    public function __construct(
        SubjectReference $subject,
        $action,
        ResourceReference $resource,
        ResourceFinder $resourceFinder
    )
    {
        $this->subject = $subject;
        $this->action = $action;
        $this->resource = $resource;
        $this->resourceFinder = $resourceFinder;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function isSubjectInGroup()
    {
        if ($this->subject->isAnonymous())
        {
            return false;
        }

        return true;
    }

    public function getResourceType()
    {
        return $this->resource->getResourceType();
    }

    /**
     * @return mixed
     * @throws UnresolvableResourceException
     */
    public function getResourceValue()
    {
        try
        {
            return $this->resourceFinder->getResourceValue($this->resource);
        }
        catch(Exception $e)
        {
            throw new UnresolvableResourceException('Exception while trying to retrieve resource value', 0, $e);
        }
    }
}