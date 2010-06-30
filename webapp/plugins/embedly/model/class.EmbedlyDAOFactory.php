<?php
/**
 * Embed.ly Data Access Object Factory
 *
 * Inits a DAO based on the ThinkTank config db_type and $dao_mapping definitions.
 * db_type is defined in webapp/config.inc.php as:
 *
 *     $THINKTANK_CFG['db_type'] = 'somedb';
 *
 * Example of use:
 *
 * <code>
 *  DAOFactory::getDAO('SomeDAO');
 * </code>
 *
 * @author Jordan Running <jordan[at]jordanrunning[dot]com>
 */
class EmbedlyDAOFactory extends DAOFactory {

    /**
     * maps DAO from db_type and defines class names and path for initialization
     */     
    public static function getDAO($dao_key) {
        self::$dao_mapping['EmbedlyDAO'] = array(
            'mysql' => array(
                'class' => 'EmbedlyMySQLDAO',
                'path'  => 'plugins/embedly/model/class.EmbedlyMySQLDAO.php'
            )
        );
        
        return parent::getDAO($dao_key);
    }
}