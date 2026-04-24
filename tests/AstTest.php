<?php

namespace DevTheorem\HandlebarsParser\Test;

use DevTheorem\HandlebarsParser\Ast\BlockStatement;
use DevTheorem\HandlebarsParser\Ast\CommentStatement;
use DevTheorem\HandlebarsParser\Ast\ContentStatement;
use DevTheorem\HandlebarsParser\Ast\MustacheStatement;
use DevTheorem\HandlebarsParser\Ast\PartialStatement;
use DevTheorem\HandlebarsParser\Ast\PathExpression;
use DevTheorem\HandlebarsParser\ParserFactory;
use PHPUnit\Framework\TestCase;

class AstTest extends TestCase
{
    // whitespace control > parse

    public function testWhitespaceControlParseMustache(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('  {{~comment~}} ');

        assert($ast->body[0] instanceof ContentStatement);
        assert($ast->body[2] instanceof ContentStatement);
        $this->assertSame('', $ast->body[0]->value);
        $this->assertSame('', $ast->body[2]->value);
    }

    public function testWhitespaceControlParseBlockStatements(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse(" {{# comment~}} \nfoo\n {{~/comment}}");

        assert($ast->body[0] instanceof ContentStatement);
        assert($ast->body[1] instanceof BlockStatement);
        assert($ast->body[1]->program !== null);
        assert($ast->body[1]->program->body[0] instanceof ContentStatement);
        $this->assertSame('', $ast->body[0]->value);
        $this->assertSame('foo', $ast->body[1]->program->body[0]->value);
    }

    public function testWhitespaceControlParseTildeOnElseIfPreservesLaterBranchWhitespace(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('{{#if a}}A{{else if b}}B {{~else if c}}C {{/if}}');

        assert($ast->body[0] instanceof BlockStatement);
        $block = $ast->body[0];
        assert($block->inverse !== null && $block->inverse->body[0] instanceof BlockStatement);
        $bBlock = $block->inverse->body[0];
        assert($bBlock->inverse !== null && $bBlock->inverse->body[0] instanceof BlockStatement);
        $cBlock = $bBlock->inverse->body[0];

        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($bBlock->program !== null && $bBlock->program->body[0] instanceof ContentStatement);
        assert($cBlock->program !== null && $cBlock->program->body[0] instanceof ContentStatement);

        $this->assertSame('A', $block->program->body[0]->value);
        $this->assertSame('B', $bBlock->program->body[0]->value); // tilde strips trailing space from b
        $this->assertSame('C ', $cBlock->program->body[0]->value); // no tilde on {{/if}}, space preserved
    }

    // whitespace control > parseWithoutProcessing

    public function testWhitespaceControlParseWithoutProcessingMustache(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing('  {{~comment~}} ');

        assert($ast->body[0] instanceof ContentStatement);
        assert($ast->body[2] instanceof ContentStatement);
        $this->assertSame('  ', $ast->body[0]->value);
        $this->assertSame(' ', $ast->body[2]->value);
    }

    public function testWhitespaceControlParseWithoutProcessingBlockStatements(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing(" {{# comment~}} \nfoo\n {{~/comment}}");

        assert($ast->body[0] instanceof ContentStatement);
        assert($ast->body[1] instanceof BlockStatement && $ast->body[1]->program !== null);
        assert($ast->body[1]->program->body[0] instanceof ContentStatement);
        $this->assertSame(' ', $ast->body[0]->value);
        $this->assertSame(" \nfoo\n ", $ast->body[1]->program->body[0]->value);
    }

    // node details > paths

    public function testNodeDetailsPathsThis(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('{{this}}');

        assert($ast->body[0] instanceof MustacheStatement);
        assert($ast->body[0]->path instanceof PathExpression);
        $path = $ast->body[0]->path;

        $this->assertSame('this', $path->original);
        // In JS path.head is undefined; in PHP head is '' when only 'this' is present
        $this->assertSame('', $path->head);
        $this->assertCount(0, $path->tail);
        $this->assertCount(0, $path->parts);
    }

