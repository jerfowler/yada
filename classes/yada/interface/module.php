<?php defined('SYSPATH') or die('No direct script access.');

interface Yada_Interface_Module
{
    public function export(Yada_Interface_Aggregate $object);
}