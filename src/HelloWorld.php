<?php

namespace Mifra\Crud;

class HelloWorld
{
    public function sayHello()
    {
        return "Hello, World! " . config('mifracrud.template_path');
    }
}
