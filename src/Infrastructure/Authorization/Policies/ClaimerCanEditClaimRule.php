<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization\Policies;

use App\Domain\Entity\Character\Claim;
use App\Infrastructure\Authorization\EvaluationCtx;
use App\Infrastructure\Authorization\Rule;
use App\Core\Modules\Activity\ActivityType;

class ClaimerCanEditClaimRule extends Rule
{
    public function match(EvaluationCtx $evaluationCt)
    {
        return
            $evaluationCt->getAction() == ActivityType::CLAIM_EDIT
            ||
            $evaluationCt->getAction() == ActivityType::CLAIM_REMOVE;
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

        /** @var Claim $claim */
        $claim = $evaluationCtx->getResourceValue();

        $account = $evaluationCtx->getSubject()->getAccount();

        return $claim->getAccount()->getId() == $account->getId();
    }
}