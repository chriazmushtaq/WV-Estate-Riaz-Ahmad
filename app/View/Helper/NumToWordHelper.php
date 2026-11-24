<?php
App::uses('AppHelper', 'View/Helper');

class NumToWordHelper extends AppHelper
{
    protected $_ones = array(
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen'
    );

    protected $_tens = array(
        2 => 'Twenty',
        3 => 'Thirty',
        4 => 'Forty',
        5 => 'Fifty',
        6 => 'Sixty',
        7 => 'Seventy',
        8 => 'Eighty',
        9 => 'Ninety'
    );

    protected $_urduUnderHundred = array(
        0 => '',
        1 => 'ایک',
        2 => 'دو',
        3 => 'تین',
        4 => 'چار',
        5 => 'پانچ',
        6 => 'چھ',
        7 => 'سات',
        8 => 'آٹھ',
        9 => 'نو',
        10 => 'دس',
        11 => 'گیارہ',
        12 => 'بارہ',
        13 => 'تیرہ',
        14 => 'چودہ',
        15 => 'پندرہ',
        16 => 'سولہ',
        17 => 'سترہ',
        18 => 'اٹھارہ',
        19 => 'انیس',
        20 => 'بیس',
        21 => 'اکیس',
        22 => 'بائیس',
        23 => 'تئیس',
        24 => 'چوبیس',
        25 => 'پچیس',
        26 => 'چھبیس',
        27 => 'ستائیس',
        28 => 'اٹھائیس',
        29 => 'انتیس',
        30 => 'تیس',
        31 => 'اکتیس',
        32 => 'بتیس',
        33 => 'تینتیس',
        34 => 'چونتیس',
        35 => 'پینتیس',
        36 => 'چھتیس',
        37 => 'سینتیس',
        38 => 'اڑتیس',
        39 => 'انتالیس',
        40 => 'چالیس',
        41 => 'اکتالیس',
        42 => 'بیالیس',
        43 => 'تینتالیس',
        44 => 'چوالیس',
        45 => 'پینتالیس',
        46 => 'چھیالیس',
        47 => 'سینتالیس',
        48 => 'اڑتالیس',
        49 => 'انچاس',
        50 => 'پچاس',
        51 => 'اکیاون',
        52 => 'باون',
        53 => 'ترپن',
        54 => 'چون',
        55 => 'پچپن',
        56 => 'چھپن',
        57 => 'ستاون',
        58 => 'اٹھاون',
        59 => 'انسٹھ',
        60 => 'ساٹھ',
        61 => 'اکسٹھ',
        62 => 'باسٹھ',
        63 => 'تریسٹھ',
        64 => 'چونسٹھ',
        65 => 'پینسٹھ',
        66 => 'چھاسٹھ',
        67 => 'سڑسٹھ',
        68 => 'اڑسٹھ',
        69 => 'انہتر',
        70 => 'ستر',
        71 => 'اکہتر',
        72 => 'بہتر',
        73 => 'تہتر',
        74 => 'چوہتر',
        75 => 'پچھتر',
        76 => 'چھہتر',
        77 => 'ستتر',
        78 => 'اٹھہتر',
        79 => 'اناسی',
        80 => 'اسی',
        81 => 'اکیاسی',
        82 => 'بیاسی',
        83 => 'تراسی',
        84 => 'چوراسی',
        85 => 'پچاسی',
        86 => 'چھیاسی',
        87 => 'ستاسی',
        88 => 'اٹھاسی',
        89 => 'نواسی',
        90 => 'نوے',
        91 => 'اکیانوے',
        92 => 'بانوے',
        93 => 'ترانوے',
        94 => 'چورانوے',
        95 => 'پچانوے',
        96 => 'چھیانوے',
        97 => 'ستانوے',
        98 => 'اٹھانوے',
        99 => 'ننانوے'
    );

    public function NumberToWord($amount, $currencyWord = 'Rupees')
    {
        $amount = trim(str_replace(',', '', (string)$amount));
        if ($amount === '' || !is_numeric($amount)) {
            return '';
        }

        $isNegative = ((float)$amount < 0);
        $amount = round(abs((float)$amount), 2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount - $whole) * 100);
        if ($fraction === 100) {
            $whole++;
            $fraction = 0;
        }

