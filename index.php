<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
require_once('helpers/Uploader.php');

$json = $dataToExport = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // isset($_POST['year']) and !empty($_POST['year'])
    // isset($_POST['submit']))
    // $input = (int)$_POST['year'];
    // echo is_numeric($input);

    // Gestion fichier uploadé
        $file = new Uploader();
        $file->setDir("csv/specnoteid/");
        $file->setMaxSize(.5);
        $file->setExtensions(array('csv'));

        if($file->uploadFile('uploadCSV')) {
            $json = getAllSpecnoteId($file->getUploadName());
            $dataToExport = filterData($json);
            print_r($json);
            // createCSV($dataToExport);
        } 
        else echo $file->getMessage();
} 

/**
 * Récupère le texte des CHK, RADLIST, COMBO,...
 */
function getMyText(string $comboName, $value) : string {
    if($comboName == 'loc_COMBO') {
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

    if($comboName == 'projet_RADLIST') {
        if ($value == 1) return 'Nutritionteam/Equipes nutritionnelles';
        if ($value == 2) return 'kankerplan/Plan Cancer';
        return '';
    }

    if($comboName == 'delai_RADLIST') {
        if ($value == 1) return 'nee/non';
        if ($value == 2) return 'ja/oui < 48h';
        if ($value == 3) return 'ja/oui > 48h';
        return '';
    }
        
    if($comboName == 'risque_RADLIST') {
        if ($value == 1) return 'ja/oui';
        if ($value == 2) return 'nee/non';
        return '';
    }

    if($comboName == 'evalnutri_RADLIST') {
        if ($value == 1) return 'geen herscreening/ pas de réévaluation';
        if ($value == 2) return 'ja met risico/ oui avec risque';
        if ($value == 3) return 'ja zonder risico/ oui sans risque';
        return '';
    }

    if($comboName == 'prise_RADLIST') {
        if ($value == 1) return "geen nutritionele interventie uitgevoerd/pas d'intervention nutritionnelle réalisée";
        if ($value == 2) return 'diëtist/diététicien';
        if ($value == 3) return 'arts/médecin';
        if ($value == 4) return 'verpleegkundige/infirmier';
        if ($value == 5) return 'apotheker/pharmacien';
        return '';
    }

    if($comboName == 'eval_COMBO') {
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

    if($comboName == 'suivi_RADLIST') {
        if ($value == 1) return 'Ja/oui';
        if ($value == 2) return 'Geen FU/pas de suivi';
        return '';
    }
}

/**
 * Récupère sans distinction les specnote ID mentionné dans le CSV
 */
function getAllSpecnoteId(string $target) : array {
    $specnoteId = $json = [];
    $lines = file("csv/specnoteid/" . $target, FILE_IGNORE_NEW_LINES);

    foreach ($lines as $key => $value)
    {
        $specnoteId[$key] = str_getcsv($value);
    }

    for ($i=1; $i < count($specnoteId); $i++) { 
        $json[$i-1] = file_get_contents('http://intranet-common.bureautique.local/chupmbws/ebdapptiv/v1/note-export/diete-nutrition-adulte/' . $specnoteId[$i][0]);
        $json[$i-1] = json_decode($json[$i-1]);
    }

    return $json;
}

/**
 * Filtre les données afin des les préparer telle qu'elles doivent apparaitre pour l'extraction
 */
function filterData(array $json) : array {
    $dataToExport = [];

    for ($i=0; $i < count($json); $i++) { 
        if(!checkYear($json[$i]->noteInfosWs->dateCreation)) continue;
    
        $dataToExport[$i] = [
            "idPat" => (isset($json[$i]->patInfosWs->patientNDOSM)) ? $json[$i]->patInfosWs->patientNDOSM : '',
            "dateScreening" => (isset($json[$i]->noteInfosWs->dateCreation) ? formatDate($json[$i]->noteInfosWs->dateCreation) : ''),
            "indexLit" => (isset($json[$i]->loc_COMBO) ? getMyText('loc_COMBO', $json[$i]->loc_COMBO) : ''),
            "age" => (isset($json[$i]->age_TXT) ? $json[$i]->age_TXT : ''),
            "typeProjet" => (isset($json[$i]->projet_RADLIST) ? getMyText('projet_RADLIST', $json[$i]->projet_RADLIST) : ''),
            "depistage" => (isset($json[$i]->delai_RADLIST) ? getMyText('delai_RADLIST', $json[$i]->delai_RADLIST) : ''),
            "risqueDenutrition" => (isset($json[$i]->risque_RADLIST) ? getMyText('risque_RADLIST', $json[$i]->risque_RADLIST) : ''),
            "reeval" => (isset($json[$i]->evalnutri_RADLIST) ? getMyText('evalnutri_RADLIST', $json[$i]->evalnutri_RADLIST) : ''),
            "priseCharge" => (isset($json[$i]->prise_RADLIST) ? getMyText('prise_RADLIST', $json[$i]->prise_RADLIST) : ''),
            "evalNutri" => (isset($json[$i]->eval_COMBO) ? getMyText('eval_COMBO', $json[$i]->eval_COMBO) : ''),
            "intervention1" => (isset($json[$i]->interv_CHKLIST) ? $json[$i]->interv_CHKLIST : 'nok'),
            "intervention2" => (isset($json[$i]->interv_CHKLIST) ? $json[$i]->interv_CHKLIST : 'nok'),
            "intervention3" => (isset($json[$i]->interv_CHKLIST) ? $json[$i]->interv_CHKLIST : 'nok'),
            "intervention4" => (isset($json[$i]->interv_CHKLIST) ? $json[$i]->interv_CHKLIST : 'nok'),
            "suiviNutri" => (isset($json[$i]->suivi_RADLIST) ? getMyText('suivi_RADLIST', $json[$i]->suivi_RADLIST) : ''),
        ];
    }

    return $dataToExport;
}

/**
 * Affiche dans le navigateur les données (pour test)
 */
function showData(array $data) : void {
    for ($i=0; $i < count($data) ; $i++) {
        echo '<hr>';
        echo "Identité patient anonymisée : " . $data[$i]['idPat'] .  
        "<br>Date du screening : " . formatDate($data[$i]['dateScreening']) . 
        "<br>Index de lit ou type d'admission : " . $data[$i]['indexLit'] .
        "<br>Age ou date de naissance : " . $data[$i]['age'] .
        "<br>Type de projet : " . $data[$i]['typeProjet'] .
        "<br>Dépistage </> 48h : " . $data[$i]['depistage'] .
        "<br>Risque de dénutrition : " . $data[$i]['risqueDenutrition'] .
        "<br>Si le premier dépistage était négatif, y a t-il eu une réévaluation? : " . $data[$i]['reeval'] .
        "<br>Qui a pris en charge l'intervention  nutritionnelle? : " . $data[$i]['priseCharge'] .
        "<br>Résultat de l'évaluation nutritionnelle : " . $data[$i]['evalNutri'] .
        "<br>Type d'intervention 1 : " . $data[$i]['intervention1'] .
        "<br>Type d'intervention 2 : " . $data[$i]['intervention2'] .
        "<br>Type d'intervention 3 : " . $data[$i]['intervention3'] .
        "<br>Type d'intervention 4 : " . $data[$i]['intervention4'] .
        "<br>Y-a-il eu un suivi nutritionnel soit en hospitalisation soit en consultation ? : " . $data[$i]['suiviNutri'];        
    }
    echo '<hr>';
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
function checkYear(string $year) : bool {
    $year = explode("-", $year);

    if($year[0] == '2023') return true;

    return false;
}

/**
 * Créer le CSV avec les données à copier/coller dans le classeur Excel des diet
 * @param $data tableau contenant le données à récupérer dans le CSV
 */
function createCSV(array $data) : void {
    $fp = fopen("csv/export/" . getRandomName(), "w");
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

    foreach($data as $row) {
        fputcsv($fp, $row);
    }

    fclose($fp);
}

/**
 * Genère un nom aléatoire pour la création des fichiers CSV (éviter doublon)
 * @param $ex -> Extension souhaitée par défaut CSV
 */
function getRandomName(string $ex = "csv") : string {
    return strtotime(date('Y-m-d H:i:s')).rand(1111,9999).rand(11,99).rand(111,999) . "." . $ex;
}