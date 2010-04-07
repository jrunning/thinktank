<?php
session_start();

if (!isset($_SESSION['user'])) {
	require_once('public.php');
	die();

} else {

	require_once("common/init.php");

	$od = new OwnerDAO($db);
	$owner = $od->getByEmail($_SESSION['user']);

	$id = new InstanceDAO($db);

	$s = new SmartyThinkTank();

	if ( isset($_REQUEST['u']) && $id->isUserConfigured($_REQUEST['u']) ){
		$username = $_REQUEST['u'];
		$oid = new OwnerInstanceDAO($db);
		if ( !$oid->doesOwnerHaveAccess($owner, $username) ) {
			echo 'Insufficient privileges. <a href="/">Back</a>.';
			$db->closeConnection($conn);
			die;
		} else {
			$i = $id->getByUsername($username);
		}
	} else {
		$i = $id->getFreshestByOwnerId($owner->id);
		if ( !isset($i) && $i == null ) {
			$s->assign('msg', 'You have no Twitter accounts configured. <a href="'.$THINKTANK_CFG['site_root_path'].'account/?p=twitter">Set up a Twitter account here</a>');
			$s->display('message.tpl');
			
			$db->closeConnection($conn);
			die;
		}
	}
	
	// save the session instance network username to the current instance
	$_SESSION['network_username']=$i->network_username;
    $_SESSION['instance'] = serialize($i);

	if(!$s->is_cached('index.tpl', $i->network_username."-".$_SESSION['user'])) {

		$cfg = new Config($i->network_username, $i->network_user_id);

		$u = new Utils();

		// instantiate data access objects
		$ud = new UserDAO($db);
		$pd = new PostDAO($db);
		$fd = new FollowDAO($db);

		// pass data to smarty
		$owner_stats = $ud->getDetails($i->network_user_id);
		$s->assign('owner_stats', $owner_stats);

		$s->assign('instance', $i);
		$s->assign('instances', $id->getByOwner($owner));
		$s->assign('cfg', $cfg);

		$total_follows_with_errors = $fd->getTotalFollowsWithErrors($cfg->twitter_user_id);
		$s->assign('total_follows_with_errors', $total_follows_with_errors);

		$total_follows_with_full_details = $fd->getTotalFollowsWithFullDetails($cfg->twitter_user_id);
		$s->assign('total_follows_with_full_details', $total_follows_with_full_details);

		$total_follows_protected = $fd-> getTotalFollowsProtected($cfg->twitter_user_id);
		$s->assign('total_follows_protected', $total_follows_protected);

		//TODO: Get friends with full details and also friends with errors, same as with followers
		$total_friends_loaded = $fd->getTotalFriends($cfg->twitter_user_id);
		$s->assign('total_friends', $total_friends_loaded);

		$total_friends_with_errors = $fd->getTotalFriendsWithErrors($cfg->twitter_user_id);
		$s->assign('total_friends_with_errors', $total_friends_with_errors);

		$total_friends_protected = $fd->getTotalFriendsProtected($cfg->twitter_user_id);
		$s->assign('total_friends_protected', $total_friends_protected);

		//Percentages
		$percent_followers_loaded = $u->getPercentage($owner_stats->follower_count, ($total_follows_with_full_details + $total_follows_with_errors));
		$percent_followers_loaded = ($percent_followers_loaded  > 100) ? 100 : $percent_followers_loaded;
		$percent_tweets_loaded = $u->getPercentage($owner_stats->post_count,$i->total_posts_in_system );
		$percent_tweets_loaded = ($percent_tweets_loaded  > 100) ? 100 : $percent_tweets_loaded;

		$percent_friends_loaded = $u->getPercentage($owner_stats->friend_count, ($total_friends_loaded));
		$percent_friends_loaded = ($percent_friends_loaded  > 100) ? 100 : $percent_friends_loaded;

		$percent_followers_suspended = round($u->getPercentage($total_follows_with_full_details, $total_follows_with_errors), 2);
		$percent_followers_protected = round($u->getPercentage($total_follows_with_full_details, $total_follows_protected), 2);

		$s->assign('percent_followers_loaded', $percent_followers_loaded);
		$s->assign('percent_tweets_loaded', $percent_tweets_loaded);
		$s->assign('percent_friends_loaded', $percent_friends_loaded);
		$s->assign('percent_followers_suspended', $percent_followers_suspended);
		$s->assign('percent_followers_protected', $percent_followers_protected);

	}

	# clean up
	$db->closeConnection($conn);

	$s->display('index.tpl', $i->network_username."-".$_SESSION['user']);

}
?>
