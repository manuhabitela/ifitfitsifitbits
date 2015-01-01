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
}
