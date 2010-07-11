<?php
require_once 'Embedly/class.EmbedlyClient.php';

class EmbedlyPlugin implements CrawlerPlugin {
    
    const ClassName     = 'EmbedlyPlugin';
    const PluginName    = 'Embed.ly Plugin';
    const NumToEmbed    = 500;    

    static $check_services_first = true;

    public function crawl() {
        $logger = Logger::getInstance();
        $edao   = EmbedlyDAOFactory::getDAO('EmbedlyDAO');
        $ldao   = DAOFactory::getDAO('LinkDAO');

        //TODO Set limit on total number of links to expand per crawler run in the plugin settings, now set here to 1500
        $links = $edao->getLinksToEmbed(self::NumToEmbed);
        $embedly = new EmbedlyClient();

        $links_to_embed = array();

        foreach($links as $link) {
            if(self::$check_services_first) {
                if($embedly->getServicesMatch($link['url'])) {
                    $links_to_embed[$link['id']] = $link['url'];
                } else {
                    self::updateEmbedlyLinkCheckedAt($link['id'], $edao);
                }
            } else {
                $links_to_embed[$link['id']] = $link['url'];
            }
        }
        
        $logger->logStatus(count($links_to_embed) . ' of ' . count($links)
            . ' links for Embed.ly',
            self::PluginName
        );
        
        $curl_handler = new EmbedlyMultiCurlHandler($edao, $logger,
            self::PluginName);
       
        $embedly->batchedQuery($links_to_embed,
            $curl_handler, 'handleBatchedEmbedResult', 'handleBatchedEmbedError'
        );

        $logger->logStatus("Embed.ly API calls complete for this run",
            self::PluginName);
        $logger->close(); # Close logging
    }

    
    protected function updateEmbedlyLinkCheckedAt($link_id, $edao) {
        $edao->setLinkEmbedlyCheckedAt($link_id);     
    }
    
    public function renderConfiguration($owner) {
        //TODO: Write controller class, echo its results
    }
}

class EmbedlyMultiCurlHandler {
    private $edao;
    private $logger;
    private $logger_name;
    
    public function __construct($embedly_dao, $logger, $logger_name) {
        $this->edao = $embedly_dao;
        $this->logger = $logger;
        $this->loggerName = $logger_name;
    }
    
    public function handleBatchedEmbedResult($link_id, $oembed) {
        if($insert_id = $this->edao->insert($link_id, $oembed)) {
            $this->log( "  - Inserted OEmbed data for {$oembed->url}; " .
                        "embedly_embed_id $insert_id");
        } else {
            $this->log("  - Error inserting OEmbed data for {$oembed->url}");
        }
        
        $this->updateEmbedlyLinkCheckedAt($link_id);
    }
    
    public function handleBatchedEmbedError($link_id, $error_message) {
        $this->log( "  - Embed.ly returned an error for link_id $link_id: " .
                    "$error_message -- skipping");
    }
      
    protected function updateEmbedlyLinkCheckedAt($link_id) {
        $this->edao->setLinkEmbedlyCheckedAt($link_id);     
    }
    
    protected function log($message) {
        return $this->logger->logStatus($message, $this->logger_name);
    }
}