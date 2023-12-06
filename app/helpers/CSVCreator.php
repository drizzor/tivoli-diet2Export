<?php

/**
 * Classe permettant la création des fichiers CSV
 */
class CSVCreator
{
    private string $_dataSourcePath;
    private string $_destinationPath;
    private string $_filename;
    private string $_errorMessage;
    private string $_extension = "csv";
    private string $_apiurl = "http://intranet-common.bureautique.local/chupmbws/ebdapptiv/v1/note-export/diete-nutrition-adulte/";

    private array $_specnoteId;
    private array $_json;
    private array $_dataToExport;

    /**
     * Constructeur permettant de récupérer le fichier source
     * @param string $dataSourcePath Chemin vers le fichier source à lire (ex: pubic/csv/export/file.csv)
     */
    public function __construct(string $dataSourcePath)
    {
        $this->_dataSourcePath = $dataSourcePath;
    }

    /**
     * Créer le CSV avec les données à copier/coller dans le classeur Excel des diet
     */
    public function create()
    {
        $this->fillAllId($this->_dataSourcePath);
        if(!empty($this->_dataToExport)) {
            $this->_setFilename();
            $fp = fopen($this->_destinationPath.$this->_filename, "w");
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
            if(!$this->_checkIntegrity($fp)) $this->_setErrorMessage("Les données envoyées sont incorrect.");
        } else $this->_setErrorMessage("Aucune données trouvées pour l'année : ".$_POST['year']);
        
    }

    /**
     * Récupère dans un tableau l'ensemble des specnoteid
     * @param $target cible le fichier envoyé par l'utilisateur comprenant les specnoteid
     */
    private function fillAllId(string $target)
    {
        $lines = file($target, FILE_IGNORE_NEW_LINES);

        foreach ($lines as $key => $value)
        {
            $this->_specnoteId[$key] = str_getcsv($value);
        }
        $this->fillAllData();
    }

    /**
     * Récupère l'ensemble des informations depuis l'API
     */
    private function fillAllData()
    {
        try {
            for ($i=0; $i < count($this->_specnoteId); $i++) { 
                $this->_json[$i] = file_get_contents($this->_apiurl . $this->_specnoteId[$i][0]);        
                $this->_json[$i] = json_decode($this->_json[$i]);
        
                // S'il ne s'agit pas d'un specnoteid valable remplace la donnée par "error" qui sera intercepté
                if((!isset($this->_json[$i]->noteInfosWs->specNoteId)) or 
                (isset($this->_json[$i]->noteInfosWs->noteName) and 
                $this->_json[$i]->noteInfosWs->noteName !== "Diagnostic de l'état nutritionnel adulte")) 
                    $this->_json[$i] = "error";
            } $this->filterData();
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la lecture du JSON : '.$e);
        }
    }

