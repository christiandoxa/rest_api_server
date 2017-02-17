<?php
/**
 * Part of ci-phpunit-test
 *
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2015 Kenji Suzuki
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

namespace Kenjis\MonkeyPatch\Patcher;

use Kenjis\MonkeyPatch\MonkeyPatchManager;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

abstract class AbstractPatcher {
    public static $replacement;
    protected $node_visitor;

    public function patch($source) {
        $patched = false;
        static::$replacement = [];

        $parser = (new ParserFactory)
            ->create(
                MonkeyPatchManager::getPhpParser(),
                new Lexer(
                    ['usedAttributes' => ['startTokenPos', 'endTokenPos']]
                )
            );
        $traverser = new NodeTraverser;
        $traverser->addVisitor($this->node_visitor);

        $ast_orig = $parser->parse($source);
        $traverser->traverse($ast_orig);

        if (static::$replacement !== []) {
            $patched = true;
            $new_source = static::generateNewSource($source);
        } else {
            $new_source = $source;
        }

        return [
            $new_source,
            $patched,
        ];
    }
}
