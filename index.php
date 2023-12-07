<!DOCTYPE html>
<?php
require_once('app/bootstrap.php');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($file->uploadFile('uploadCSV')) {
        $createCSV->setDataSourcePath($file->getDir().$file->getUploadName());
        $createCSV->create("year");
        $file->deleteUploaded();
    } 
} 
?>

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
    <h1>Extraction équipes nutritionnelles et Plan Cancer</h1>
    <p>Extraction valable uniquement pour la note <b>Diagnostic de l'état nutritionnel adulte</b>. <br/>
    Le fichier CSV doit contenir uniquement une colonne de specnote ID. Ceux ne correspondant pas à la note seront ignoré.</p>
    <?php if($createCSV->getErrorMessage()): ?>
        <div class="error" style="color:red"><?=$createCSV->getErrorMessage()?></div>
    <?php endif; ?>    
    <?php if($file->getMessage()): ?>
        <div class="error" style="color:red"><?=$file->getMessage()?></div>
    <?php endif; ?> 

    
    <?php
        // Proposer le téléchargement du csv
        if($createCSV->getAllOK() and $file->getAllOK()) $createCSV->getCSVUrl();
    ?>

    <form action="index.php" method="POST" enctype="multipart/form-data">
        <label for="year">Année souhaitée:</label>
        <input type="text" name="year" id="year" value="<?= date('Y') ?>"><br>

        <label for="uploadCSV">Upload du CSV:</label>
        <input type="file" name="uploadCSV" id="uploadCSV"><br>
        <button type="submit">Générer le CSV</button>
    </form>
    <?= $createCSV->getAllCSV(); ?>
</body>
</html>

