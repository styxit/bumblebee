<?php

namespace Styxit\Bumblebee;

use Stringy\StaticStringy as S;
use Stringy\Stringy;

/**
 * Transformer class.
 */
class Bee
{

    /**
     * Let Bumblebee transform a string according to one of the four defined transform methods.
     *
     * @param int    $transformType The transform type to use.
     * @param string $inputTxt      The input text to transform.
     *
     * @return string The transformed intput text.
     */
    public static function transform(int $transformType, string $inputTxt)
    {
        if (!in_array($transformType, [1, 2, 3, 4])) {
            return $inputTxt;
        }

        $methodName = 'type'.$transformType;

        return self::{$methodName}($inputTxt);
    }
    /**
     * Soort 1: Vervang ieder woord van 1 t/m 3 letters, door hetzelfde woord maar dan achterstevoren geschreven
     * waarbij de hoofdletters op dezelfde positie blijven. ‘Wij’ wordt dus ‘Jiw’.
     *
     * Voorbeeld output:
     * “Jiw doen iets goed fo jiw doen teh niet.
     * Jiw besparen niet po onze service, apparatuur ne faciliteiten.”
     *
     * @param $txt
     *
     * @return string
     */
    private static function type1($txt) {
        // Split text into word array
        $wordArray = explode(' ', $txt);

        $outArray = [];

        // Loop input words.
        foreach($wordArray as $word) {
            if (mb_strlen($word) <= 3) {
                $word = self::reverseWord($word, true);
            }

            $outArray[] = $word;
        }

        return implode(' ', $outArray);
    }


    /**
     * Soort 2: Verwissel ieder derde woord om met zijn voorganger en schrijf deze achterstevoren,
     * waarbij eventuele hoofdletters op dezelfde positie blijven.
     *
     * Voorbeeld output:
     * “Wij stei doen goed jiw of doen tein het.
     * Wij tein besparen op ecivres onze, apparatuur netietilicaf en.”
     *
     * @param $txt
     *
     * @return string
     */
    private static function type2($txt) {
        // Split text into word array
        $wordArray = explode(' ', $txt);

        $outArray = [];

        // Loop input words.
        foreach($wordArray as $wordPosition => $word) {

            if (fmod(($wordPosition+1), 3) == 0) {
                $word = self::reverseWord($word, true);
                $prevWord = $outArray[$wordPosition-1];

                // Swith word from position.
                $outArray[$wordPosition-1] = $word;

                $word = $prevWord;
            }

            $outArray[] = $word;
        }

        ksort($outArray);
        return implode(' ', $outArray);
    }


    /**
     * Soort 3:
     * Onderzoek heeft uitgewezen dat de volgorde van letters in een woord niet heel erg belangrijk
     * is voor de leesbaarheid. Zolang de eerste en de laatste letter van een woord op hun plaats staan,
     * maakt de volgorde van de overige letters weinig uit. Zorg ervoor dat de het script instaat is om
     * input zoals bovenstaande tekst om te zetten volgens dit principe. De volgorde van de letters tussen
     * de eerste en laatste letter mag random zijn.
     *
     * Voorbeeld output:
     * “Wij deon ites geod of wij deon het neit. Wij beaerspn neit op ozne secvrie, auurptaapr en fieteliaictn.”
     *
     *
     * @param $txt
     *
     * @return string
     */
    private static function type3($txt) {
        // Split text into word array
        $wordArray = explode(' ', $txt);
        // Collect output words.
        $outArray = [];

        // Loop input words.
        foreach($wordArray as $wordPosition => $word) {
            $outArray[] = self::shuffleWord($word);
        }

        return implode(' ', $outArray);
    }


    /**
     * Soort 4:
     * In deze tekst willen we karakters vervangen op basis van zijn voorganger.
     * Ieder karakter heeft een waarde in de ASCII tabel. Is de waarde van zijn voorganger groter,
     * vervang dan het karakter met het volgende karakter volgens de ASCII tabel, is deze kleiner, vervang dan het
     * karakter met het vorige karakter volgens de ASCII tabel. Is de waarde identiek, doe dan niks.
     * Het eerste karakter heeft geen voorganger dus, ook deze kun je laten zoals het is.
     *
     * @param $txt The input string.
     *
     * @return string The converted string.
     */
    private static function type4($txt) {
        // Split text into word array
        $txt = Stringy::create($txt, 'UTF-8');

        $outStr = '';

        $prevCharAscii = null;

        // Loop characters.
        foreach($txt->chars() as $characterPosition => $character) {
            $characterAscii = ord($character);

            if($prevCharAscii){
                if ($prevCharAscii > $characterAscii) {
                    $character = chr($characterAscii + 1);
                } elseif ($prevCharAscii < $characterAscii) {
                    $character = chr($characterAscii - 1);
                }
            }

            $prevCharAscii = $characterAscii;

            $outStr .= $character;
        }

        return $outStr;
    }


    /**
     * Shuffle the characters of a word, except the first and last character.
     *
     * @param $word The in put string to shuffle.
     *
     * @return string The suffled word.
     */
    private static function shuffleWord($word) {
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
    private static function reverseWord($word, $keepCasePosition = false) {
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
