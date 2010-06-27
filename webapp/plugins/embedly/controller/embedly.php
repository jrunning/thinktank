<?php 
/* 
 Plugin Name: Embed.ly
 Plugin URI: http://github.com/jrunning/thinktank/tree/master/webapp/plugins/embedly/
 Description: Plugin to integrate Embed.ly API
 Version: 0.01
 Icon: assets/img/plugin_icon.png
 Author: Jordan Running
 */

$webapp = Webapp::getInstance();
$webapp->registerPlugin('embedly', 'EmbedlyPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('EmbedlyPlugin');