        $words = $this->_integerToWords($whole);
        if (strlen(trim((string)$currencyWord)) > 0) {
            $words .= ' ' . trim((string)$currencyWord);
        }
        if ($fraction > 0) {
            $words .= ' And ' . $this->_integerToWords($fraction) . ' Paisa';
        }
        if ($isNegative) {
            $words = 'Minus ' . $words;
        }

        return trim($words) . ' Only';
    }

    public function NumberToUrduWord($amount, $currencyWord = 'روپے')
    {
        $amount = trim(str_replace(',', '', (string)$amount));
        if ($amount === '' || !is_numeric($amount)) {
            return '';
        }

        $isNegative = ((float)$amount < 0);
        $amount = round(abs((float)$amount), 2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount - $whole) * 100);
        if ($fraction === 100) {
            $whole++;
            $fraction = 0;
        }

        $words = $this->_integerToUrduWords($whole);
        if (strlen(trim((string)$currencyWord)) > 0) {
            $words .= ' ' . trim((string)$currencyWord);
        }
        if ($fraction > 0) {
            $words .= ' اور ' . $this->_integerToUrduWords($fraction) . ' پیسے';
        }
        if ($isNegative) {
            $words = 'منفی ' . $words;
        }

        return trim($words) . ' صرف';
    }

    protected function _integerToWords($number)
    {
        $number = (int)$number;
        if ($number === 0) {
            return 'Zero';
        }

        $scaleNames = array('', 'Thousand', 'Lakh', 'Crore', 'Arab', 'Kharab');
        $groups = array($number % 1000);
        $number = (int)floor($number / 1000);
        while ($number > 0) {
            $groups[] = $number % 100;
            $number = (int)floor($number / 100);
        }

        $parts = array();
        for ($i = count($groups) - 1; $i >= 0; $i--) {
            $groupValue = (int)$groups[$i];
            if ($groupValue === 0) {
                continue;
            }

            $groupWords = ($i === 0) ? $this->_underThousandToWords($groupValue) : $this->_underHundredToWords($groupValue);
            if (!empty($scaleNames[$i])) {
                $groupWords .= ' ' . $scaleNames[$i];
            }
            $parts[] = $groupWords;
        }

        return implode(' ', $parts);
    }

    protected function _underThousandToWords($number)
    {
        $number = (int)$number;
        $parts = array();

        if ($number >= 100) {
            $parts[] = $this->_ones[(int)floor($number / 100)] . ' Hundred';
            $number = $number % 100;
        }
        if ($number > 0) {
            $parts[] = $this->_underHundredToWords($number);
        }

        return implode(' ', $parts);
    }

    protected function _underHundredToWords($number)
    {
        $number = (int)$number;
        if ($number < 20) {
            return $this->_ones[$number];
        }

        $ten = (int)floor($number / 10);
        $unit = $number % 10;
        $words = $this->_tens[$ten];
        if ($unit > 0) {
            $words .= ' ' . $this->_ones[$unit];
        }

        return $words;
    }

    protected function _integerToUrduWords($number)
    {
        $number = (int)$number;
        if ($number === 0) {
            return 'صفر';
        }

        $scaleNames = array('', 'ہزار', 'لاکھ', 'کروڑ', 'ارب', 'کھرب');
        $groups = array($number % 1000);
        $number = (int)floor($number / 1000);
        while ($number > 0) {
            $groups[] = $number % 100;
            $number = (int)floor($number / 100);
        }

        $parts = array();
        for ($i = count($groups) - 1; $i >= 0; $i--) {
            $groupValue = (int)$groups[$i];
            if ($groupValue === 0) {
                continue;
            }

            $groupWords = ($i === 0) ? $this->_underThousandToUrduWords($groupValue) : $this->_underHundredToUrduWords($groupValue);
            if (!empty($scaleNames[$i])) {
                $groupWords .= ' ' . $scaleNames[$i];
            }
            $parts[] = $groupWords;
        }

        return implode(' ', $parts);
    }

    protected function _underThousandToUrduWords($number)
    {
        $number = (int)$number;
        $parts = array();

        if ($number >= 100) {
            $parts[] = $this->_urduUnderHundred[(int)floor($number / 100)] . ' سو';
            $number = $number % 100;
        }
        if ($number > 0) {
            $parts[] = $this->_underHundredToUrduWords($number);
        }

        return implode(' ', $parts);
    }

    protected function _underHundredToUrduWords($number)
    {
        $number = (int)$number;
        return isset($this->_urduUnderHundred[$number]) ? $this->_urduUnderHundred[$number] : '';
    }
}
