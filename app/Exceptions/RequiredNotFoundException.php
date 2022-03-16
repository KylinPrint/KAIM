<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Throwable;

class RequiredNotFoundException extends Exception
{
    //
    public function __construct(string $message = '',int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message,$code,$previous);
    }

    public function render(Request $request)
    {
        $str = '第'.$this->getMessage().'行有未填必填项！';
        return response($str ?: '真有问题了');
    }
}
