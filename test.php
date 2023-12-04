<form action="test.php" method="POST">
    <label for="year">Année souhaitée:</label>
    <input type="text" name="year" id="year" value="<?= date('Y') ?>"><br>
    <button type="submit">Générer le CSV</button>
</form>
<?php

$dataToExport = [];

// Test année filtre
$data = array(
    array("attr1" => "lorem",
    "attr2" => "Lorem ipsum dolor sit",
    "attr3" => "amet consectetur adipisicing elit",
    "attr4" => "Veniam, iure beatae?",
    "year" => "2023"),
    array( "attr1" => "Aperiam, nostrum cum dignissimos",
    "attr2" => "impedit sit asperiores libero",
    "attr3" => "debitis minima inventore officia",
    "attr4" => "officiis illum laudantium neque",
    "year" => "2023"),
    array( "attr1" => "Aperiam, nostrum cum dignissimos",
    "attr2" => "impedit sit asperiores libero",
    "attr3" => "debitis minima inventore officia",
    "attr4" => "officiis illum laudantium neque",
    "year" => "2022"),
    array( "attr1" => "Aperiam, nostrum cum dignissimos",
    "attr2" => "impedit sit asperiores libero",
    "attr3" => "debitis minima inventore officia",
    "attr4" => "officiis illum laudantium neque",
    "year" => "2023"),
    array( "attr1" => "adipisicing elit", 
    "attr2" => "Sapiente aut",
    "attr3" => "voluptatem autem",
    "attr4" => "quae ea sequi",
    "year" => "2022"),
    array( "attr1" => "Aperiam, nostrum cum dignissimos",
    "attr2" => "impedit sit asperiores libero",
    "attr3" => "debitis minima inventore officia",
    "attr4" => "officiis illum laudantium neque",
    "year" => "2023"),
);

$y = 0;
for ($i = $y = 0; $i < count($data); $i++, $y++) { 
    if(!filterYear($data[$i]['year'])) {$y-=1;continue; }
    $dataToExport[$y] = [
        "attr1" => $data[$i]['attr1'],
        "attr1" => $data[$i]['attr2'],
        "attr3" => $data[$i]['attr3'],
        "attr4" => $data[$i]['attr4'],
        "year" => $data[$i]['year'],
    ];
} 

function filterYear(string $year) : bool {
    if(empty($_POST['year'])) return true;
    if($year == $_GET['year']) return true;
    return false;
}

print_r($dataToExport); 