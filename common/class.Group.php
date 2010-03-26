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

    public function getGroup($group_id) {
        $query = '  SELECT t.*
                    FROM #prefix#' . self::TableName . ' t
                    WHERE id = ' . mysql_real_escape_string($group_id) . ';
                ';
        
        $sql_result = $this->executeSQL($query);
        $group = new Group(mysql_fetch_assoc($sql_result));
        mysql_free_result($sql_result); # Free up memory

        return $group;
    }

    public function groupExists($group_id) {
        $query = '  SELECT group_id
                    FROM #prefix#' . self::TableName . ' t
                    WHERE group_id = ' . mysql_real_escape_string($group_id) . ';
                ';
                
        $result = $this->executeSQL($query);
        
        return mysql_num_rows($sql_result) > 0);
    }
    
    public function updateGroup($row) {
        $status_message = '';
        
        $query = '
            INSERT INTO #prefix#' . self::TableName . ' t
                (id,    name,       full_name,      slug,   description,
                 subscriber_count,  member_count,   uri,    mode)
            VALUES
                (%1$s,  %2$s,       %3$s,           %4$s,   %5$s,
                 %6$s,              %7$s,           %8$s,   %9$s)
            ON DUPLICATE KEY UPDATE
                name = %2$s,                full_name = %3$s,
                slug = %4$s,                description = %5$s
                subscriber_count = %6$s,    member_count = %7$s,
                uri = %8$s,                 mode = %9$s;
        ';
        
        $this->executeSQL(vsprintf($query, self::EscapeParams($row));

        if (mysql_affected_rows() > 0) {
            if(isset($this->logger) && $this->logger != null) {
                $status_message = 'List ' . $row['slug'] . ' updated.';
                $this->logger->logStatus($status_message, get_class($this));
            }
            return 1;
        } else {
            if(isset($this->logger) && $this->logger != null) {
                $status_message = 'User ' . $row['slug'] . ' was NOT updated.';
                $this->logger->logStatus($status_message, get_class($this));
            }
            return 0;
        }
    }
}
?>