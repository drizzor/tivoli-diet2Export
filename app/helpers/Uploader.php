<?php

/**
 * Classe permettant l'uplaod de fichier
 */
class Uploader
{
    private $destinationPath;
    private $errorMessage;
    private $extensions;
    private $maxSize;
    private $uploadName;
    public $name = 'Uploader';
    public $useTable = false;

    /**
     * Défini le répertoire où va être upload le fichier
     */
    public function setDir(string $path) : void {
        $this->destinationPath = $path;
    }

    /**
     * Définir la taille maximale du fichier
     * @param $sizeMB la taille est inscrite en MB
     */
    public function setMaxSize(float $sizeMB) : void {
        $this->maxSize = $sizeMB * (1024*1024);
    }

    /**
     * Défini l'extension du fichier qui pourra être envoyé
     * @param $options inscrire les différentes extensions de fichier autorisée
     */
    public function setExtensions(array $options) : void {
        $this->extensions = $options;
    }

    /**
     * Récupère l'extension du fichier (.csv, .png,...)
     */
    private function getExtension(string $string) : string {
        $ext = "";
        try{
            $parts = explode(".", $string);
            $ext = strtolower($parts[count($parts)-1]);
        }catch(Exception $c){
            $ext = "";
        }
        return $ext;
    }

    /**
     * Récupère le chemin/répertoire du fichier
     */
    public function getDir() : string {
        return $this->destinationPath;
    }

    /**
     * Défini le message d'erreur
     */
    private function setMessage(string $message) : void {
        $this->errorMessage = $message;
    }

    /**
     * Récupère le message d'erreur
     */
    public function getMessage() : string {
        return $this->errorMessage;
    }

    /**
     * Récupère le nom du fichier de l'instance
     */
    public function getUploadName() : string {
        return $this->uploadName;
    }

    /**
     * Retourne un nom aléatoire pour la création de fichiers
     */
    private function getRandom() : string {
        return strtotime(date('Y-m-d H:i:s')).rand(1111,9999).rand(11,99).rand(111,999);
    }
    
    /**
     * Upload le CSV de l'utilisateur
     * @param $fileBrowse valeur attribut name de l'input file 
     * @param $autoDelete supprime automatiquement le csv 
     */
    public function uploadFile(string $fileBrowse) : bool{
        $result = false;
        $size = $_FILES[$fileBrowse]["size"];
        $name = $_FILES[$fileBrowse]["name"];
        $ext = $this->getExtension($name);

        if(!is_dir($this->destinationPath)) $this->setMessage("La destination du fichier n'existe pas.");
        else if(!is_writable($this->destinationPath)) $this->setMessage("Le répertoire n'est pas écrivable.");
        else if(empty($name)) $this->setMessage("Aucun fichier sélectionné.");
        else if($size>$this->maxSize) $this->setMessage("Fichier trop volumineux !");
        else if(in_array($ext,$this->extensions)){
            $this->uploadName = substr(md5(rand(1111,9999)),0,8).$this->getRandom().rand(1111,1000).rand(99,9999).".".$ext;

            if(move_uploaded_file($_FILES[$fileBrowse]["tmp_name"],$this->destinationPath.$this->uploadName))
                $result = true; 
            else $this->setMessage("L'upload à échoué, réessayer !");
            
        }
        else $this->setMessage("Format du fichier invalide !");
        
        return $result;
    }

    /**
     * Supprime le fichier
     */
    public function deleteUploaded() : void{
        unlink($this->destinationPath.$this->uploadName);
    }
}