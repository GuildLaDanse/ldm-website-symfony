<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Doctrine;

use Doctrine\ORM\Query\AST\ASTException;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;

class CollateFunction extends FunctionNode
{
    /**
     * @var PathExpression
     */
    public ?PathExpression $expressionToCollate = null;

    /**
     * @var string
     */
    public ?string $collation = null;

    /**
     * @param Parser $parser
     *
     * @throws QueryException
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->expressionToCollate = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $parser->match(Lexer::T_IDENTIFIER);
        $lexer = $parser->getLexer();
        $this->collation = $lexer->token['value'];
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @param SqlWalker $sqlWalker
     *
     * @return string
     *
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf('%s COLLATE %s', $this->expressionToCollate->dispatch($sqlWalker), $this->collation);
    }
}