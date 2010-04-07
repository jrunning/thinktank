<?php 
class TwitterAPIAccessorOAuth {
    var $available = true;
    var $next_api_reset = null;
    var $cURL_source;
    var $to;
    var $oauth_access_token;
    var $oauth_access_token_secret;
    var $next_cursor;    
       
    # Define how to access Twitter API
    const APIDomain = 'https://twitter.com';
    const APIFormat = 'xml';
    const SearchDomain = 'http://search.twitter.com';
    const SearchFormat = 'atom';

    # Define method paths ... [id] is a placeholder
    private static $MethodPaths = array(
        "end_session"       => "/account/end_session",
        "rate_limit"        => "/account/rate_limit_status",
        "delivery_device"   => "/account/update_delivery_device",
        "location"          => "/account/update_location",
        "profile"           => "/account/update_profile",
        "profile_background"    => "/account/update_profile_background_image",
        "profile_colors"    => "/account/update_profile_colors",
        "profile_image"     => "/account/update_profile_image",
        "credentials"       => "/account/verify_credentials",
        "block"             => "/blocks/create/[id]",
        "remove_block"      => "/blocks/destroy/[id]",
        "messages_received" => "/direct_messages",
        "delete_message"    => "/direct_messages/destroy/[id]",
        "post_message"      => "/direct_messages/new",
        "messages_sent"     => "/direct_messages/sent",
        "bookmarks"         => "/favorites/[id]",
        "create_bookmark"   => "/favorites/create/[id]",
        "remove_bookmark"   => "/favorites/destroy/[id]",
        "followers_ids"     => "/followers/ids",
        "following_ids"     => "/friends/ids",
        "follow"            => "/friendships/create/[id]",
        "unfollow"          => "/friendships/destroy/[id]",
        "confirm_follow"    => "/friendships/exists",
        "show_friendship"   => "/friendships/show",
        "test"              => "/help/test",
        "turn_on_notification"  => "/notifications/follow/[id]",
        "turn_off_notification" => "/notifications/leave/[id]",
        "delete_tweet"      => "/statuses/destroy/[id]",
        "followers"         => "/statuses/followers",
        "following"         => "/statuses/friends",
        "friends_timeline"  => "/statuses/friends_timeline",
        "public_timeline"   => "/statuses/public_timeline",
        "mentions"          => "/statuses/mentions",
        "show_tweet"        => "/statuses/show/[id]",
        "post_tweet"        => "/statuses/update",
        "user_timeline"     => "/statuses/user_timeline/[id]",
        "show_user"         => "/users/show/[id]",
        "retweeted_by_me"   => "/statuses/retweeted_by_me",
        "lists"             => "/lists"
    );
    
    const DateFormat = "Y-m-d H:i:s";
    
    function TwitterAPIAccessorOAuth($oauth_access_token, $oauth_access_token_secret, $oauth_consumer_key, $oauth_consumer_secret) {
        $this->$oauth_access_token = $oauth_access_token;
        $this->$oauth_access_token_secret = $oauth_access_token_secret;
        
        $this->to = new TwitterOAuth($oauth_consumer_key, $oauth_consumer_secret, $this->$oauth_access_token, $this->$oauth_access_token_secret);
        $this->cURL_source = $this->prepAPI();
    }
    
    function verifyCredentials() {
        //returns user array; -1 if not.
        $auth = $this->cURL_source['credentials'];
        list($cURL_status, $twitter_data) = $this->apiRequestFromWebapp($auth);
        if ($cURL_status == 200) {
            $user = $this->parseXML($twitter_data);
            return $user[0];
        } else {
            return - 1;
        }
    }
    
    function apiRequestFromWebapp($url) {
        $content = $this->to->OAuthRequest($url, array(), 'GET');
        $status = $this->to->lastStatusCode();
        return array($status, $content);
    }
    
    function prepAPI() {
        # Construct cURL sources
        $urls = array();
        foreach (self::$MethodPaths as $method => $path) {
            $urls[$method] = self::APIDomain . $path . "." . self::APIFormat;
        }
        $urls['search']     = self::SearchDomain . "/search." . self::SearchFormat;
        $urls['search_web'] = self::SearchDomain . "/search";
        $urls['trends']     = self::SearchDomain . "/trends.json";
        
        return $urls;
    }
    
