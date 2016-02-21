<?php

namespace Styxit\Bumblebee;

/**
 * Transformer class.
 */
class Bee
{
    public static function welcome()
    {
        return 'Hi, welcome.';
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
    public static function type1($txt) {

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
    public static function type2($txt) {
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


    private static function reverseWord($word, $keepCasePosition = false) {
        setLocale(LC_ALL, 'NL_nl.UTF-8');

        // Check the last character in the string for punctuation.
        $lastCharacter = substr($word, -1);
        if (ctype_punct($lastCharacter)) {
            // Reverse the word but do not include the last character.
            $revWord = self::mb_strrev(mb_substr($word, 0, -1));
        } else {
            // Reverse the word.
            $revWord = self::mb_strrev($word);
        }

        // Check if case position must be kept.
        if (!$keepCasePosition) {
            // Upper- and lowercase character position must remain.
            // Match character case in the original string with that of the reversed character at that position.

            foreach (self::mb_str_split($word) as $characterPosition => $character) {
                if (ctype_upper($character) && !ctype_upper($revWord[$characterPosition])) {
                    //$revWord[$characterPosition] = mb_convert_case($revWord[$characterPosition], MB_CASE_UPPER, "UTF-8");
                    $revWord[$characterPosition] = mb_strtoupper($revWord[$characterPosition], "UTF-8");
                } elseif (ctype_lower($character) && !ctype_lower($revWord[$characterPosition])) {
                    //$revWord[$characterPosition] = mb_convert_case($revWord[$characterPosition], MB_CASE_LOWER, "UTF-8");
                    $revWord[$characterPosition] = mb_strtolower($revWord[$characterPosition], "UTF-8");
                }
                echo $word;
            }
        }

        // Add last punctuation character if needed.
        if (strlen($revWord) != strlen($word)) {
            $revWord .= $lastCharacter;
        }

        return $revWord;
    }


    static function mb_str_split( $string ) {
        # Split at all position not after the start: ^
        # and not before the end: $
        return preg_split('/(?<!^)(?!$)/u', $string );
    }

    static function mb_strrev($string) {
        $encoding = mb_detect_encoding($string);


        $length   = mb_strlen($string, $encoding);
        $reversed = '';
        while ($length-- > 0) {
            $reversed .= mb_substr($string, $length, 1, $encoding);
        }

        return $reversed;
    }

}
