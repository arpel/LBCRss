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
            $this->proxyindex = $this->configData["general"]["bestproxy"];
        }
        else {
            $this->proxyindex = 0;
        }
    }

    public function configSave() {
        $fh = fopen("./lib/proxyWrapper.json", 'w')
                or die("Error opening output file");

        fwrite($fh, json_encode($this->configData));
        fclose($fh);
    }

    public function addDefaultProxies() {
        $this->proxyList = $this->configData["proxyList"];
    }

    public function file_get_contents($filename){
    if ($this->_useProxy){
            return $this->proxiedFileGetContent($filename);
        }
        else {
            return file_get_contents($filename);
        }
    }

    public function findbestproxy(){
        $status = "Starting proxy selection </br>";
        $index = 0;
        do {
            $status .= sprintf('  Trying proxy at index %d </br>', $index);
            $this->proxyindex = $index;

            $content = $this->proxiedFileGetContent('http://www.mon-ip.com/');

            if($content){
                $status .= sprintf('    Found working proxy at index %d </br>', $index);
                //print $content;
                
                $this->configData["general"]["bestproxy"] = $index;
                $this->configSave();
                
                $this->proxyindex = $index;
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
        $result = $this->proxiedFileGetContentCurl($url);

        if (empty($result['ERR'])) {
            //print $result['EXE'];
            return $result['EXE'];
        } else {
            print $result['ERR'];
            print("</br>");
            return false;
        }
    }

    public function proxiedFileGetContentCurl($url){
        $proxy = $this->proxyList[$this->proxyindex]["ip"];
        $proxyLoginPass= "";
        $referer = 'http://www.google.fr/';
        $agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8';
        $header = 1;
        $timeout = 2;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        if($proxyLoginPass!= "")
        {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, '$proxyLoginPass');
        }
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
     
        $result['EXE'] = curl_exec($ch);
        $result['INF'] = curl_getinfo($ch);
        $result['ERR'] = curl_error($ch);
     
        curl_close($ch);
     
        return $result;
    }
}