    function parseFeed($url, $date = 0) {
        $thisFeed = array();
        $feed_title = '';
        if (preg_match("/^http/", $url)) {
            try {
                $doc = createDOMfromURL($url);
                
                $feed_title = $doc->getElementsByTagName('title')->item(0)->nodeValue;
                
                $item = $doc->getElementsByTagName('item');
                foreach ($item as $item) {
                    $articleInfo = array('title'=>$item->getElementsByTagName('title')->item(0)->nodeValue, 'link'=>$item->getElementsByTagName('link')->item(0)->nodeValue, 'id'=>$item->getElementsByTagName('id')->item(0)->nodeValue, 'pubDate'=>$item->getElementsByTagName('pubDate')->item(0)->nodeValue);
                    if (($date == 0) || (strtotime($articleInfo['pubDate']) > strtotime($date))) {
                        array_push($thisFeed, $articleInfo);
                    }
                }
                
                $entry = $doc->getElementsByTagName('entry');
                foreach ($entry as $entry) {
                    $articleInfo = array('title'=>$entry->getElementsByTagName('title')->item(0)->nodeValue, 'link'=>$entry->getElementsByTagName('link')->item(0)->getAttribute('href'), 'id'=>$entry->getElementsByTagName('id')->item(0)->nodeValue, 'pubDate'=>$entry->getElementsByTagName('pubDate')->item(0)->nodeValue, 'published'=>$entry->getElementsByTagName('published')->item(0)->nodeValue);
                    foreach ($articleInfo as $key=>$value) {
                        $articleInfo[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    }
                    if (($date == 0) || (strtotime($articleInfo['pubDate']) > strtotime($date)) || (strtotime($articleInfo['published']) > strtotime($date))) {
                        array_push($thisFeed, $articleInfo);
                    }
                }
            }
            catch(Exception $e) {
                $form_error = 15;
            }
        }
        
        $feed_title = htmlspecialchars($feed_title, ENT_QUOTES, 'UTF-8');
        return array($thisFeed, $feed_title);
    }
    
    function parseError($data) {
        $thisFeed = array();
        try {
            $xml = $this->createParserFromString(utf8_encode($data));
            if ($xml != false) {
                $root = $xml->getName();
                switch ($root) {
                    case 'hash':
                        $thisFeed = array('request'=>$xml->request, 'error'=>$xml->error);
                        break;
                    default:
                        break;
                }
            }
        }
        catch(Exception $e) {
            $form_error = 15;
        }
        
        return $thisFeed;
    }
    
    function parseXML($data) {
        $thisFeed = array();
        try {
            $xml = $this->createParserFromString(utf8_encode($data));
            if ($xml != false) {
                $root = $xml->getName();
                switch ($root) {
                    case 'user':
                        $thisFeed[] = $this->parseUser($xml);
                        break;
                    case 'ids':
                        $thisFeed = $this->parseIdList($xml->children());
                        break;
                    case 'id_list':
                        $this->next_cursor = $xml->next_cursor;
                        $thisFeed = $this->parseIdList($xml->ids->children());
                        break;
                    case 'status':
                        $thisFeed[] = $this->parseStatus($xml);
                        break;
                    case 'users_list':
                        $this->next_cursor = $xml->next_cursor;
                        $thisFeed = $this->parseUsersList($xml->users->children());
                        break;
                    case 'users':
                        $thisFeed = $this->parseUsersList($xml->children());
                        break;
                    case 'statuses':
                        $thisFeed = $this->parseStatuses($xml->children());
                        break;
                    case 'hash':
                        $thisFeed = $this->parseHash($xml);
                        break;
                    case 'relationship':
                        $thisFeed = $this->parseRelationship($xml);
                        break;
                    case 'lists_list':
                        $thisFeed = $this->parseLists($xml->lists->children());
                        break;
                    default:
                        break;
                }                
            }
        }
        catch(Exception $e) {
            $form_error = 15;
        }
        
        return $thisFeed;
    }
    
    function getNextCursor() {
        return $this->next_cursor;
    }
    
    function createDOMfromURL($url) {
        $doc = new DOMDocument();
        $doc->load($url);
        return $doc;
    }
    
    function createParserFromString($data) {
        $xml = simplexml_load_string($data);
        return $xml;
    }
    
    private function parseUser(&$xml) {
        return array(
            'user_id'       => $xml->id,
            'user_name'     => $xml->screen_name,
            'full_name'     => $xml->name,
            'avatar'        => $xml->profile_image_url,
            'location'      => $xml->location,
            'description'   => $xml->description,
            'url'           => $xml->url,
            'is_protected'  => $xml->protected,
            'follower_count'    => $xml->followers_count,
            'friend_count'  => $xml->friends_count,
            'post_count'    => $xml->statuses_count,
            'favorites_count'   => $xml->favourites_count,
            'joined'        => gmdate(self::DateFormat, strToTime($xml->created_at))
        );
    }
    
    private function parseIdList(&$xml) {
        $id_list_parsed = array();
        foreach ($xml as $id) {
            $id_list_parsed[] = array('id' => $id);
        }
        
        return $id_list_parsed;
    }

    private function parseStatus(&$xml) {
        return array(
            'post_id'       => $xml->id,
            'user_id'       => $xml->user->id,
            'user_name'     => $xml->user->screen_name,
            'full_name'     => $xml->user->name,
            'avatar'        => $xml->user->profile_image_url,
            'location'      => $xml->user->location,
            'description'   => $xml->user->description,
            'url'           => $xml->user->url,
            'is_protected'  => $xml->user->protected ,
            'followers'     => $xml->user->followers_count,
            'following'     => $xml->user->friends_count,
            'tweets'        => $xml->user->statuses_count,
            'joined'        => gmdate(self::DateFormat, strToTime($xml->user->created_at)),
            'post_text'     => $xml->text,
            'pub_date'      => gmdate(self::DateFormat, strToTime($xml->created_at)),
            'in_reply_to_post_id'   => $xml->in_reply_to_status_id,
            'in_reply_to_user_id'   => $xml->in_reply_to_user_id,
            'source'        => $xml->source
        );
    }
    
    private function parseUsersList(&$xml) {
        $users_list_parsed = array();
        foreach ($xml as $user) {
            $users_list_parsed[] = array(
                'post_id'       => $user->status->id,
                'user_id'       => $user->id,
                'user_name'     => $user->screen_name,
                'full_name'     => $user->name,
                'avatar'        => $user->profile_image_url,
                'location'      => $user->location,
                'description'   => $user->description,
                'url'           => $user->url,
                'is_protected'  => $user->protected,
                'friend_count'  => $user->friends_count,
                'follower_count'    => $user->followers_count,
                'joined'        => gmdate(self::DateFormat, strToTime($user->created_at)),
                'post_text'     => $user->status->text,
                'last_post'     => gmdate(self::DateFormat, strToTime($user->status->created_at)),
                'pub_date'      => gmdate(self::DateFormat, strToTime($user->status->created_at)),
                'favorites_count'   => $user->favourites_count,
                'post_count'    => $user->statuses_count
            );
        }
        
        return $users_list_parsed;
    }
    
    private function parseStatuses(&$xml) {
        $statuses_parsed = array();        
        foreach ($xml as $status) {
            $statuses_parsed = array('post_id'=> $status->id,
            'user_id'       => $status->user->id,
            'user_name'     => $status->user->screen_name,
            'full_name'     => $status->user->name,
            'avatar'        => $status->user->profile_image_url,
            'location'      => $status->user->location,
            'description'   => $status->user->description,
            'url'           => $status->user->url,
            'is_protected'  => $status->user->protected ,
            'follower_count'    => $status->user->followers_count,
            'friend_count'  => $status->user->friends_count,
            'post_count'    => $status->user->statuses_count,
            'joined'        => gmdate(self::DateFormat, strToTime($status->user->created_at)),
            'post_text'     => $status->text,
            'pub_date'      => gmdate(self::DateFormat, strToTime($status->created_at)),
            'favorites_count'   => $status->user->favourites_count,
            'in_reply_to_post_id'   => $status->in_reply_to_status_id,
            'in_reply_to_user_id'   => $status->in_reply_to_user_id,
            'source'        => $status->source);
        }
        
        return $statuses_parsed;
    }
    
    private function parseHash(&$xml) {
        return array(
            'remaining-hits'    => $xml->{'remaining-hits'},
            'hourly-limit'      => $xml->{'hourly-limit'},
            'reset-time'        => $xml->{'reset-time-in-seconds'}
        );
    }
    
    private function parseRelationship(&$xml) {
        return array(
            'source_follows_target' => $xml->source->following,
            'target_follows_source' => $xml->target->following
        );
    }
    
    private function parseLists(&$xml) {
        $lists_parsed = array();
        foreach ($xml as $list) {
            $thisFeed[] = array(
                'list_id'   => $list->id,
                'name' => $list->name,
                'full_name' => $list->full_name,
                'slug'  => $list->slug,
                'description' => $list->description,
                'subscriber_count'  => $list->subscriber_count,
                'member_count'  => $list->member_count,
                'uri'   => $list->uri,
                'mode'  => $list->mode
            );
        }
        
        return $lists_parsed;
    }
}

class CrawlerTwitterAPIAccessorOAuth extends TwitterAPIAccessorOAuth {
    var $api_calls_to_leave_unmade;
    var $api_calls_to_leave_unmade_per_minute;
    var $available_api_calls_for_crawler = null;
    var $available_api_calls_for_twitter = null;
    var $api_hourly_limit = null;
    var $archive_limit;
    
