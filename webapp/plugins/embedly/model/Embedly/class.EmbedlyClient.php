<?php
require_once 'class.EmbedlyExceptions.php';

class EmbedlyClient {
    const LoggerName        = 'Embed.ly Plugin';
    const OEmbedEndpoint    = 'http://api.embed.ly/v1/api/oembed';
    const ServicesEndpoint  = 'http://api.embed.ly/v1/api/services/php';
    const OEmbedFormat      = 'object';
    const UserAgent         =
        'Mozilla/5.0 (compatible; ThinkTank Embed.ly Plugin/0.1; +http://github.com/jrunning/thinktank/tree/embedly-plugin/webapp/plugins/embedly/)';
    
    private $services = array();

    public function __construct() {
        ini_set('user_agent', self::UserAgent);
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
    
    protected function jsonRequest($url) {
        $response = file_get_contents($url);
        
        if($response === false) {
            // $http_response_header is a magic variable populated by file_get_contents()
            // Get "200 OK" from "HTTP/1.1 200 OK"
            $error_code = array_pop(explode(' ', $http_response_header[0], 2));
            
            throw new EmbedlyHTTPErrorException($error_code);
        }
        
        return json_decode($response);
    }
    
    protected function log($message) {
        if($this->logger) {
            $this->logger->logStatus($message, self::LoggerName);
        }
    }
}
