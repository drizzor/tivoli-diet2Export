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

    echo "<script>console.log('Debug Objects: " . addslashes($output) . "' );</script>". PHP_EOL;
    if (ob_get_level() > 0) {
        ob_flush(); // Vide le tampon
        flush();    // Envoie immédiatement au navigateur
    }
}

/**
 * Permet l'affichage du spinner d'attente lorsque l'ont récupère les données
 */
function waitingScreen() : void {

    echo "<style>
            body {
                font-family: 'Roboto', sans-serif;
                text-align: center;
                padding: 50px;
                background-color: #161b22;
            }
            .message {
                font-size: 20px;
                color: lightgrey;
            }
            .spinner {
                margin: 20px auto;
                width: 50px;
                height: 50px;
                border: 5px solid #007e8a;
                border-top-color: #333;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            .hidden {
                display: none;
            }
        </style>
        <div id='waiting-screen'>
            <div class='message'>Travail en cours... Voir la console pour suivre la progression.</div>
            <div class='spinner'></div>
        </div>";
}