    function CrawlerTwitterAPIAccessorOAuth($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret, $instance, $archive_limit) {
        parent::TwitterAPIAccessorOAuth($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret);
        $this->api_calls_to_leave_unmade_per_minute = $instance->api_calls_to_leave_unmade_per_minute;
        $this->archive_limit = $archive_limit;
    }
    
    function init($logger) {
        $status_message = "";
        $account_status = $this->cURL_source['rate_limit'];
        list($cURL_status, $twitter_data) = $this->apiRequest($account_status, $logger);
        $this->available_api_calls_for_crawler++; //status check doesnt' count against balance
        
        if ($cURL_status > 200) {
            $this->available = false;
        } else {
            try {
                # Parse file
                $status_message = "Parsing XML data from $account_status ";
                $status = $this->parseXML($twitter_data);
                
                if (isset($status['remaining-hits']) && isset($status['hourly-limit']) && isset($status['reset-time'])) {
                    $this->available_api_calls_for_twitter = $status['remaining-hits'];//get this from API
                    $this->api_hourly_limit = $status['hourly-limit'];//get this from API
                    $this->next_api_reset = $status['reset-time'];//get this from API
                } else
                    throw new Exception('API status came back malformed');
                    
                //Figure out how many minutes are left in the hour, then multiply that x 1 for api calls to leave unmade
                $next_reset_in_minutes = (int) date('i', (int) $this->next_api_reset);
                $current_time_in_minutes = (int) date("i", time());
                $minutes_left_in_hour = 60;
                if ($next_reset_in_minutes > $current_time_in_minutes)
                    $minutes_left_in_hour = $next_reset_in_minutes - $current_time_in_minutes;
                elseif ($next_reset_in_minutes < $current_time_in_minutes)
                    $minutes_left_in_hour = 60 - ($current_time_in_minutes - $next_reset_in_minutes);

                    
                //echo $minutes_left_in_hour . " minutes left in the hour till ".  date('H:i:s', (int) $this->next_api_reset);
                $this->api_calls_to_leave_unmade = $minutes_left_in_hour * $this->api_calls_to_leave_unmade_per_minute;
                //echo "  ".$this->api_calls_to_leave_unmade . " API calls to leave unmade\n";
                $this->available_api_calls_for_crawler = $this->available_api_calls_for_twitter - round($this->api_calls_to_leave_unmade);

                
            }
            catch(Exception $e) {
                $status_message = 'Could not parse account status: '.$e->getMessage();
            }
        }
        $logger->logStatus($status_message, get_class($this));
        $logger->logStatus($this->getStatus(), get_class($this));

        
    }
    
