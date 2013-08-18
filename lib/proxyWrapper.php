<?php

class proxyWrapper
{
    protected $_useProxy;
    
    public function __construct() {
        $this->_useProxy = true;
    }

    /**
    * @param int $id
    * @return Lbc_Ad
    */
    public function addProxy($proxy){
        $this->_id = $id;
    }

    public function file_get_contents($filename){
        if ($this->_useProxy){
            return $this->proxiedFileGetContent($filename);
        }
        else {
            return file_get_contents($filename);
        }
    }

    public function proxiedFileGetContent($url){

        // Création des options de la requête
        $opts = array(
            'http' => array (
                'proxy'=>'tcp://89.29.128.254:8081',
                'request_fulluri' => true
            )
        );

        // Création du contexte de transaction
        $ctx = stream_context_create($opts);
        // Récupération des données
        
        $content = file_get_contents($url, false, $ctx);

        return $content;
    }
}