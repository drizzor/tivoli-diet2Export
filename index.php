<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400&display=swap" rel="stylesheet"> 
    <title>Diet Export</title>
</head>
<body>
    <form action="index.php" method="POST" enctype="multipart/form-data">
        <label for="year">Année souhaitée:</label>
        <input type="text" name="year" id="year" value="<?= date('Y') ?>"><br>

        <label for="uploadCSV">Upload du CSV:</label>
        <input type="file" name="uploadCSV" id="uploadCSV"><br>
        <button type="submit">Générer le CSV</button>
    </form>
</body>
</html>

<?php
require_once('app/bootstrap.php');

$json = $dataToExport = [];
$csvName = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {

    $file = new Uploader();
    $file->setDir("public/csv/specnoteid/");
    $file->setMaxSize(.5);
    $file->setExtensions(array('csv'));
    $createCSV = new CSVCreator($file->getDir().$file->getUploadName());

    if($file->uploadFile('uploadCSV')) {
        // $json = getAllSpecnoteId($file->getDir().$file->getUploadName());
        // $dataToExport = filterData($json);

        // if(!empty($dataToExport)) $csvName = createCSV($dataToExport);
        // else echo "Aucune données trouvées pour l'année : ".$_POST['year'];
        
        $createCSV->setPath("public/csv/export/");
        $createCSV->create();
        $file->deleteUploaded();
    } 
    else echo $file->getMessage();

     // Proposer le téléchargement du csv
     if(!$createCSV->getErrorMessage())
        getCSVUrl($createCSV->getFilename());
    else echo $createCSV->getErrorMessage();
    
} getAllCSV("public/csv/export");

/**
 * Récupère le texte des CHK, RADLIST, COMBO,...
 * @param $fieldName nom du champ dont les données doivent-être récupérée
 * @param $value la valeur du champ qui doit être traduit
 */
function getMyText(string $fieldName, $value) : string {
    if($fieldName == 'loc_COMBO') {
        if ($value == 1) return 'C';
        if ($value == 2) return 'D';
        if ($value == 3) return 'G';
        if ($value == 4) return 'E';
        if ($value == 5) return 'I';
        if ($value == 6) return 'K';
        if ($value == 7) return 'K1';
        if ($value == 8) return 'A1';
        if ($value == 9) return 'A';
        if ($value == 10) return 'SP';
        if ($value == 11) return 'H (CD)';
        if ($value == 12) return 'ambulant/consultation';
        if ($value == 13) return 'Dagopname/Hôpital de jour';
        return '';
    }

    if($fieldName == 'projet_RADLIST') {
        if ($value == 1) return 'Nutritionteam/Equipes nutritionnelles';
        if ($value == 2) return 'kankerplan/Plan Cancer';
        return '';
    }

    if($fieldName == 'delai_RADLIST') {
        if ($value == 1) return 'nee/non';
        if ($value == 2) return 'ja/oui < 48h';
        if ($value == 3) return 'ja/oui > 48h';
        return '';
    }
        
    if($fieldName == 'risque_RADLIST') {
        if ($value == 1) return 'ja/oui';
        if ($value == 2) return 'nee/non';
        return '';
    }

    if($fieldName == 'evalnutri_RADLIST') {
        if ($value == 1) return 'geen herscreening/ pas de réévaluation';
        if ($value == 2) return 'ja met risico/ oui avec risque';
        if ($value == 3) return 'ja zonder risico/ oui sans risque';
        return '';
    }

    if($fieldName == 'prise_RADLIST') {
        if ($value == 1) return "geen nutritionele interventie uitgevoerd/pas d'intervention nutritionnelle réalisée";
        if ($value == 2) return 'diëtist/diététicien';
        if ($value == 3) return 'arts/médecin';
        if ($value == 4) return 'verpleegkundige/infirmier';
        if ($value == 5) return 'apotheker/pharmacien';
        return '';
    }

    if($fieldName == 'eval_COMBO') {
        if ($value == 1) return 'afwezigheid van ondervoeding en overgewicht/absence de dénutrition et de surcharge pondérale ';
        if ($value == 2) return 'ernstige ondervoeding/dénutrition sévère';
        if ($value == 3) return 'ondervoeding/dénutrition';
        if ($value == 4) return 'overgewicht/surpoids';
        if ($value == 5) return 'obesitas/obésité';
        if ($value == 6) return 'obesitas en ondervoeding/obésité et dénutrition';
        if ($value == 7) return 'overgewicht en ondervoeding/surpoids et dénutrition';
        if ($value == 8) return 'ernstige obesitas/obésité sévère';
        return '';
    }

    if($fieldName == 'suivi_RADLIST') {
        if ($value == 1) return 'Ja/oui';
        if ($value == 2) return 'Geen FU/pas de suivi';
        return '';
    }

    if($fieldName == 'interv_CHKLIST') {
        if ($value == 1) return 'persoonlijk advies/Conseils personnalisés';
        if ($value == 2) return 'ONS/Compléments nutritionnels oraux';
        if ($value == 3) return 'EN/Nutrition entérale';
        if ($value == 4) return 'PN/Nutrition parentérale';
        return '';
    }
}

