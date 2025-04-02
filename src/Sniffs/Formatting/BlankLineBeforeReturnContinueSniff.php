<?php

namespace SparkFabrik\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks that there is a blank line before return and continue statements.
 */
class BlankLineBeforeReturnContinueSniff implements Sniff {
  /**
   * A list of tokenizers this sniff supports.
   *
   * @var array
   */
  public array $supportedTokenizers = ['PHP'];

  /**
   * Returns an array of tokens this test wants to listen for.
   */
  public function register(): array {
    return [
      T_RETURN,
      T_CONTINUE,
    ];
  }

  /**
   * Processes this test, when one of its tokens is encountered.
   *
   * @param \PHP_CodeSniffer\Files\File $phpcsFile
   *   The file being scanned.
   * @param int $stackPtr
   *   The position of the current token in the stack.
   */
  public function process(File $phpcsFile, $stackPtr): void {
    $tokens = $phpcsFile->getTokens();
    $current = $tokens[$stackPtr];
    $currentLine = $tokens[$stackPtr]['line'];

    // If we're not inside a scope (function, if, loop, etc.) then ignore.
    if (empty($current['conditions'])) {
      return;
    }

    // Find the previous open curly bracket.
    $prevOpenCurlyBracket = $phpcsFile->findPrevious(
      T_OPEN_CURLY_BRACKET,
      ($stackPtr - 1),
    );

    if ($prevOpenCurlyBracket === FALSE) {
      return;
    }

    // Now let's check if there is a blank line before the return/continue statement.
    $openCurlyBracketLine = $tokens[$prevOpenCurlyBracket]['line'];

    // The return/continue statement is the first statement in the block.
    if (($currentLine - $openCurlyBracketLine) == 1) {
      return;
    }

    // Find the previous semicolon.
    $prevSemicolon = $phpcsFile->findPrevious(
      T_SEMICOLON,
      ($stackPtr - 1),
    );

    if ($prevSemicolon === FALSE) {
      return;
    }

    // Now let's check if there is a blank line before the return/continue statement.
    $openSemicolon = $tokens[$prevSemicolon]['line'];

    if (($currentLine - $openSemicolon) < 2) {
      $type = $tokens[$stackPtr]['content'];

      $error = 'There must be a blank line before %s statement';
      $fix = $phpcsFile->addFixableError($error, $stackPtr, 'MissingBlankLine', [$type]);
      if ($fix === TRUE) {
        $phpcsFile->fixer->addNewlineBefore($stackPtr);
      }
    }
  }

}
