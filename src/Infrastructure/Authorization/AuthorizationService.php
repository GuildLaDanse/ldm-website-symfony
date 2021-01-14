<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;

use App\Infrastructure\Authorization\Policies\PolicyCatalog;
use Exception;
use Psr\Log\LoggerInterface;

class AuthorizationService
{
    /**
     * @var LoggerInterface
     */
    public LoggerInterface $logger;

    /**
     * @var PolicyCatalog
     */
    private PolicyCatalog $policyCatalog;

    /**
     * @var ResourceFinder
     */
    private ResourceFinder $resourceFinder;

    /**
     * @param LoggerInterface $logger
     * @param ResourceFinder $resourceFinder
     */
    public function __construct(LoggerInterface $logger, ResourceFinder $resourceFinder)
    {
        $this->logger = $logger;

        $this->policyCatalog = new PolicyCatalog();
        $this->resourceFinder = $resourceFinder;
    }

    /**
     * Verify if $subject is authorized to perform $action on $resource
     *
     * @param SubjectReference $subject
     * @param string $action
     * @param ResourceReference $resource
     *
     * @return bool
     *
     * @throws NotAuthorizedException
     */
    public function allowOrThrow(SubjectReference $subject, $action, ResourceReference $resource)
    {
        try
        {
            $evalutionResult = $this->evaluate($subject, $action, $resource);
        }
        catch(Exception $e)
        {
            $this->logger->warning(
                __CLASS__ . ' could not evaluate authorization request',
                [
                    "exception" => $e->getMessage(),
                    "request"   => $this->createAuthZRequestRepresentation($subject, $action, $resource)
                ]
            );

            throw new NotAuthorizedException("Could not evaluate authorization request", $e);
        }

        if (!$evalutionResult)
        {
            $this->logger->warning(
                __CLASS__ . ' the subject is not authorized to perform this action on this resource',
                $this->createAuthZRequestRepresentation($subject, $action, $resource)
            );

            throw new NotAuthorizedException("The subject is not authorized to perform this action on this resource");
        }

        $this->logger->debug(
            __CLASS__ . ' allowing authorization request',
            [
                "request"   => $this->createAuthZRequestRepresentation($subject, $action, $resource)
            ]
        );

        return true;
    }

    /**
     * Verify if $subject is authorized to perform $action on $resource
     *
     * @param SubjectReference $subject
     * @param string $action
     * @param ResourceReference $resource
     *
     * @return bool
     *
     * @throws CannotEvaluateException
     */
    public function evaluate(SubjectReference $subject, $action, ResourceReference $resource)
    {
        $evaluationCtx = new EvaluationCtx(
            $subject,
            $action,
            $resource,
            $this->resourceFinder
        );

        $matchedPolicy = null;

        try
        {
            $matchedPolicy = $this->findMatchingPolicy($this->policyCatalog->getPolicies(), $evaluationCtx);
        }
        catch(AuthorizationException $e)
        {
            $this->logger->error(
                'Could not find single matching policy for this evaluation request',
                ['exception' => $e]
            );

            throw new CannotEvaluateException('Cannot evaluate because no single policy matched', 0, $e);
        }

        try
        {
            return $matchedPolicy->evaluate($evaluationCtx);
        }
        catch(AuthorizationException $e)
        {
            $this->logger->error(
                'Could not properly evaluate matching policy',
                ['exception' => $e]
            );

            throw new CannotEvaluateException('Cannot evaluate', 0, $e);
        }
    }

    /**
     * @param array $policies
     * @param EvaluationCtx $evaluationCtx
     *
     * @return PolicyTreeElement|mixed|null
     *
     * @throws TooManyPoliciesMatchException
     * @throws NoMatchingPolicyFoundException
     */
    private function findMatchingPolicy(array $policies, EvaluationCtx $evaluationCtx)
    {
        $matchedPolicy = null;

        /** @var PolicyTreeElement $policy */
        foreach($policies as $policy)
        {
            if ($policy->match($evaluationCtx))
            {
                if ($matchedPolicy === null)
                {
                    $matchedPolicy = $policy;
                }
                else
                {
                    throw new TooManyPoliciesMatchException('more than one top policy matched the evaluation context');
                }
            }
        }

        if ($matchedPolicy === null)
        {
            throw new NoMatchingPolicyFoundException('No matching top policy found');
        }

        return $matchedPolicy;
    }

    private function createAuthZRequestRepresentation(SubjectReference $subject, $action, ResourceReference $resource)
    {
        return [
            "subject" => [
                "id"   => $subject->getAccount()->getId(),
                "name" => $subject->getAccount()->getDisplayName(),
            ],
            "action" => $action,
            "resource" => [
                "type"      => $resource->getResourceType(),
                "reference" => get_class($resource)
            ]
        ];
    }
}