/**
 * Récupère les informations de la note via le specnoteid
 * @param $target contient le nom du fichier uploadé par l'utilisateur
 */
function getAllSpecnoteId(string $target) : array {
    $specnoteId = $json  = [];
    $lines = file($target, FILE_IGNORE_NEW_LINES);

    foreach ($lines as $key => $value)
    {
        $specnoteId[$key] = str_getcsv($value);
    }

    set_error_handler(
        function ($err_severity, $err_msg, $err_file, $err_line) {
            // do not throw an exception if the @-operator is used (suppress)
            if (error_reporting() === 0) return false;
            throw new ErrorException( $err_msg, 0, $err_severity, $err_file, $err_line );
        },
        E_WARNING
    );

    // Permet notament de capturer l'éventuelle espace blanc (sans contenu) au milieu du fichier
    try {
        for ($i=0; $i < count($specnoteId); $i++) { 
            $json[$i] = file_get_contents('http://intranet-common.bureautique.local/chupmbws/ebdapptiv/v1/note-export/diete-nutrition-adulte/' . $specnoteId[$i][0]);        
            $json[$i] = json_decode($json[$i]);
    
            // S'il ne s'agit pas d'un specnoteid valable remplace donné par "error" qui sera intercepté
            if((!isset($json[$i]->noteInfosWs->specNoteId)) or 
            (isset($json[$i]->noteInfosWs->noteName) and 
            $json[$i]->noteInfosWs->noteName !== "Diagnostic de l'état nutritionnel adulte")) 
                $json[$i] = "error";
        }
    } catch (Exception $e) {
        // debug_to_console("Erreur présente dans le fichier uploadé. Certaine ligne ont été ignorée.");
    }
    restore_error_handler();

    return $json;
}

/**
 * Filtre les données afin des les préparer telle qu'elles doivent apparaitre pour l'extraction
 * @param $json Passe le json avec l'ensemble des données non filtrée
 */
function filterData(array $json) : array {
    $dataToExport = [];

    for ($i = $y = 0; $i < count($json); $i++, $y++) { 
        if($json[$i] != 'error') {
            if(!filterYear($json[$i]->noteInfosWs->dateCreation)) {$y-=1;continue;}

            $countInterv = isset($json[$i]->interv_CHKLIST) ? count($json[$i]->interv_CHKLIST) : 0;

            $dataToExport[$y] = [
                "idPat" => (isset($json[$i]->patInfosWs->patientNDOSM)) ? $json[$i]->patInfosWs->patientNDOSM : '',
                "dateScreening" => (isset($json[$i]->noteInfosWs->dateCreation) ? formatDate($json[$i]->noteInfosWs->dateCreation) : ''),
                "indexLit" => (isset($json[$i]->loc_COMBO[0]) ? getMyText('loc_COMBO', $json[$i]->loc_COMBO[0]) : ''),
                "age" => (isset($json[$i]->age_TXT[0]) ? $json[$i]->age_TXT[0] : ''),
                "typeProjet" => (isset($json[$i]->projet_RADLIST[0]) ? getMyText('projet_RADLIST', $json[$i]->projet_RADLIST[0]) : ''),
                "depistage" => (isset($json[$i]->delai_RADLIST[0]) ? getMyText('delai_RADLIST', $json[$i]->delai_RADLIST[0]) : ''),
                "risqueDenutrition" => (isset($json[$i]->risque_RADLIST[0]) ? getMyText('risque_RADLIST', $json[$i]->risque_RADLIST[0]) : ''),
                "reeval" => (isset($json[$i]->evalnutri_RADLIST[0]) ? getMyText('evalnutri_RADLIST', $json[$i]->evalnutri_RADLIST[0]) : ''),
                "priseCharge" => (isset($json[$i]->prise_RADLIST[0]) ? getMyText('prise_RADLIST', $json[$i]->prise_RADLIST[0]) : ''),
                "evalNutri" => (isset($json[$i]->eval_COMBO[0]) ? getMyText('eval_COMBO', $json[$i]->eval_COMBO[0]) : ''),
                "intervention1" => $countInterv > 0 ? getMyText("interv_CHKLIST", $json[$i]->interv_CHKLIST[0]) : '',
                "intervention2" => $countInterv > 1 ? getMyText("interv_CHKLIST", $json[$i]->interv_CHKLIST[1]) : '',
                "intervention3" => $countInterv > 2 ? getMyText("interv_CHKLIST", $json[$i]->interv_CHKLIST[2]) : '',
                "intervention4" => $countInterv > 3 ? getMyText("interv_CHKLIST", $json[$i]->interv_CHKLIST[3]) : '',
                "suiviNutri" => (isset($json[$i]->suivi_RADLIST[0]) ? getMyText('suivi_RADLIST', $json[$i]->suivi_RADLIST[0]) : ''),
            ];
        } else {
            $dataToExport[$y] = [
                "error" => "Note invalide",
            ];
        }
    }
    return $dataToExport;
}

