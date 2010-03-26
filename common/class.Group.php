<?php
class Group {
    var $id;
    var $name;
    var $full_name;
    var $slug;
    var $description;
    var $subscriber_count;
    var $member_count;
    var $uri;
    var $mode;
    
    var $owner;
    
    function __construct($params) {
        $this->id = $params['id'];
        $this->name = $params['name'];
        $this->full_name = $params['full_name'];
        $this->slug = $params['slug'];
        $this->description = $params['description'];
        $this->subscriber_count = $params['subscriber_count'];
        $this->member_count = $params['member_count'];
        $this->uri = $params['uri'];
        $this->mode = $params['mode'];
    }
}

class GroupDAO extends MySQLDAO {
    const TableName = 'groups';

    function getGroup($group_id) {
        $query = '  SELECT t.*
                    FROM #prefix#' . self::TableName . ' t
                    WHERE id = ' . mysql_real_escape_string($group_id) . ';
                ';
        
        $sql_result = $this->executeSQL($query);
        $group = new Group(mysql_fetch_assoc($sql_result));
        mysql_free_result($sql_result); # Free up memory

        return $group;
    }

    
}
?>