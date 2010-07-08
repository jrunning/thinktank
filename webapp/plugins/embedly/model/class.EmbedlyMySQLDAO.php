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
        $q  = " SELECT l.id, l.expanded_url AS url ";
        $q .= " FROM #prefix#links AS l ";
        $q .= " WHERE l.expanded_url IS NOT NULL ";
        $q .= "     AND l.expanded_url <> '' ";
        $q .= "     AND COALESCE(embedly_checked_at, 0) = 0";
        $q .= "     AND NOT EXISTS (";
        $q .= "         SELECT * ";
        $q .= "         FROM #prefix#" . self::TableName . " AS em ";
        $q .= "         WHERE em.link_id = l.id ";
        $q .= "     )" ;
        $q .= " GROUP BY l.url ";
        $q .= " LIMIT :limit ";
 
        $vars = array( ':limit' => $limit );
        $result = $this->execute($q, $vars);

        $rows = $this->getDataRowsAsArrays($result);
        
        return $rows;
    }
    
    public function insert($link_id, $oembed) {
        $param_names =  array(
            'type',             'author_name',      'provider_name',
            'provider_url',     'cache_age',        'thumbnail_url',
            'thumbnail_width',  'thumbnail_height', 'url',
            'width',            'height',           'html'
        );
        
        $q  = ' INSERT IGNORE INTO #prefix#' . self::TableName . ' ';
        $q .= ' (link_id, ' . implode(', ', $param_names) . ') ';
        $q .= ' VALUES ';
        $q .= ' (:link_id, :' . implode(', :', $param_names) . ' ) ';

        $vars = array('link_id' => $link_id);
        
        foreach($param_names as $param_name) {
            $vars[$param_name] = isset($oembed->{$param_name}) ?
                $oembed->{$param_name} : null;
        }
        
        $result = $this->execute($q, $vars);
        return $this->getInsertId($result);
    }
    
    public function setLinkEmbedlyCheckedAt($link_id) {
        $q  = " UPDATE #prefix#links ";
        $q .= " SET embedly_checked_at = NOW() ";
        $q .= " WHERE id = :link_id ";
        
        $vars = array('link_id' => $link_id);

        $result = $this->execute($q, $vars);
        return $this->getUpdateCount($result);
    }
}