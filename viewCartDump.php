<?php

echo "<div class='viewcart'><pre>";
var_dump($cart->contents());
echo "<hr>";
// var_dump($cart->total());
echo "</pre></div>";

?>

<style>
    pre {
        background-color: dodgerblue;
        color: white;

    }
</style>