/**
 * Formate la date : 240823
 * @param $date au format 2023-07-19 11:51:03
 */
function formatDate(string $date) : string {
    $date = substr($date, 0, strpos($date, " "));
    $date = explode("-", $date);
    $formated = $date[2] . $date[1] . substr($date[0], 2,3);
    return $formated;
}

/**
 * Vérifie si l'année correspond à celle souhaitée
 * @param $year
 */
function filterYear(string $year) : bool {
    $year = explode("-", $year);

    if(empty($_POST['year'])) return true;
    if($year[0] == $_POST['year']) return true;
    return false;
}

/**
 * Créer le CSV avec les données à copier/coller dans le classeur Excel des diet
 * @param $data tableau contenant le données à récupérer dans le CSV
 */
function createCSV(array $data) : bool|string {
    $filename = getRandomName();
    $fp = fopen("public/csv/export/" . $filename, "w");
    // convert special char éèë,....
    fputs($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

    $headers = array(
        "idPat",
        "dateScreening",
        "indexLit",
        "age",
        "typeProjet",
        "depistage",
        "risqueDenutrition",
        "reeval",
        "priseCharge",
        "evalNutri",
        "intervention1",
        "intervention2",
        "intervention3",
        "intervention4",
        "suiviNutri",
    );

    fputcsv($fp, $headers);
    $checkData = []; $i = 0;

    foreach($data as $key => $row) {
        if(!isset($row['error'])) {
            fputcsv($fp, $row);
            $checkData[$i] = $row; $i++;
        } 
    }

    fclose($fp);

    // Si aucune données (capture du scénario où toutes les lignes sont en "error") supprimer directement le CSV
    if(!isset($checkData[0]['idPat'])) { 
        unlink("public/csv/export/" . $filename);
        return false;
    }
    return $filename;    
}

/**
 * Récupère l'ensemble des CSV créé
 * @param $path chemin où doit être lu les différents fichiers
 */
function getAllCSV(string $path) : void {
    $csvLinks = []; $i = 0;

    if (is_dir($path)) {
        if ($dh = opendir($path)) {
            while (($file = readdir($dh)) !== false) {
                if($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'csv') {
                    $csvLinks[$i]["link"] = "<li><a href='".$path."/".$file."'>".$file."</a></li>"; 
                    $csvLinks[$i]["path"] = $path."/".$file;
                    $i++;
                }
            }
            closedir($dh);
        } 
    }     
    $csvLinks = cleanCsvList(array_reverse($csvLinks));
    showCSV($csvLinks);
}

/**
 * Affiche les CSV disponible dans l'interface (fonction liée à getAllCSV())
 */
function showCSV(array $csvLinks) : void {
    echo "<h1>Derniers fichiers créés</h1>";
    if(!empty($csvLinks)) {          
        echo "<ul>";
        foreach ($csvLinks as $csvLink) {
            echo $csvLink['link'];
        }
        echo "</ul>";
    } else echo "<p>Aucun fichier pour le moment.</p>";
}

/**
 * Crée un a href avec les données diet fraichement récupérée
 * @param $filename nom du fichier csv
 */
function getCSVUrl(string $filename) : void {
    echo "<div>Télécharger le CSV : <a href='public/csv/export/".$filename."'>". $filename ."</a></div>";
}

/**
 * Supprime la dernière valeur SI plus de 20 éléments
 * @param $data contient les fichiers existants ainsi que leur localisation
 * @param $max nombre maximum de fichier autorisé
 */
function cleanCsvList(array $data, int $max = 5) : array {
    if(count($data) > $max) {
        unlink(end($data)['path']);
        array_pop($data);
    }
    return $data;
}

/**
 * Genère un nom aléatoire pour la création des fichiers CSV (éviter doublon)
 * @param $ex -> Extension souhaitée par défaut CSV
 */
function getRandomName(string $ex = "csv") : string {
    if(isset($_POST['year']) and !empty($_POST['year']))
        return date('YmdHis').rand(1,99) . "-". htmlspecialchars($_POST['year']) . "." . $ex;
    return date('YmdHis').rand(1,99) . "-ALL" . "." . $ex;
}