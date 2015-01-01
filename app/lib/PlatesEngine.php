<?php
use League\Plates\Engine;

require_once __DIR__.'/PlatesTemplate.php';

class PlatesEngine extends Engine {
    public function make($name)
    {
        return new PlatesTemplate($this, $name);
    }
}
