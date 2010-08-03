<?php defined('SYSPATH') or die('No direct script access.');

interface Yada_Interface_Aggregate
{
    public function register(Yada_Interface_Module $object, array $methods);
    public function unregister(Yada_Interface_Module $object);
}
