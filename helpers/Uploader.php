<?php

class Uploader
{
    private $destinationPath;
    private $errorMessage;
    private $extensions;
    private $maxSize;
    private $uploadName;
    public $name = 'Uploader';
    public $useTable = false;

    public function setDir($path){
        $this->destinationPath = $path;
    }

    public function setMaxSize($sizeMB){
        $this->maxSize = $sizeMB * (1024*1024);
    }

    public function setExtensions(array $options) : void {
        $this->extensions = $options;
    }

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

    public function getDir(){
        return $this->destinationPath;
    }

    private function setMessage($message){
        $this->errorMessage = $message;
    }

    public function getMessage(){
        return $this->errorMessage;
    }

    public function getUploadName(){
        return $this->uploadName;
    }

    private function getRandom(){
        return strtotime(date('Y-m-d H:i:s')).rand(1111,9999).rand(11,99).rand(111,999);
    }
        
    public function uploadFile($fileBrowse) : bool{
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

    public function deleteUploaded() : void{
        unlink($this->destinationPath.$this->uploadName);
    }
}