    public function testNodeDetailsPathsThisBar(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('{{this.bar}}');

        assert($ast->body[0] instanceof MustacheStatement);
        assert($ast->body[0]->path instanceof PathExpression);
        $path = $ast->body[0]->path;

        $this->assertSame('this.bar', $path->original);
        $this->assertSame('bar', $path->head);
        $this->assertCount(0, $path->tail);
        $this->assertCount(1, $path->parts);
        $this->assertSame('bar', $path->parts[0]);
    }

    public function testNodeDetailsPathsThisHashBar(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('{{this.#bar}}');

        assert($ast->body[0] instanceof MustacheStatement);
        assert($ast->body[0]->path instanceof PathExpression);
        $path = $ast->body[0]->path;

        $this->assertSame('this.#bar', $path->original);
        $this->assertSame('#bar', $path->head);
        $this->assertCount(0, $path->tail);
        $this->assertCount(1, $path->parts);
        $this->assertSame('#bar', $path->parts[0]);
    }

    public function testNodeDetailsPathsFooBar(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('{{foo.bar}}');

        assert($ast->body[0] instanceof MustacheStatement);
        assert($ast->body[0]->path instanceof PathExpression);
        $path = $ast->body[0]->path;

        $this->assertSame('foo.bar', $path->original);
        $this->assertSame('foo', $path->head);
        $this->assertCount(1, $path->tail);
        $this->assertSame('bar', $path->tail[0]);
        $this->assertCount(2, $path->parts);
        $this->assertSame('foo', $path->parts[0]);
        $this->assertSame('bar', $path->parts[1]);
    }

    public function testNodeDetailsPathsFooHashBar(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('{{foo.#bar}}');

        assert($ast->body[0] instanceof MustacheStatement);
        assert($ast->body[0]->path instanceof PathExpression);
        $path = $ast->body[0]->path;

        $this->assertSame('foo.#bar', $path->original);
        $this->assertSame('foo', $path->head);
        $this->assertCount(1, $path->tail);
        $this->assertSame('#bar', $path->tail[0]);
        $this->assertCount(2, $path->parts);
        $this->assertSame('foo', $path->parts[0]);
        $this->assertSame('#bar', $path->parts[1]);
    }

    // standalone flags > mustache

    public function testStandaloneFlagsMustacheNotStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('  {{comment}} ');

