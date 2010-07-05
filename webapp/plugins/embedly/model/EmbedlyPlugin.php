<?php
require_once 'Embedly/class.EmbedlyClient.php';

class EmbedlyPlugin implements CrawlerPlugin {

    const PluginName = 'Embed.ly Plugin';
    const NumToEmbed = 500;

    public function crawl() {
        $logger = Logger::getInstance();
        $edao   = EmbedlyDAOFactory::getDAO('EmbedlyDAO');

        //TODO Set limit on total number of links to expand per crawler run in the plugin settings, now set here to 1500
        $links_to_embed = $edao->getLinksToEmbed(self::NumToEmbed);
        $embedly = new EmbedlyClient($edao, $logger);
        
        $logger->logStatus(count($links_to_embed) . " links for Embed.ly", self::PluginName);

        foreach ($links_to_embed as $link) {
            try {
                $logger->logStatus("Trying $link[url]", self::PluginName);
                
                $embedly->checkLink($link);
                self::updateEmbedlyLinkCheckedAt($link['id'], $edao);
            } catch (Services_oEmbed_Exception $ex) {
                $logger->logStatus("  - Error: " . $ex->getMessage(), self::PluginName); 
            }
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