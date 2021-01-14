<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;

abstract class PolicyTreeElement
{
    abstract public function match(EvaluationCtx $evaluationCt);

    /**
     * @param EvaluationCtx $evaluationCtx
     * @return bool
     *
     * @throws UnresolvableResourceException
     */
    abstract public function evaluate(EvaluationCtx $evaluationCtx);
}