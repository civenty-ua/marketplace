<?php
declare(strict_types = 1);

namespace App\DoctrineFilters;

use Doctrine\ORM\Query\{
    Lexer,
    Parser,
    SqlWalker,
    QueryException,
};
use Doctrine\ORM\Query\AST\{
    ASTException,
    PathExpression,
    Functions\FunctionNode,
};
/**
 * CAST filter.
 */
class CastFilter extends FunctionNode
{
    private PathExpression  $first;
    private string          $second;
    /**
     * @inheritDoc
     *
     * @throws ASTException
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return sprintf(
            'CAST(%s AS %s)',
            $this->first->dispatch($sqlWalker),
            $this->second
        );
    }
    /**
     * @inheritDoc
     *
     * @throws QueryException
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->first = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_AS);
        $parser->match(Lexer::T_IDENTIFIER);
        $this->second = $parser->getLexer()->token['value'];
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
