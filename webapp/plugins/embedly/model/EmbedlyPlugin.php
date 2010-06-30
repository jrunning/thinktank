<?php

if (!class_exists('Services_oEmbed')) {
    $config = Config::getInstance();
    set_include_path(   get_include_path() . PATH_SEPARATOR .
                        $config->getValue('source_root_path') . 'extlib/pear'
                    );
    require_once 'Services/oEmbed.php';
}

class EmbedlyPlugin implements CrawlerPlugin {

    const PluginName    = 'Embed.ly Plugin';
    const OEmbedFormat  = 'object';
    const NumToEmbed    = 200;
    const APIEndpoint   = 'http://api.embed.ly/v1/api/oembed';

    public function crawl() {
        $logger = Logger::getInstance();
        $ldao   = EmbedlyDAOFactory::getDAO('EmbedlyDAO');

        //TODO Set limit on total number of links to expand per crawler run in the plugin settings, now set here to 1500
        $links_to_embed = $ldao->getLinksToEmbed(self::NumToEmbed);
        $logger->logStatus(count($links_to_embed)." links to expand", self::PluginName);

        foreach ($links_to_embed as $id => $url) {
            try {
                $logger->logStatus("Trying $url", self::PluginName);
                $oEmbed = new Services_oEmbed($url, array(
                    Services_oEmbed::OPTION_API => self::APIEndpoint
                ));

                $object = $oEmbed->getObject();
                if($ldao->insert($id, $object)) {
                    $logger->logStatus("Inserted embed data for $url");
                }
            } catch (Services_oEmbed_Exception $ex) {
                $logger->logStatus($ex->getMessage(), self::PluginName);
            }
        }

        $logger->logStatus("URL expansion complete for this run", self::PluginName);
        $logger->close(); # Close logging
    }

    public function renderConfiguration($owner) {
        //TODO: Write controller class, echo its results

    }
}