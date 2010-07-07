<?php
require_once 'config.tests.inc.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once 'model/class.Config.php';
require_once 'model/class.Database.deprecated.php';
require_once 'model/class.MySQLDAO.deprecated.php';
require_once 'model/class.PDODAO.php';
require_once 'model/class.User.php';
require_once 'model/class.Owner.php';
require_once 'model/class.Post.php';
require_once 'model/class.Link.php';
require_once 'model/class.Instance.php';
require_once 'model/class.OwnerInstance.php';
require_once 'model/class.PluginHook.php';
require_once 'model/class.Crawler.php';
require_once 'model/class.Utils.php';
require_once 'model/class.Profiler.php';
require_once 'model/class.Session.php';

require_once 'model/class.Logger.php';

require_once 'config.inc.php';
require_once $THINKTANK_CFG['smarty_path'].'Smarty.class.php';
require_once 'model/class.SmartyThinkTank.php';
?>