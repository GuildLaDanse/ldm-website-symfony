<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization\Policies;

use App\Infrastructure\Authorization\EvaluationCtx;
use App\Infrastructure\Authorization\Rule;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Event\DTO\PostEvent;

class AllCanCreateEventWhenOrganiser extends Rule
{
    public function match(EvaluationCtx $evaluationCt)
    {
        return $evaluationCt->getAction() == ActivityType::EVENT_CREATE;
    }

    /**
     * @inheritDoc
     */
    public function evaluate(EvaluationCtx $evaluationCtx)
    {
        if ($evaluationCtx->getSubject()->isAnonymous())
        {
            return false;
        }

        /** @var PostEvent $postEvent */
        $postEvent = $evaluationCtx->getResourceValue();

        $account = $evaluationCtx->getSubject()->getAccount();

        return $postEvent->getOrganiserReference()->getId() == $account->getId();
    }
}