    function apiRequest($url, $logger, $args = array()) {
        $content = $this->to->OAuthRequest($url, $args, 'GET');
        $status = $this->to->lastStatusCode();
        
        $this->available_api_calls_for_twitter = $this->available_api_calls_for_twitter - 1;
        $this->available_api_calls_for_crawler = $this->available_api_calls_for_crawler - 1;
        $status_message = "";
        if ($status > 200) {
            $status_message = "Could not retrieve $url";
            if (sizeof($args) > 0)
                $status_message .= "?";
            foreach ($args as $key=>$value)
                $status_message .= $key."=".$value."&";
            $status_message .= " | API ERROR: $status";
            $status_message .= "\n\n$content\n\n";
            if ($status != 404 && $status != 403)
                $this->available = false;
            $logger->logStatus($status_message, get_class($this));
            $status_message = "";
        } else {
            $status_message = "API request: ".$url;
            if (sizeof($args) > 0)
                $status_message .= "?";
            foreach ($args as $key=>$value)
                $status_message .= $key."=".$value."&";
        }
        
        $logger->logStatus($status_message, get_class($this));
        $status_message = "";
        
        if ($url != "https://twitter.com/account/rate_limit_status.xml") {
            $status_message = $this->getStatus();
            $logger->logStatus($status_message, get_class($this));
            $status_message = "";
        }
        
        return array($status, $content);
        
    }
    
    private function getStatus() {
        return $this->available_api_calls_for_twitter." of ".$this->api_hourly_limit." API calls left this hour; ".round($this->available_api_calls_for_crawler)." for crawler until ".date('H:i:s', (int) $this->next_api_reset);
    }
}
?>
