<?php
/**
 * Debug de variables
 */
function dd(...$vars)
{
    foreach($vars as $var)
    {
        echo '<pre style="color: green;">';
        print_r($var);
        echo '</pre>';
    }
}