<?php

namespace Greg\Support;

class DateTime
{
    static public function toCurrentYearInterval($start, $delimiter = ' - ')
    {
        $interval = $start;

        $y = date('Y');

        if ($y > $start) {
            $interval .= $delimiter . $y;
        }

        return $interval;
    }

    static public function format($format, $time = 'now')
    {
        $string = strftime($format, static::toTimestamp($time));

        if (PHP_OS == 'WINNT') {
            $string = iconv('windows-1251', 'UTF-8', $string);
        }

        return $string;
    }

    static public function toTimestamp($time)
    {
        return Type::isNaturalNumber($time) ? $time : strtotime($time);
    }

    static public function diff($time1, $time2)
    {
        $time1 = static::toTimestamp($time1);

        $time2 = static::toTimestamp($time2);

        return ($time1 === $time2) ? 0 : ($time1 > $time2 ? 1 : -1);
    }
}