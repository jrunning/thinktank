<?php
require_once 'class.EmbedlyExceptions.php';

class EmbedlyClient {
    const LoggerName        = 'Embedly';
    const OEmbedEndpoint    = 'http://api.embed.ly/v1/api/oembed';
    const ServicesEndpoint  = 'http://api.embed.ly/v1/api/services/php';
    const OEmbedFormat      = 'object';
    
    private $services = array();
    private $check_services_first = true;
    private $dao;
    private $logger;

    public function __construct(EmbedlyDAO $dao, $logger = null) {
        $this->logger   = $logger;
        $this->dao      = $dao;
        $this->loadServices();
    }
    
    public function loadServices() {
        $this->services = $this->jsonRequest(self::ServicesEndpoint);
        return count($this->services);
    }
    
    public function oEmbedRequest($url) {
        $req_url = self::OEmbedEndpoint . '?url=' . urlencode($url);
        
        $resp = $this->jsonRequest($req_url);
    }
    
    public function checkLink($link) {
        if(!$this->check_services_first || $match = $this->getUrlMatch($link['url'])) {
            if($this->check_services_first) {
                $this->log("  - $match matched $link[url]");
            }
            
            $this->log("  - Asking Embed.ly OEmbed about $link[url]");
        
            /* $oEmbed = new Services_oEmbed($link['url'], array(
                Services_oEmbed::OPTION_API => self::OEmbedEndpoint
            ));

            $object = $oEmbed->getObject();
            
            */
            $oembed = $this->oEmbedRequest($link['url']);
            if($this->dao->insert($link['id'], $oembed)) {
                $this->log("  - Inserted embed data for $link[url]");
            }
        } else {
            $this->log("  - No URL match for $link[url]");
        }
    }
    
    protected function getUrlMatch($url) {
        foreach($this->services as $service) {
            foreach($service->regex as $reg) {
                if(preg_match($reg, $url)) {
                    return $service->name;
                }
            }
        }
        
        return false;
    }
    
    protected function jsonRequest($url) {
        $response = file_get_contents($url);
        
        if($response === false) {
            // $http_response_header is a magic variable populated by file_get_contents()
            // Get "200 OK" from "HTTP/1.1 200 OK"
            $error_code = array_pop(explode(' ', $http_response_header, 2));
            
            throw new HTTPErrorException($error_code);
        }
        
        return json_decode($response);
    }
    
    protected function log($message) {
        if($this->logger) {
            $this->logger->logStatus($message, self::LoggerName);
        }
    }
}
