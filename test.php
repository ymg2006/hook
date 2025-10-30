<?php
class ClassOne
{
    function func_one()
    {
        echo "one";
    }
function notused_one()
    {
        return 1;
    }
}
class ClassTwo
{
    function func_two()
    {
        echo "two";
    }
function notused_two()
    {
        return 2;
    }
}
$a = rand(1, 2);
if ($a == 1)
{
    $b = new ClassOne();
    $b->func_one();
}
else
{
    $b = new ClassTwo();
    $b->func_two();
}