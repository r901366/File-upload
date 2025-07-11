<?php

namespace WPS\Ai\Application\Services;

use Text_LanguageDetect;

/**
 * Language Detector class.
 */
final class LanguageDetector
{
    /**
     * Test if a given string is in english or not.
     *
     * @param string $str The string to test.
     *
     * @return bool        true if the string is in english, false if not.
     */
    public static function isEnglish($str)
    {
        if (! is_string($str)) {
            return false;
        }

        if (strlen($str) !== mb_strlen($str, 'utf-8')) {
            return false;
        }

        if (0 === mb_strlen($str, 'utf-8')) {
            return false;
        }

        $ld                = new Text_LanguageDetect();
        $possible_language = $ld->detect($str, 3);
        return ( isset($possible_language['english']) && ( (float) $possible_language['english'] > 0 ) );
    }
}
