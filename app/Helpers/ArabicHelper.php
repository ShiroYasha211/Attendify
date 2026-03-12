<?php

namespace App\Helpers;

/**
 * A robust Arabic Shaper to handle UTF-8 Arabic ligatures and joining.
 * This version uses hex codes for mapped characters to avoid encoding issues.
 */
class ArabicHelper
{
    /**
     * Fixes Arabic text by shaping and reversing for RTL engines like DomPDF.
     */
    public static function fixArabic($text, $reverse = true)
    {
        if (empty($text)) return $text;

        // Clean text
        $text = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $text);
        
        // Shape (join)
        $shaped = self::reshape($text);

        // Reverse for RTL
        if ($reverse) {
            return self::reverseRtl($shaped);
        }

        return $shaped;
    }

    private static function reshape($text)
    {
        // Forms: [Isolated, Initial, Medial, Final]
        // Using hex to ensure no encoding corruption
        $map = [
            'ا' => ["\u{0627}", "\u{0627}", "\u{FE8E}", "\u{FE8E}"],
            'ب' => ["\u{0628}", "\u{FE91}", "\u{FE92}", "\u{FE90}"],
            'ت' => ["\u{062A}", "\u{FE97}", "\u{FE98}", "\u{FE96}"],
            'ث' => ["\u{062B}", "\u{FE9B}", "\u{FE9C}", "\u{FE9A}"],
            'ج' => ["\u{062C}", "\u{FE9F}", "\u{FEA0}", "\u{FE9E}"],
            'ح' => ["\u{062D}", "\u{FEA3}", "\u{FEA4}", "\u{FEA2}"],
            'خ' => ["\u{062E}", "\u{FEA7}", "\u{FEA8}", "\u{FEA6}"],
            'د' => ["\u{062F}", "\u{062F}", "\u{FEAA}", "\u{FEAA}"],
            'ذ' => ["\u{0630}", "\u{0630}", "\u{FEAC}", "\u{FEAC}"],
            'ر' => ["\u{0631}", "\u{0631}", "\u{FEAE}", "\u{FEAE}"],
            'ز' => ["\u{0632}", "\u{0632}", "\u{FEB0}", "\u{FEB0}"],
            'س' => ["\u{0633}", "\u{FEB3}", "\u{FEB4}", "\u{FEB2}"],
            'ش' => ["\u{0634}", "\u{FEB7}", "\u{FEB8}", "\u{FEB6}"],
            'ص' => ["\u{0635}", "\u{FEBB}", "\u{FEBC}", "\u{FEBA}"],
            'ض' => ["\u{0636}", "\u{FEBF}", "\u{FEC0}", "\u{FEBE}"],
            'ط' => ["\u{0637}", "\u{FEC3}", "\u{FEC4}", "\u{FEC2}"],
            'ظ' => ["\u{0638}", "\u{FEC7}", "\u{FEC8}", "\u{FEC6}"],
            'ع' => ["\u{0639}", "\u{FECB}", "\u{FECC}", "\u{FECA}"],
            'غ' => ["\u{063A}", "\u{FECF}", "\u{FED0}", "\u{FECE}"],
            'ف' => ["\u{0641}", "\u{FED3}", "\u{FED4}", "\u{FED2}"],
            'ق' => ["\u{0642}", "\u{FED7}", "\u{FED8}", "\u{FED6}"],
            'ك' => ["\u{0643}", "\u{FEDB}", "\u{FEDC}", "\u{FEDA}"],
            'ل' => ["\u{0644}", "\u{FEDF}", "\u{FEE0}", "\u{FEDE}"],
            'م' => ["\u{0645}", "\u{FEE3}", "\u{FEE4}", "\u{FEE2}"],
            'ن' => ["\u{0646}", "\u{FEE7}", "\u{FEE8}", "\u{FEE6}"],
            'ه' => ["\u{0647}", "\u{FEEB}", "\u{FEEC}", "\u{FEEA}"],
            'و' => ["\u{0648}", "\u{0648}", "\u{FEEE}", "\u{FEEE}"],
            'ي' => ["\u{064A}", "\u{FEF3}", "\u{FEF4}", "\u{FEF2}"],
            'آ' => ["\u{0622}", "\u{0622}", "\u{FE82}", "\u{FE82}"],
            'أ' => ["\u{0623}", "\u{0623}", "\u{FE84}", "\u{FE84}"],
            'ؤ' => ["\u{0624}", "\u{0624}", "\u{FE86}", "\u{FE86}"],
            'إ' => ["\u{0625}", "\u{0625}", "\u{FE88}", "\u{FE88}"],
            'ئ' => ["\u{0626}", "\u{FE8B}", "\u{FE8C}", "\u{FE8A}"],
            'ة' => ["\u{0629}", "\u{0629}", "\u{FE94}", "\u{FE94}"],
            'ى' => ["\u{0649}", "\u{0649}", "\u{FEF0}", "\u{FEF0}"],
        ];

        $noLeftConnect = ['ا', 'د', 'ذ', 'ر', 'ز', 'و', 'آ', 'أ', 'إ', 'ؤ', 'ة', 'ى'];

        $chars = self::splitUtf8($text);
        $result = [];
        $count = count($chars);

        for ($i = 0; $i < $count; $i++) {
            $current = $chars[$i];

            // Lam-Alif logic
            if ($current === 'ل' && $i < $count - 1) {
                $next = $chars[$i+1];
                $lamAlif = match($next) {
                    'ا' => ["\u{FEFB}", "\u{FEFB}", "\u{FEFC}", "\u{FEFC}"],
                    'أ' => ["\u{FEF7}", "\u{FEF7}", "\u{FEF8}", "\u{FEF8}"],
                    'إ' => ["\u{FEF9}", "\u{FEF9}", "\u{FEFA}", "\u{FEFA}"],
                    'آ' => ["\u{FEF5}", "\u{FEF5}", "\u{FEF6}", "\u{FEF6}"],
                    default => null
                };

                if ($lamAlif) {
                    $prev = ($i > 0) ? $chars[$i-1] : null;
                    $connectPrev = ($prev && isset($map[$prev]) && !in_array($prev, $noLeftConnect));
                    $result[] = $connectPrev ? $lamAlif[2] : $lamAlif[0];
                    $i++; continue;
                }
            }

            if (!isset($map[$current])) {
                $result[] = $current;
                continue;
            }

            $prev = ($i > 0) ? $chars[$i-1] : null;
            $next = ($i < $count - 1) ? $chars[$i+1] : null;

            $connectPrev = ($prev && isset($map[$prev]) && !in_array($prev, $noLeftConnect));
            $connectNext = ($next && isset($map[$next]));

            if ($connectPrev && $connectNext) $result[] = $map[$current][2];
            elseif ($connectPrev) $result[] = $map[$current][3];
            elseif ($connectNext) $result[] = $map[$current][1];
            else $result[] = $map[$current][0];
        }

        return implode('', $result);
    }

    private static function reverseRtl($text)
    {
        preg_match_all('/([0-9\/\.\:\-]+|[a-zA-Z\s\(\)]+|[ \(\)\#\$\%\!]+|.)/u', $text, $matches);
        return implode('', array_reverse($matches[0]));
    }

    private static function splitUtf8($str) {
        return preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
    }
}
