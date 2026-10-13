<?php
/**
 * توابع کمکی تاریخ شمسی (با استفاده از jdf)
 */

if (!function_exists('persianDate')) {
    function persianDate(string $gregorianDate): string
    {
        if (empty($gregorianDate) || $gregorianDate === '0000-00-00' || $gregorianDate === '0000-00-00 00:00:00') {
            return '—';
        }
        // جدا کردن بخش تاریخ از ساعت (در صورت وجود)
        $datePart = explode(' ', $gregorianDate)[0];
        list($year, $month, $day) = explode('-', $datePart);
        list($jy, $jm, $jd) = gregorian_to_jalali((int)$year, (int)$month, (int)$day);
        return $jy . '/' . str_pad($jm, 2, '0', STR_PAD_LEFT) . '/' . str_pad($jd, 2, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('currentPersianYear')) {
    function currentPersianYear(): int
    {
        list($y, $m, $d) = gregorian_to_jalali((int)date('Y'), (int)date('n'), (int)date('j'));
        return $y;
    }
}

/**
 * تبدیل تاریخ شمسی به میلادی (رشته Y-m-d)
 */
if (!function_exists('persian_to_gregorian')) {
    function persian_to_gregorian(int $year, int $month, int $day): string
    {
        $result = jalali_to_gregorian($year, $month, $day);
        return sprintf('%04d-%02d-%02d', $result[0], $result[1], $result[2]);
    }
}

/**
 * تبدیل تاریخ میلادی (Y-m-d) به آرایه شمسی [year, month, day]
 */
if (!function_exists('gregorian_to_jalali_array')) {
    function gregorian_to_jalali_array(string $gregorianDate): array
    {
        $parts = explode('-', $gregorianDate);
        if (count($parts) !== 3) {
            return ['year' => 0, 'month' => 0, 'day' => 0];
        }
        $gy = (int)$parts[0];
        $gm = (int)$parts[1];
        $gd = (int)$parts[2];
        $result = gregorian_to_jalali($gy, $gm, $gd);
        return ['year' => $result[0], 'month' => $result[1], 'day' => $result[2]];
    }
}

/**
 * بررسی صحت تاریخ شمسی
 */
if (!function_exists('is_valid_persian_date')) {
    function is_valid_persian_date(int $year, int $month, int $day): bool
    {
        return jcheckdate($month, $day, $year);
    }
}