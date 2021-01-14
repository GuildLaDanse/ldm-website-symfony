<?php
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/bderidder/ldm-guild-website
 */

namespace App\Infrastructure\Authorization\Policies;

use App\Infrastructure\Authorization\EvaluationCtx;
use App\Infrastructure\Authorization\Rule;
use App\Core\Modules\Activity\ActivityType;

class AllCanCreateGameDataRule extends Rule
{
    public function match(EvaluationCtx $evaluationCt)
    {
        return (
            $evaluationCt->getAction() == ActivityType::REALM_CREATE
            || $evaluationCt->getAction() == ActivityType::GUILD_CREATE
        );
    }

    public function evaluate(EvaluationCtx $evaluationCtx)
    {
        return true;
    }
}