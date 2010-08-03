<?php defined('SYSPATH') or die('No direct script access.');

abstract class Yada_Record_Core extends ArrayObject
{
    protected $_model;

    public function  __construct($array, $model)
    {
        $this->_model = $model;
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }

    public function prefill()
    {
        $fill = array_fill_keys($this->_model->fields(), NULL);
        $fill = array_merge($fill, $this->_model->defaults(), $this->getArrayCopy());
        $this->exchangeArray($fill);
    }
}