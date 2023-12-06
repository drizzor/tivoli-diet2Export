<?php
/**
 * Debug de variables
 * @param $param je peux passer autant de variable que souhaité
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

/**
 * Permet d'envoyer un message d'erreur dans la console
 */
function debug_to_console(string | array $data) : void {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}