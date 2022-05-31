<?php
/**
 * new对象时传一个对象进来，复制目标的所有属性（protected属性要继承目标）
 */

namespace App\Traits;


Trait CopyObjectAttributes
{
    public function __construct(object $Obj)
    {
        $this->copyParentAttributes($Obj);
    }
    function copyParentAttributes($Obj)
    {
        $objValues = get_object_vars($Obj); // return array of object values
        foreach($objValues AS $key=>$value)
        {
            $this->$key = $value;
        }
    }
}
