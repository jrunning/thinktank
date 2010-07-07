<?php
require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankTestDatabaseHelper.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.MySQLDAO.deprecated.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Database.deprecated.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.LoggerSlowSQL.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';

class ThinkTankWebTestCase extends WebTestCase {
    var $db;
    var $conn;
    var $testdb_helper;
    var $url;

    function setUp() {
        global $THINKTANK_CFG;
        global $TEST_DATABASE;
        global $TEST_SERVER_DOMAIN;

        $this->url = $TEST_SERVER_DOMAIN;

        //Override default CFG values
        $THINKTANK_CFG['db_name'] = $TEST_DATABASE;

        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();

        $this->testdb_helper = new ThinkTankTestDatabaseHelper();
        $this->testdb_helper->create($this->db);
    }

    function tearDown() {
        $this->testdb_helper->drop($this->db);
        $this->db->closeConnection($this->conn);
    }
}
?>
