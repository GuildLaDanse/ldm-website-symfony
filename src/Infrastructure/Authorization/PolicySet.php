<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;

class PolicySet extends AbstractPolicy
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var array
     */
    private array $children;

    /**
     * @var string
     */
    private string $target;

    /**
     * @var bool
     */
    private bool $default;

    /**
     * PolicySet constructor.
     *
     * @param string $name
     * @param string $target
     * @param array $children
     * @param bool $default
     */
    public function __construct($name, $target, $children, $default = false)
    {
        $this->name = $name;
        $this->children = $children;
        $this->target = $target;
        $this->default = $default;
    }

    public function match(EvaluationCtx $evaluationCtx)
    {
        return $evaluationCtx->getAction() == $this->target;
    }

    public function evaluate(EvaluationCtx $evaluationCtx)
    {
        $evalResult = $this->default;

        /** @var PolicyTreeElement $childPolicy */
        foreach($this->children as $childPolicy)
        {
            $evalResult = $evalResult || $childPolicy->evaluate($evaluationCtx);
        }

        return $evalResult;
    }
}