<?php

namespace App\Helpers;

class NumberFormatter
{
    public static function toArabic($number)
    {
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return str_replace(range(0, 9), $arabicNumbers, $number);
    }
}