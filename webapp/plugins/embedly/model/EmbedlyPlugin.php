<?php
if (!class_exists('OEmbed')) {
    $config = Config::getInstance();
    require_once $config->getValue('source_root_path') . 'extlib/php-oembed/config.php';
}

class EmbedlyPlugin implements CrawlerPlugin {

    const PluginName    = "Embed.ly Plugin";
    const OEmbedFormat  = 'object';
    const NumToEmbed    = 200;

    public function crawl() {
        $logger = Logger::getInstance();
        $ldao   = EmbedlyDAOFactory::getDAO('EmbedlyLinkDAO');

        //TODO Set limit on total number of links to expand per crawler run in the plugin settings, now set here to 1500
        $links_to_embed = $ldao->getLinksToEmbed(self::NumToEmbed);
        $logger->logStatus(count($links_to_embed)." links to expand", self::PluginName);

        foreach ($links_to_embed as $l) {
            // $eurl = self::untiny_url($l, $ldao);
            // if ($eurl != '') {
            //     $ldao->saveEmbed($l, $eurl);
            // }
            var_dump($l);
        }
        $logger->logStatus("URL expansion complete for this run", self::PluginName);
        $logger->close(); # Close logging
    }

    public function renderConfiguration($owner) {
        //TODO: Write controller class, echo its results

    }
}