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
    const NumToEmbed    = 500;
    const OEmbedEndpoint    = 'http://api.embed.ly/v1/api/oembed';
    const ServicesEndpoint  = 'http://api.embed.ly/v1/api/services/php';
    const CheckServicesFirst = true;

    public function crawl() {
        $logger = Logger::getInstance();
        $ldao   = EmbedlyDAOFactory::getDAO('EmbedlyDAO');

        //TODO Set limit on total number of links to expand per crawler run in the plugin settings, now set here to 1500
        $links_to_embed = $ldao->getLinksToEmbed(self::NumToEmbed);
        $logger->logStatus(count($links_to_embed) . " links for Embed.ly", self::PluginName);

        if(self::CheckServicesFirst) {
            $services = self::getServices();
        }

        foreach ($links_to_embed as $link) {
            try {
                $logger->logStatus("Trying $link[url]", self::PluginName);
                
                if(!self::CheckServicesFirst || $match = self::getUrlMatch($link['url'], $services)) {
                    if(self::CheckServicesFirst) {
                        $logger->logStatus($match . " matched " . $link['url'], self::PluginName);
                    }
                    
                    $logger->logStatus("Asking Embed.ly about $link[url]", self::PluginName);
                
                    $oEmbed = new Services_oEmbed($link['url'], array(
                        Services_oEmbed::OPTION_API => self::OEmbedEndpoint
                    ));

                    $object = $oEmbed->getObject();
                    if($ldao->insert($link['id'], $object)) {
                        $logger->logStatus("Inserted embed data for $link[url]", self::PluginName);
                    }
                } else {
                    $logger->logStatus("No URL match for $link[url]", self::PluginName);
                }
                
                self::updateLinkEmbedlyCheckedAt($link['id'], $ldao);
            } catch (Services_oEmbed_Exception $ex) {
                $logger->logStatus($ex->getMessage(), self::PluginName);
            }
        }

        $logger->logStatus("Embed.ly API calls complete for this run", self::PluginName);
        $logger->close(); # Close logging
    }

    public function renderConfiguration($owner) {
        //TODO: Write controller class, echo its results

    }
    
    protected function getServices() {
        $services_resp = file_get_contents(self::ServicesEndpoint);
        return json_decode($services_resp);
    }
    
    protected function getUrlMatch($url, &$services) {
        foreach($services as $service) {
            foreach($service->regex as $reg) {
                if(preg_match($reg, $url)) {
                    return $service->name;
                }
            }
        }
        
        return false;
    }
    
    protected function updateLinkEmbedlyCheckedAt($link_id, &$ldao) {
        $ldao->setLinkEmbedlyCheckedAt($link_id);        
    }
}