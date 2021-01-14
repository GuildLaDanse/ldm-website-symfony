<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization\Policies;

use App\Infrastructure\Authorization\EvaluationCtx;
use App\Infrastructure\Authorization\Rule;

class AllowCommandRule extends Rule
{
    public function match(EvaluationCtx $evaluationCt)
    {
        return true;
    }

    public function evaluate(EvaluationCtx $evaluationCtx)
    {
        return 'cli' === PHP_SAPI;
    }
}