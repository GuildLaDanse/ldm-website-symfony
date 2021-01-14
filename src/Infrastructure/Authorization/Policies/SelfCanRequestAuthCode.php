<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization\Policies;

use App\Infrastructure\Authorization\EvaluationCtx;
use App\Infrastructure\Authorization\Rule;
use App\Core\Modules\Activity\ActivityType;

class SelfCanRequestAuthCode extends Rule
{
    public function match(EvaluationCtx $evaluationCt)
    {
        return $evaluationCt->getAction() == ActivityType::AUTHZ_DISCORD_REQUEST_AUTHCODE;
    }

    public function evaluate(EvaluationCtx $evaluationCtx)
    {
        if ($evaluationCtx->getSubject()->isAnonymous())
        {
            return false;
        }

        return $evaluationCtx->getSubject()->getAccount()->getId() == $evaluationCtx->getResourceValue();
    }
}