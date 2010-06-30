<?php
/**
 * Embedly Link MySQL Data Access Object
 *
 * @author Jordan Running <jordan[at]jordanrunning[dot]com>
 */
$config = Config::getInstance();
require_once $config->getValue('source_root_path') . 'webapp/model/class.LinkMySQLDAO.php';
require_once 'interface.EmbedlyLinkDAO.php';

class EmbedlyLinkMySQLDAO extends LinkMySQLDAO implements EmbedlyLinkDAO {
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
 
        $vars = array( ':limit' => $limit );
        $result = $this->execute($q, $vars);

        $rows = $this->getDataRowsAsArrays($result);
        $urls = array();
        foreach($rows as $row){
            $urls[] = $row['url'];
        }
        return $urls;
    }

}