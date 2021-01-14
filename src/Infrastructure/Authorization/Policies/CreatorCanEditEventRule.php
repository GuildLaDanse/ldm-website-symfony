<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization\Policies;

use App\Domain\Entity\Event\Event;
use App\Infrastructure\Authorization\EvaluationCtx;
use App\Infrastructure\Authorization\Rule;
use App\Core\Modules\Activity\ActivityType;

class CreatorCanEditEventRule extends Rule
{
    public function match(EvaluationCtx $evaluationCt)
    {
        return $evaluationCt->getAction() == ActivityType::EVENT_EDIT
            ||
            $evaluationCt->getAction() == ActivityType::EVENT_DELETE;
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

        /** @var Event $event */
        $event = $evaluationCtx->getResourceValue();

        $account = $evaluationCtx->getSubject()->getAccount();

        return $event->getOrganiser()->getId() == $account->getId();
    }
}