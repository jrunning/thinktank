<?php
/**
 * Embed.ly Link Data Access Object Interface
 *
 * @author Jordan Running <jordan[at]jordanrunning[dot]com>
 */

$config = Config::getInstance();
require_once $config->getValue('source_root_path') . 'webapp/model/interface.LinkDAO.php';

interface EmbedlyLinkDAO extends LinkDAO {
  /**
   * Gets links that need to be resolved by Embed.ly
   * @param int $limit
   * @return array with numbered keys, with strings
   */
  public function getLinksToEmbed($limit = 200);
}