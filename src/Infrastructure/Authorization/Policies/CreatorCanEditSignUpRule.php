<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization\Policies;

use App\Domain\Entity\Event\SignUp;
use App\Infrastructure\Authorization\EvaluationCtx;
use App\Infrastructure\Authorization\Rule;
use App\Core\Modules\Activity\ActivityType;

class CreatorCanEditSignUpRule extends Rule
{
    public function match(EvaluationCtx $evaluationCt)
    {
        return $evaluationCt->getAction() == ActivityType::SIGNUP_EDIT;
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

        /** @var SignUp $signUp */
        $signUp = $evaluationCtx->getResourceValue();

        $account = $evaluationCtx->getSubject()->getAccount();

        return $signUp->getAccount()->getId() == $account->getId();
    }
}