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
    private $check_services_first = true;
    private $dao;
    private $logger;

    public function __construct(EmbedlyDAO $dao, $logger = null) {
        ini_set('user_agent', self::UserAgent);
    
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

            try {
                $oembed = $this->oEmbedRequest($link['url']);
                
                # TODO:  Return this instead of inserting--this should happen in ../EmbedlyPlugin.php;
                #        EmbedlyClient shouldn't do database access.
                if($this->dao->insert($link['id'], $oembed)) {
                    $this->log("  - Inserted embed data for $link[url]");
                }
            } catch(EmbedlyHTTPErrorException $e) {
                $this->log('  - Embed.ly returned HTTP error: ' . $e->getMessage());
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
