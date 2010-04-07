<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.Post.php");
require_once ("common/class.Link.php");

class TestOfLinkDAO extends ThinkTankUnitTestCase {
    function TestOLinkDAO() {
        $this->UnitTestCase('LinkDAO class test');
    }
    
    function setUp() {
        parent::setUp();
        
        //Insert test links (not images, not expanded)
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            
            $q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) VALUES ('http://example.com/".$counter."', 'Link $counter', 0, $post_id, 0);";
            $this->db->exec($q);
            
            $counter++;
        }
        
        //Insert test links (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            
            $q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) VALUES ('http://flic.kr/p/".$counter."', 'Link $counter', 0, $post_id, 1);";
            $this->db->exec($q);
            
            $counter++;
        }
        
        //Insert test links with errors (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            
            $q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image, error) VALUES ('http://flic.kr/p/".$counter."', 'Link $counter', 0, $post_id, 1, 'Generic test error message, Photo not found');";
            $this->db->exec($q);
            
            $counter++;
        }
        
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function testGetLinksToExpand() {
        $ldao = new LinkDAO($this->db, $this->logger);
        
        $linkstoexpand = $ldao->getLinksToExpand();
        
        $this->assertEqual(count($linkstoexpand), 45);
    }
    
    function testGetLinkById() {
        $ldao = new LinkDAO($this->db, $this->logger);
        $link = $ldao->getLinkById(1);
        
        $this->assertEqual($link->id, 1);
        $this->assertEqual($link->url, 'http://example.com/0');
    }
    
    function testSaveExpandedUrl() {
        $ldao = new LinkDAO($this->db, $this->logger);
        $linkstoexpand = $ldao->getLinksToExpand();
        
        $link = $linkstoexpand[0];
        $ldao->saveExpandedUrl($link->id, "http://expandedurl.com");
        
        $updatedlink = $ldao->getLinkById($link->id);
        $this->assertEqual($updatedlink->expanded_url, "http://expandedurl.com");
        
        $ldao->saveExpandedUrl($link->id, "http://expandedurl1.com", 'my title');
        $updatedlink = $ldao->getLinkById($link->id);
        $this->assertEqual($updatedlink->expanded_url, "http://expandedurl1.com");
        $this->assertEqual($updatedlink->title, "my title");
        
        $ldao->saveExpandedUrl($link->id, "http://expandedurl2.com", 'my title1', 1);
        $updatedlink = $ldao->getLinkById($link->id);
        $this->assertEqual($updatedlink->expanded_url, "http://expandedurl2.com");
        $this->assertEqual($updatedlink->title, "my title1");
        $this->assertTrue($updatedlink->is_image);
    }
    
    function testSaveExpansionError() {
        $ldao = new LinkDAO($this->db, $this->logger);
        $linktogeterror = $ldao->getLinkById(10);
        
        $this->assertEqual($linktogeterror->error, '');
        $ldao->saveExpansionError(10, "This is expansion error text");
        
        $linkthathaserror = $ldao->getLinkById(10);
        $this->assertEqual($linkthathaserror->error, "This is expansion error text");
    }
    
    function testGetLinksToExpandByURL() {
        $ldao = new LinkDAO($this->db, $this->logger);
        $flickrlinkstoexpand = $ldao->getLinksToExpandByUrl('http://flic.kr/');
        
        $this->assertEqual(count($flickrlinkstoexpand), 5);
    }
    
}
?>
