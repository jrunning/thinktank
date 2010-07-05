<?php
/**
 * Embed.ly Link Data Access Object Interface
 *
 * @author Jordan Running <jordan[at]jordanrunning[dot]com>
 */

$config = Config::getInstance();
require_once $config->getValue('source_root_path') . 'webapp/model/interface.LinkDAO.php';

interface EmbedlyDAO {
  /**
   * Gets links that need to be resolved by Embed.ly
   * @param int $limit
   * @return array with numbered keys, with strings
   */
  public function getLinksToEmbed($limit = 200);

  /**
   * Gets links that need to be resolved by Embed.ly
   * @param int $link_id
   * @param Services_oEmbed_Object_Common $obj
   * @return id of inserted record
   */
  public function insert($link_id, $oembed);
  
  /**
   * Sets the embedly_checked_at property for a link
   * @param int $link_id
   * @return Number of link objects updated
   */
  public function setLinkEmbedlyCheckedAt($link_id);
}