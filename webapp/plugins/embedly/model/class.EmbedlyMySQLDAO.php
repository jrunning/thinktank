<?php
/**
 * Embedly Link MySQL Data Access Object
 *
 * @author Jordan Running <jordan[at]jordanrunning[dot]com>
 */
$config = Config::getInstance();
require_once 'interface.EmbedlyDAO.php';

class EmbedlyMySQLDAO extends PDODAO implements EmbedlyDAO {
    const TableName = 'embedly_embeds';

    public function getLinksToEmbed($limit = 200) {
        $q  = " SELECT l.url AS url ";
        $q .= " FROM #prefix#links AS l ";
        $q .= " WHERE NOT EXISTS (";
        $q .= "     SELECT * ";
        $q .= "     FROM #prefix#" . self::TableName . " AS em ";
        $q .= "     WHERE em.link_id = l.id ";
        $q .= " )" ;
        $q .= " GROUP BY l.url ";
        $q .= " LIMIT :limit ";
 
        $vars = array( ':limit' => $limit );
        $result = $this->execute($q, $vars);

        $rows = $this->getDataRowsAsArrays($result);
        $urls = array();
        foreach($rows as $row){
            $urls[] = $row['url'];
        }
        return $urls;
    }
    
    public function insert($link_id, Services_oEmbed_Object_Common $obj) {
        $q  = " INSERT IGNORE INTO #prefix#" . self::TableName . " ";
        $q .= " (   link_id, type, title, author_name, author_url, ";
        $q .= "     provider_name, provider_url, cache_age, thumbnail_url, ";
        $q .= "     thumbnail_width, thumbnail_height, url, width, height, ";
        $q .= "     html )";
        $q .= " VALUES (:link_id, :type, :title, :author_name, :author_url, ";
        $q .= "         :provider_name, :provider_url, :cache_age, ";
        $q .= "         :thumbnail_url, :thumbnail_width, :thumbnail_height, ";
        $q .= "         :url, :width, :height, :html ) ";
        
        $vars = array(
            'link_id'            => $link_id,
            'type'               => $obj->type,
            'title'              => $obj->title,
            'author_name'        => $obj->author_name,
            'author_url'         => $obj->author_url,
            'provider_name'      => $obj->provider_name,
            'provider_url'       => $obj->provider_url,
            'cache_age'          => $obj->cache_age,
            'thumbnail_url'      => $obj->thumbnail_url,
            'thumbnail_width'    => $obj->thumbnail_width,
            'thumbnail_height'   => $obj->thumbnail_height,
            'url'                => $obj->url,
            'width'              => $obj->url,
            'height'             => $obj->height,
            'html'               => $obj->html
        );
        
        $result = $this->execute($q, $vars);
        return $this->getInsertId($result);
    }
}