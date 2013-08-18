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
            $this->findbestproxy("http://www.google.com");
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
                'timeout' => 5
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
        $index = 0;
        do {
            printf('Trying proxy at index %d ', $index);
            $this->buildOpts($index);

            $content = $this->proxiedFileGetContent($url);

            if($content){
                printf('Found working proxy at index %d ', $index);
                //print $content;
                
                $this->configData["general"]["bestproxy"] = $index;
                $this->configSave();
                
                $this->buildOpts($index);
                return $content;
            }
            else {
                $index++;
                if($index >= sizeof($this->proxyList))
                {
                    print "Failed to get content with proxy ... ";
                    return false;
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