<?php
class Halp {
    public static function toNanos($seconds)
    {
        return $seconds*1000000000;
    }

    public static function toSeconds($nanos)
    {
        return round($nanos/1000000000);
    }

    public static function roundedHour($hour)
    {
        return $hour < 6 ? 0 : ($hour < 11 ? 8 : ($hour < 14 ? 12 :
            ($hour < 18 ? 16 : ($hour < 23 ? 20 : 0) ) ) );
    }
}
