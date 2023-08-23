<?php 
namespace App\Data;

class Person
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

?>