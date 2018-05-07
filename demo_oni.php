<?php
require_once("ONI.php");

$c = new ONI("H\x94d\xE4l\x88l\xE0n\x96");
echo $c
    ->decrypt("Hello")
    ->rot(13)
    ->encrypt("Hello")
    ->decrypt("Hello")
    ->rot(13)
    ->reverse()
    ->toString();
    
?>