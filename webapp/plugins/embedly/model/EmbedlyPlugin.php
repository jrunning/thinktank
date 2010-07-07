<?php
require_once 'Embedly/class.EmbedlyClient.php';

class EmbedlyPlugin implements CrawlerPlugin {

    const PluginName = 'Embed.ly Plugin';
    const NumToEmbed = 500;    

    static $check_services_first = true;

    public function crawl() {
        $logger = Logger::getInstance();
        $edao   = EmbedlyDAOFactory::getDAO('EmbedlyDAO');

        $links_to_embed = $edao->getLinksToEmbed(self::NumToEmbed);
        $embedly = new EmbedlyClient();
        
        $logger->logStatus(count($links_to_embed) . " links for Embed.ly", self::PluginName);

        foreach ($links_to_embed as $link) {
            $logger->logStatus("Trying $link[url] (link_id $link[id])", self::PluginName);
            
            if(!self::$check_services_first || $service_match = $embedly->getServicesMatch($link['url'])) {
                if(self::$check_services_first) {
                    $logger->logStatus('  - "' . $service_match->displayname
                        . "\" matched $link[url]; Asking Embed.ly for "
                        . 'OEmbed data',
                        self::PluginName
                    );
                }

                try {
                    if($oembed = $embedly->getOEmbedForURL($link['url'])) {                
                        if($insert_id = $edao->insert($link['id'], $oembed)) {
                            $logger->logStatus("  - Inserted OEmbed data for $link[url]; embedly_embed_id $insert_id", self::PluginName);
                        } else {
                            $logger->logStatus("  - Error inserting OEmbed data for $link[url]", self::PluginName);
                        }
                    } else {
                        $logger->logStatus("  - Embed.ly returned nothing for $link[url]", self::PluginName);
                    }
                } catch(EmbedlyHTTPErrorException $ex) {
                    $logger->logStatus('  - Embed.ly API returned an error: ' . $ex->getMessage(), self::PluginName);
                }
            } else {
                $logger->logStatus("  - No Services match for $link[url]--skipping", self::PluginName);
            }
            self::updateEmbedlyLinkCheckedAt($link['id'], $edao);
        }

        $logger->logStatus("Embed.ly API calls complete for this run", self::PluginName);
        $logger->close(); # Close logging
    }

    public function renderConfiguration($owner) {
        //TODO: Write controller class, echo its results
    }
    
    protected function updateEmbedlyLinkCheckedAt($link_id, $edao) {
        $edao->setLinkEmbedlyCheckedAt($link_id);     
    }
}