    /**
     * Filtre les données afin des les préparer telles qu'elles doivent apparaitre pour l'extraction
     */
    private function filterData() : void
    {
        for ($i = $y = 0; $i < count($this->_json); $i++, $y++) { 
            if($this->_json[$i] != 'error') {
                if(!$this->filterYear($this->_json[$i]->noteInfosWs->dateCreation)) {$y-=1;continue;}

                $countInterv = isset($this->_json[$i]->interv_CHKLIST) ? count($this->_json[$i]->interv_CHKLIST) : 0;

                $this->_dataToExport[$y] = [
                    "idPat" => (isset($this->_json[$i]->patInfosWs->patientNDOSM)) ? $this->_json[$i]->patInfosWs->patientNDOSM : '',
                    "dateScreening" => (isset($this->_json[$i]->noteInfosWs->dateCreation) ? $this->formatDate($this->_json[$i]->noteInfosWs->dateCreation) : ''),
                    "indexLit" => (isset($this->_json[$i]->loc_COMBO[0]) ? $this->getMyText('loc_COMBO', $this->_json[$i]->loc_COMBO[0]) : ''),
                    "age" => (isset($this->_json[$i]->age_TXT[0]) ? $this->_json[$i]->age_TXT[0] : ''),
                    "typeProjet" => (isset($this->_json[$i]->projet_RADLIST[0]) ? $this->getMyText('projet_RADLIST', $this->_json[$i]->projet_RADLIST[0]) : ''),
                    "depistage" => (isset($this->_json[$i]->delai_RADLIST[0]) ? $this->getMyText('delai_RADLIST', $this->_json[$i]->delai_RADLIST[0]) : ''),
                    "risqueDenutrition" => (isset($this->_json[$i]->risque_RADLIST[0]) ? $this->getMyText('risque_RADLIST', $this->_json[$i]->risque_RADLIST[0]) : ''),
                    "reeval" => (isset($this->_json[$i]->evalnutri_RADLIST[0]) ? $this->getMyText('evalnutri_RADLIST', $this->_json[$i]->evalnutri_RADLIST[0]) : ''),
                    "priseCharge" => (isset($this->_json[$i]->prise_RADLIST[0]) ? $this->getMyText('prise_RADLIST', $this->_json[$i]->prise_RADLIST[0]) : ''),
                    "evalNutri" => (isset($this->_json[$i]->eval_COMBO[0]) ? $this->getMyText('eval_COMBO', $this->_json[$i]->eval_COMBO[0]) : ''),
                    "intervention1" => $countInterv > 0 ? $this->getMyText("interv_CHKLIST", $this->_json[$i]->interv_CHKLIST[0]) : '',
                    "intervention2" => $countInterv > 1 ? $this->getMyText("interv_CHKLIST", $this->_json[$i]->interv_CHKLIST[1]) : '',
                    "intervention3" => $countInterv > 2 ? $this->getMyText("interv_CHKLIST", $this->_json[$i]->interv_CHKLIST[2]) : '',
                    "intervention4" => $countInterv > 3 ? $this->getMyText("interv_CHKLIST", $this->_json[$i]->interv_CHKLIST[3]) : '',
                    "suiviNutri" => (isset($this->_json[$i]->suivi_RADLIST[0]) ? $this->getMyText('suivi_RADLIST', $this->_json[$i]->suivi_RADLIST[0]) : ''),
                ];
            } else {
                $this->_dataToExport[$y] = [
                    "error" => "Note invalide",
                ];
            }
        }
    }

    /**
     * Vérifie si les données sont intègre et donc pas remplie de "error"
     * @param $fp fichier csv ouvert à vérifier
     */
    private function _checkIntegrity($fp) : bool
    {
        $checkData = []; $i = 0;
        
        foreach($this->_dataToExport as $key => $row) {
            if(!isset($row['error'])) {
                fputcsv($fp, $row);
                $checkData[$i] = $row; $i++;
            } 
        }
    
        fclose($fp);
    
        // Si aucune données (capture du scénario où toutes les lignes sont en "error") supprimer directement le CSV
        if(!isset($checkData[0]['idPat'])) { 
            unlink("public/csv/export/" . $this->getFilename());
            return false;
        } return true;
    }

    /**
     * Formate la date : 240823
     * @param $date au format 2023-07-19 11:51:03
     */
    private function formatDate(string $date) : string 
    {
        $date = substr($date, 0, strpos($date, " "));
        $date = explode("-", $date);
        $formated = $date[2] . $date[1] . substr($date[0], 2,3);
        return $formated;
    }

    /**
     * Vérifie si l'année correspond à celle souhaitée
     * @param $year
     */
    private function filterYear(string $year) : bool 
    {
        $year = explode("-", $year);

        if(empty($_POST['year'])) return true;
        if($year[0] == $_POST['year']) return true;
        return false;
    }

    /**
     * Récupère le texte des CHK, RADLIST, COMBO,...
     * @param $fieldName nom du champ dont les données doivent-être récupérée
     * @param $value la valeur du champ qui doit être traduit
     */
    private function getMyText(string $fieldName, $value) : string 
    {
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
     * Défini un nom alétoire au csv
     */
    private function _setFilename() : void 
    { 
        if(isset($_POST['year']) and !empty($_POST['year']))
            $this->_filename = date('YmdHis').rand(1,99) . "-". htmlspecialchars($_POST['year']) . "." . $this->_extension;
        $this->_filename = date('YmdHis').rand(1,99) . "-ALL" . "." . $this->_extension;
    }

    private function _setErrorMessage(string $message) : void { $this->_errorMessage = $message; }
    public function setPath(string $path) : void { $this->_destinationPath = $path; }
    public function getErrorMessage() : string { return $this->_errorMessage; }
    public function getPath() : string { return $this->_destinationPath; }
    public function getFilename() : string { return $this->_filename; }
}