<?php
require_once 'class.EmbedlyExceptions.php';

class EmbedlyClient {
    const LoggerName        = 'Embed.ly Plugin';
    const OEmbedEndpoint    = 'http://api.embed.ly/v1/api/oembed';
    const ServicesEndpoint  = 'http://api.embed.ly/v1/api/services/php';
    const OEmbedFormat      = 'object';
    const UserAgent         =
        'Mozilla/5.0 (compatible; ThinkTank Embed.ly Plugin/0.1; +http://github.com/jrunning/thinktank/tree/embedly-plugin/webapp/plugins/embedly/)';
    const BatchMaxThreads   = 5;
    const BatchTimeout      = 30; // seconds
    
    
    private $services = array();

    public function __construct() {
        $this->loadServices();
    }
    
    public function loadServices() {
        $this->services = $this->jsonRequest(self::ServicesEndpoint);
        return count($this->services);
    }
    
    public function oEmbedRequest($url) {
        $req_url = self::OEmbedEndpoint . '?url=' . urlencode($url);
        
        return $this->jsonRequest($req_url);
    }
    
    public function getOEmbedForURL($url) {
        return $this->oEmbedRequest($url);
    }
    
    public function getServicesMatch($url) {
        foreach($this->services as $service) {
            foreach($service->regex as $reg) {
                if(preg_match($reg, $url)) {
                    return $service;
                }
            }
        }
        
        return false;
    }
    
    public function batchedQuery($urls,
        $callback_class_name, $callback_static_method, $callback_extra_args
    ) {
        // Most of this code provided by manixrock[at]gmail[dot]com in PHP.net
        // documentation comments:
        // http://us3.php.net/manual/en/function.curl-multi-exec.php#88772
        $mcurl = curl_multi_init(); 
        $threads_running = 0; 
        $urls_idx = 0;
        
        for(;;) {
            // fill up the slots 
            while ($threads_running < self::BatchMaxThreads && count($urls) > 0) { 
                $ch = curl_init(); 
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                curl_setopt($ch, CURLOPT_TIMEOUT, self::BatchTimeout); 
                curl_setopt($ch, CURLOPT_URL, $this->makeReqUrl(array_shift($urls))); 
                curl_multi_add_handle($mcurl, $ch);
                $threads_running++; 
            }
            
            // check if all are finished
            if ($threads_running == 0 && count($urls) == 0) {
                break; 
            }
            
            // let cURL's threads run
            curl_multi_select($mcurl);
            while(($mc_res = curl_multi_exec($mcurl, $threads_running)) == CURLM_CALL_MULTI_PERFORM) {
                usleep(50000); 
            }

            if($mc_res != CURLM_OK) {
                break; 
            }

            while($done = curl_multi_info_read($mcurl)) { 
                $ch = $done['handle']; 
                $done_url       = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); 
                $done_content   = curl_multi_getcontent($ch); 

                if(curl_errno($ch) == 0) { 
                    $oembed = json_decode($done_content);
                    $args = array_merge(array($oembed, $done_url), $callback_extra_args);
                    
                    call_user_func_array(
                        array($callback_class_name, $callback_static_method),
                        $args
                    );
                } else { 
                    echo "Link $done_url failed: " . curl_error($ch) . "\n";
                }

                curl_multi_remove_handle($mcurl, $ch);
                curl_close($ch); 
                $threads_running--; 
            } 
        }
        
        curl_multi_close($mcurl);
    }
    
    protected function jsonRequest($url) {
        $req = curl_init($url);
        
        curl_setopt($req, CURLOPT_USERAGENT, self::UserAgent);
        // return the response as a string
        curl_setopt($req, CURLOPT_RETURNTRANSFER, 1); 
        
        $response = curl_exec($req);
        
        if($response === false) {
            $error_code = curl_getinfo($req, CURLINFO_HTTP_CODE);
            throw new EmbedlyHTTPErrorException($error_code);
        }
        
        curl_close($req);
               
        return json_decode($response);
    }
    
    protected function log($message) {
        if($this->logger) {
            $this->logger->logStatus($message, self::LoggerName);
        }
    }
    
    protected function makeReqUrl($url) {
        return self::OEmbedEndpoint . '?url=' . urlencode($url);
    }
}
