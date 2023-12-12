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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Diet Export</title>
</head>
<body>
    <div class="container">
        <div class="child-container">
            <div class="form-title"><h2>Extraction équipes nutritionnelles et Plan Cancer</h2></div>
            <div class="form-card">
                
                <p>Extraction valable uniquement pour la note <b>Diagnostic de l'état nutritionnel adulte</b>. <br/>
                Le fichier CSV doit contenir uniquement une colonne de specnote ID. Ceux ne correspondant pas à la note seront ignoré.</p>

                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <label for="year">Année souhaitée:</label>
                    <input type="text" name="year" id="year" value="<?= date('Y') ?>"><br>

                    <label for="uploadCSV">Upload du CSV:</label>
                    <input type="file" name="uploadCSV" id="uploadCSV"><br>
                    <button class="btn" type="submit">Générer le CSV</button>
                    <?php if($createCSV->getAllOK() and $file->getAllOK()) $createCSV->getCSVUrl(); ?>
                </form>
            </div>
            <?php if($createCSV->getErrorMessage()): ?>
                <div class="error"><i class="fa-solid fa-circle-exclamation"></i><?=$createCSV->getErrorMessage()?></div>
            <?php endif; ?>    
            <?php if($file->getMessage()): ?>
                <div class="error"><i class="fa-solid fa-circle-exclamation"></i><?=$file->getMessage()?></div>
            <?php endif; ?> 
        </div>

        <div class="child-container">
            <?= $createCSV->getAllCSV(); ?>
        </div>
    </div>
    
</body>
</html>

