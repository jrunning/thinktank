<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

// set up
chdir("..");


require_once 'init.php';

$od = DAOFactory::getDAO('OwnerDAO');
$owner = $od->getByEmail($_SESSION['user']);

$pd = DAOFactory::getDAO('PostDAO');
$id = DAOFactory::getDAO('InstanceDAO');

if ( isset($_REQUEST['u']) && $id->isUserConfigured($_REQUEST['u']) ){
    $username = $_REQUEST['u'];
    $oid = DAOFactory::getDAO('OwnerInstanceDAO');
    if ( !$oid->doesOwnerHaveAccess($owner, $username) ) {
        echo 'Insufficient privileges. <a href="/">Back</a>.';
        die;
    } else {
        $tweets = $pd->getAllPostsByUsername($username);
    }
} else {
    echo 'No access';
    die;
}

$s = new SmartyThinkTank();
$s->assign('tweets', $tweets);
$s->display('post.export.tpl', $username);
