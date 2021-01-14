<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization\Policies;

use App\Domain\Entity\Discord\DiscordAccessToken;
use App\Infrastructure\Authorization\EvaluationCtx;
use App\Infrastructure\Authorization\Rule;
use App\Core\Modules\Activity\ActivityType;

class SelfCanRequestDiscordConnectStatus extends Rule
{
    public function match(EvaluationCtx $evaluationCt)
    {
        return $evaluationCt->getAction() == ActivityType::AUTHZ_DISCORD_CONNECT_STATUS;
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

        /** @var DiscordAccessToken $accessToken */
        $accessToken = $evaluationCtx->getResourceValue();

        return $evaluationCtx->getSubject()->getAccount()->getId() == $accessToken->getAccount()->getId();
    }
}