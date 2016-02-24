<?php

namespace Bumblebee\Utilities;

use Stringy\StaticStringy as S;
use Stringy\Stringy;

/**
 * Utility to modify words.
 */
class Word
{
    /**
     * Shuffle the characters of a word, except the first and last character.
     *
     * @param $word The in put string to shuffle.
     *
     * @return string The suffled word.
     */
    public static function shuffle($word) {
        if (mb_strlen($word) <= 3) {
            return $word;
        }

        // Store the first and last character.
        $firstChar = mb_substr($word, 0, 1);
        $lastChar = mb_substr($word, -1);

        // Remove first and last character from the string.
        $otherCharacters = mb_substr(mb_substr($word, 1) , 0, -1);

        // Shuffle the characters.
        $shuffled = (string) S::shuffle($otherCharacters, 'UTF-8');

        return $firstChar . $shuffled . $lastChar;
    }


    /**
     * Reverse a word.
     *
     * @param      $word             Input text.
     * @param bool $keepCasePosition When true, the position of the upper and lower cases is maintained
     *                               in the reversed string.
     *
     * @return string The reverse verion of $word.
     */
    public static function reverse($word, $keepCasePosition = false) {
        if (is_numeric($word)) {
            return $word;
        }

        // Check the last character in the string for punctuation.
        $lastCharacter = substr($word, -1);
        if (ctype_punct($lastCharacter)) {
            // Reverse the word but do not include the last character.
            $revWord = S::reverse(mb_substr($word, 0, -1));
        } else {
            // Reverse the word.
            $revWord = S::reverse($word);
        }

        // Check if case position must be kept.
        if ($keepCasePosition) {
            $reverseWordString = '';
            // Upper- and lowercase character position must remain.
            // Match character case in the original string with that of the reversed character at that position.
            foreach (S::chars($word) as $characterPosition => $character) {
                $c = Stringy::create($character, 'UTF-8');
                $rc = $revWord->at($characterPosition);

                if (($c->hasUpperCase() && !$rc->hasUpperCase()) || ($c->hasLowerCase() && !$rc->hasLowerCase())) {
                    // The case of the original character and the replaced character do not match.
                    $rc = $rc->swapCase();
                }

                $reverseWordString .= (string)$rc;
            }
        } else {
            $reverseWordString = (string)$revWord;
        }

        // Add last punctuation character if needed.
        if (strlen($reverseWordString) != strlen($word)) {
            $reverseWordString .= $lastCharacter;
        }

        return $reverseWordString;
    }

}
