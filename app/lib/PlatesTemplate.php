<?php
use League\Plates\Template\Template;

class PlatesTemplate extends Template {
    public function setLayout($name, array $data = array())
    {
        $this->layoutName = $name;
        $this->layoutData = $data;
    }
}