        assert($ast->body[0] instanceof ContentStatement);
        assert($ast->body[2] instanceof ContentStatement);
        $this->assertNotEmpty($ast->body[0]->value);
        $this->assertNotEmpty($ast->body[2]->value);
    }

    // standalone flags > blocks - parseWithoutProcessing

    public function testStandaloneFlagsBlocksParseWithoutProcessingBlockMustaches(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing(" {{# comment}} \nfoo\n {{else}} \n  bar \n  {{/comment}} ");

        assert($ast->body[1] instanceof BlockStatement);
        $block = $ast->body[1];
        assert($ast->body[0] instanceof ContentStatement);
        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($block->inverse !== null && $block->inverse->body[0] instanceof ContentStatement);
        assert($ast->body[2] instanceof ContentStatement);

        $this->assertSame(' ', $ast->body[0]->value);
        $this->assertSame(" \nfoo\n ", $block->program->body[0]->value);
        $this->assertSame(" \n  bar \n  ", $block->inverse->body[0]->value);
        $this->assertSame(' ', $ast->body[2]->value);
    }

    public function testStandaloneFlagsBlocksParseWithoutProcessingInitialBlockMustaches(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing("{{# comment}} \nfoo\n {{/comment}}");

        assert($ast->body[0] instanceof BlockStatement);
        assert($ast->body[0]->program !== null && $ast->body[0]->program->body[0] instanceof ContentStatement);
        $this->assertSame(" \nfoo\n ", $ast->body[0]->program->body[0]->value);
    }

    public function testStandaloneFlagsBlocksParseWithoutProcessingMustachesWithChildren(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing("{{# comment}} \n{{foo}}\n {{/comment}}");

        assert($ast->body[0] instanceof BlockStatement);
        $block = $ast->body[0];
        assert($block->program !== null);
        assert($block->program->body[0] instanceof ContentStatement);
        assert($block->program->body[1] instanceof MustacheStatement);
        assert($block->program->body[1]->path instanceof PathExpression);
        assert($block->program->body[2] instanceof ContentStatement);

        $this->assertSame(" \n", $block->program->body[0]->value);
        $this->assertSame('foo', $block->program->body[1]->path->original);
        $this->assertSame("\n ", $block->program->body[2]->value);
    }

    public function testStandaloneFlagsBlocksParseWithoutProcessingNestedBlockMustaches(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing("{{#foo}} \n{{# comment}} \nfoo\n {{else}} \n  bar \n  {{/comment}} \n{{/foo}}");

        assert($ast->body[0] instanceof BlockStatement && $ast->body[0]->program !== null);
        $body = $ast->body[0]->program->body;
        assert($body[0] instanceof ContentStatement);
        assert($body[1] instanceof BlockStatement);
        $block = $body[1];
        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($block->inverse !== null && $block->inverse->body[0] instanceof ContentStatement);

        $this->assertSame(" \n", $body[0]->value);
        $this->assertSame(" \nfoo\n ", $block->program->body[0]->value);
        $this->assertSame(" \n  bar \n  ", $block->inverse->body[0]->value);
    }

    public function testStandaloneFlagsBlocksParseWithoutProcessingColumnZeroBlockMustaches(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing("test\n{{# comment}} \nfoo\n {{else}} \n  bar \n  {{/comment}} ");

        assert($ast->body[1] instanceof BlockStatement);
        $block = $ast->body[1];
        assert($ast->body[0] instanceof ContentStatement);
        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($block->inverse !== null && $block->inverse->body[0] instanceof ContentStatement);
        assert($ast->body[2] instanceof ContentStatement);

        $this->assertFalse($ast->body[0]->leftStripped); // omit undefined
        $this->assertSame(" \nfoo\n ", $block->program->body[0]->value);
        $this->assertSame(" \n  bar \n  ", $block->inverse->body[0]->value);
        $this->assertSame(' ', $ast->body[2]->value);
    }

    // standalone flags > blocks

    public function testStandaloneFlagsBlocksMarksBlockMustachesAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse(" {{# comment}} \nfoo\n {{else}} \n  bar \n  {{/comment}} ");

        assert($ast->body[1] instanceof BlockStatement);
        $block = $ast->body[1];
        assert($ast->body[0] instanceof ContentStatement);
        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($block->inverse !== null && $block->inverse->body[0] instanceof ContentStatement);
        assert($ast->body[2] instanceof ContentStatement);

        $this->assertSame('', $ast->body[0]->value);
        $this->assertSame("foo\n", $block->program->body[0]->value);
        $this->assertSame("  bar \n", $block->inverse->body[0]->value);
        $this->assertSame('', $ast->body[2]->value);
    }

    public function testStandaloneFlagsBlocksMarksInitialBlockMustachesAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse("{{# comment}} \nfoo\n {{/comment}}");

        assert($ast->body[0] instanceof BlockStatement);
        assert($ast->body[0]->program !== null && $ast->body[0]->program->body[0] instanceof ContentStatement);
        $this->assertSame("foo\n", $ast->body[0]->program->body[0]->value);
    }

    public function testStandaloneFlagsBlocksMarksMustachesWithChildrenAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse("{{# comment}} \n{{foo}}\n {{/comment}}");

        assert($ast->body[0] instanceof BlockStatement);
        $block = $ast->body[0];
        assert($block->program !== null);
        assert($block->program->body[0] instanceof ContentStatement);
        assert($block->program->body[1] instanceof MustacheStatement);
        assert($block->program->body[1]->path instanceof PathExpression);
        assert($block->program->body[2] instanceof ContentStatement);

        $this->assertSame('', $block->program->body[0]->value);
        $this->assertSame('foo', $block->program->body[1]->path->original);
        $this->assertSame("\n", $block->program->body[2]->value);
    }

    public function testStandaloneFlagsBlocksMarksNestedBlockMustachesAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse("{{#foo}} \n{{# comment}} \nfoo\n {{else}} \n  bar \n  {{/comment}} \n{{/foo}}");

        assert($ast->body[0] instanceof BlockStatement && $ast->body[0]->program !== null);
        $body = $ast->body[0]->program->body;
        assert($body[0] instanceof ContentStatement);
        assert($body[1] instanceof BlockStatement);
        $block = $body[1];
        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($block->inverse !== null && $block->inverse->body[0] instanceof ContentStatement);

        $this->assertSame('', $body[0]->value);
        $this->assertSame("foo\n", $block->program->body[0]->value);
        $this->assertSame("  bar \n", $block->inverse->body[0]->value);
        $this->assertSame('', $body[0]->value);
    }

    public function testStandaloneFlagsBlocksDoesNotMarkNestedBlockMustachesAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse("{{#foo}} {{# comment}} \nfoo\n {{else}} \n  bar \n  {{/comment}} {{/foo}}");

        assert($ast->body[0] instanceof BlockStatement && $ast->body[0]->program !== null);
        $body = $ast->body[0]->program->body;
        assert($body[0] instanceof ContentStatement);
        assert($body[1] instanceof BlockStatement);
        $block = $body[1];
        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($block->inverse !== null && $block->inverse->body[0] instanceof ContentStatement);

        $this->assertFalse($body[0]->leftStripped); // omit undefined in JS
        $this->assertSame(" \nfoo\n", $block->program->body[0]->value);
        $this->assertSame("  bar \n  ", $block->inverse->body[0]->value);
    }

    public function testStandaloneFlagsBlocksDoesNotMarkNestedInitialBlockMustachesAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse("{{#foo}}{{# comment}} \nfoo\n {{else}} \n  bar \n  {{/comment}}{{/foo}}");

        assert($ast->body[0] instanceof BlockStatement && $ast->body[0]->program !== null);
        $body = $ast->body[0]->program->body;
        assert($body[0] instanceof BlockStatement);
        $block = $body[0];
        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($block->inverse !== null && $block->inverse->body[0] instanceof ContentStatement);

        $this->assertSame(" \nfoo\n", $block->program->body[0]->value);
        $this->assertSame("  bar \n  ", $block->inverse->body[0]->value);
        // body[0] is a BlockStatement in JS; omit === undefined has no PHP equivalent
    }

    public function testStandaloneFlagsBlocksMarksColumnZeroBlockMustachesAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse("test\n{{# comment}} \nfoo\n {{else}} \n  bar \n  {{/comment}} ");

        assert($ast->body[1] instanceof BlockStatement);
        $block = $ast->body[1];
        assert($ast->body[0] instanceof ContentStatement);
        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($block->inverse !== null && $block->inverse->body[0] instanceof ContentStatement);
        assert($ast->body[2] instanceof ContentStatement);

        $this->assertFalse($ast->body[0]->leftStripped); // omit undefined in JS
        $this->assertSame("foo\n", $block->program->body[0]->value);
        $this->assertSame("  bar \n", $block->inverse->body[0]->value);
        $this->assertSame('', $ast->body[2]->value);
    }

    public function testStandaloneFlagsBlocksStripsCloseTagIndentFromAllChainedElseIfBranches(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse("  {{#if a}}\n    foo\n  {{else if b}}\n    bar\n  {{else if c}}\n    baz\n  {{/if}}");

        assert($ast->body[1] instanceof BlockStatement);
        $block = $ast->body[1];
        assert($ast->body[0] instanceof ContentStatement);
        assert($block->program !== null && $block->program->body[0] instanceof ContentStatement);
        assert($block->inverse !== null && $block->inverse->body[0] instanceof BlockStatement);
        $bBlock = $block->inverse->body[0];
        assert($bBlock->program !== null && $bBlock->program->body[0] instanceof ContentStatement);
        assert($bBlock->inverse !== null && $bBlock->inverse->body[0] instanceof BlockStatement);
        $cBlock = $bBlock->inverse->body[0];
        assert($cBlock->program !== null && $cBlock->program->body[0] instanceof ContentStatement);

        $this->assertSame('', $ast->body[0]->value);
        $this->assertSame("    foo\n", $block->program->body[0]->value);
        $this->assertSame("    bar\n", $bBlock->program->body[0]->value);
        $this->assertSame("    baz\n", $cBlock->program->body[0]->value); // indent before {{/if}} stripped
    }

    // standalone flags > partials - parseWithoutProcessing

    public function testStandaloneFlagsPartialsParseWithoutProcessingSimplePartial(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing('{{> partial }} ');

        assert($ast->body[1] instanceof ContentStatement);
        $this->assertSame(' ', $ast->body[1]->value);
    }

    public function testStandaloneFlagsPartialsParseWithoutProcessingIndentedPartial(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing('  {{> partial }} ');

        assert($ast->body[0] instanceof ContentStatement);
        assert($ast->body[1] instanceof PartialStatement);
        assert($ast->body[2] instanceof ContentStatement);
        $this->assertSame('  ', $ast->body[0]->value);
        $this->assertSame('', $ast->body[1]->indent);
        $this->assertSame(' ', $ast->body[2]->value);
    }

    // standalone flags > partials

    public function testStandaloneFlagsPartialsMarksPartialAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('{{> partial }} ');

        assert($ast->body[1] instanceof ContentStatement);
        $this->assertSame('', $ast->body[1]->value);
    }

    public function testStandaloneFlagsPartialsMarksIndentedPartialAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('  {{> partial }} ');

        assert($ast->body[0] instanceof ContentStatement);
        assert($ast->body[1] instanceof PartialStatement);
        assert($ast->body[2] instanceof ContentStatement);
        $this->assertSame('', $ast->body[0]->value);
        $this->assertSame('  ', $ast->body[1]->indent);
        $this->assertSame('', $ast->body[2]->value);
    }

    public function testStandaloneFlagsPartialsMarksThoseAroundContentAsNotStandalone(): void
    {
        $parser = (new ParserFactory())->create();

        $ast = $parser->parse('a{{> partial }}');
        assert($ast->body[0] instanceof ContentStatement);
        $this->assertFalse($ast->body[0]->leftStripped); // omit undefined in JS

        $ast = $parser->parse('{{> partial }}a');
        assert($ast->body[1] instanceof ContentStatement);
        $this->assertFalse($ast->body[1]->leftStripped); // omit undefined in JS
    }

    // standalone flags > comments - parseWithoutProcessing

    public function testStandaloneFlagsCommentsParseWithoutProcessingSimpleComment(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing('{{! comment }} ');

        assert($ast->body[1] instanceof ContentStatement);
        $this->assertSame(' ', $ast->body[1]->value);
    }

    public function testStandaloneFlagsCommentsParseWithoutProcessingIndentedComment(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parseWithoutProcessing('  {{! comment }} ');

        assert($ast->body[0] instanceof ContentStatement);
        assert($ast->body[1] instanceof CommentStatement);
        assert($ast->body[2] instanceof ContentStatement);
        $this->assertSame('  ', $ast->body[0]->value);
        $this->assertSame(' ', $ast->body[2]->value);
    }

    // standalone flags > comments

    public function testStandaloneFlagsCommentsMarksCommentAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('{{! comment }} ');

        assert($ast->body[1] instanceof ContentStatement);
        $this->assertSame('', $ast->body[1]->value);
    }

    public function testStandaloneFlagsCommentsMarksIndentedCommentAsStandalone(): void
    {
        $parser = (new ParserFactory())->create();
        $ast = $parser->parse('  {{! comment }} ');

        assert($ast->body[0] instanceof ContentStatement);
        assert($ast->body[2] instanceof ContentStatement);
        $this->assertSame('', $ast->body[0]->value);
        $this->assertSame('', $ast->body[2]->value);
    }

    public function testStandaloneFlagsCommentsMarksThoseAroundContentAsNotStandalone(): void
    {
        $parser = (new ParserFactory())->create();

        $ast = $parser->parse('a{{! comment }}');
        assert($ast->body[0] instanceof ContentStatement);
        $this->assertFalse($ast->body[0]->leftStripped); // omit undefined in JS

        $ast = $parser->parse('{{! comment }}a');
        assert($ast->body[1] instanceof ContentStatement);
        $this->assertFalse($ast->body[1]->leftStripped); // omit undefined in JS
    }
}
