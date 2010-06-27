<?php
class EmbedlyPlugin implements CrawlerPlugin {

    public function renderConfiguration($owner) {
        $controller = new EmbedlyPluginConfigurationController($owner);
        return $controller->go();
    }

    public function crawl() {
        //echo "Embed.ly crawler plugin is running now.";
    }
}
