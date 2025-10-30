--TEST--
hook basic test
--INI--
hook.greeting=Howdy
--FILE--
<?php
var_dump( hook_world() );
var_dump( hook_repeat("Farid", 3) );
?>
--EXPECTF--
string(%d) "%s"
string(%d) "Howdy, Farid | Howdy, Farid | Howdy, Farid"
