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
    $currentToken = $tokens[$stackPtr];
    $currentTokenLine = $tokens[$stackPtr]['line'];

    // If we're not inside a scope (function, if, loop, etc.) then ignore.
    if (empty($currentToken['conditions'])) {
      return;
    }

    // Find the previous non-whitespace token.
    $prevToken = $phpcsFile->findPrevious(
      T_WHITESPACE,
      ($stackPtr - 1),
      NULL,
      TRUE,
    );

    if ($prevToken === FALSE) {
      return;
    }

    // If the previous token is a semicolon, there must be a blank line
    // before the return/continue statement.
    if ($tokens[$prevToken]['code'] === T_SEMICOLON) {
      $prevTokenLine = $tokens[$prevToken]['line'];

      if (($currentTokenLine - $prevTokenLine) < 2) {
        $type = $tokens[$stackPtr]['content'];

        $error = 'There must be a blank line before %s statement';
        $fix = $phpcsFile->addFixableError(
          $error,
          $stackPtr,
          'MissingBlankLine',
          [$type]
        );
        if ($fix === TRUE) {
          $phpcsFile->fixer->addNewlineBefore($stackPtr);
        }
      }
    }
  }

}
