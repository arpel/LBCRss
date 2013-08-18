<?php

class proxyWrapper
{
    protected $_useProxy;
    
    public function __construct() {
        // Load configuration file
        $str_data = file_get_contents("./lib/proxyWrapper.json", FILE_USE_INCLUDE_PATH);
        $this->configData = json_decode($str_data, true);
        
        if(!$this->configData) {
            print 'Failed decoding JSON configuration file';
        }

        // Global proxy Bypass
        if($this->configData["general"]["useproxy"] > 0) {
            $this->_useProxy = true;
        }
        else {
            $this->_useProxy = false;
        }

        $this->proxyList = array();
        $this->addDefaultProxies();

        if($this->configData["general"]["bestproxy"] >= 0) {
            $this->buildOpts($this->configData["general"]["bestproxy"]);
        }
        else {
            //$this->findbestproxy("http://www.google.com");
            $this->buildOpts(0);
        }
    }

    public function configSave() {
        $fh = fopen("./lib/proxyWrapper.json", 'w')
                or die("Error opening output file");

        fwrite($fh, json_encode($this->configData));
        fclose($fh);
    }

    public function buildOpts($proxyindex){
        $this->opts = array(
            'http' => array (
                'proxy' => $this->proxyList[$proxyindex],
                'request_fulluri' => true,
                'timeout' => 2
            )
        );
    }

    public function addDefaultProxies() {
        for($index = 0; $index < sizeof($this->configData["proxyList"]); $index++){
            $this->proxyList[] = $this->configData["proxyList"][$index];
        }
    }

    public function file_get_contents($filename, $findbestproxy = false){
        if ($this->_useProxy){
            if($findbestproxy){
                $content = $this->findbestproxy($filename);
            } else
            {
                $content = $this->proxiedFileGetContent($filename);
            }
            return $content;
        }
        else {
            return file_get_contents($filename);
        }
    }

    public function findbestproxy($url){
        $status = "Starting proxy selection </br>";
        $index = 0;
        do {
            $status .= sprintf('  Trying proxy at index %d </br>', $index);
            $this->buildOpts($index);

            $content = $this->proxiedFileGetContent($url);

            if($content){
                $status .= sprintf('    Found working proxy at index %d </br>', $index);
                //print $content;
                
                $this->configData["general"]["bestproxy"] = $index;
                $this->configSave();
                
                $this->buildOpts($index);
                return $status;
            }
            else {
                $index++;
                if($index >= sizeof($this->proxyList))
                {
                    $status .= sprintf("Failed to get content with proxy ... </br>");
                    return $status;
                }
                
            }
        } while(true);
    }

    public function proxiedFileGetContent($url){
        $ctx = stream_context_create($this->opts);            
        $content = file_get_contents($url, false, $ctx);
        return $content;
    }
}