{if $smarty.foreach.foo.first}
	<div class="header clearfix"> 
        <div class="grid_1 alpha">&nbsp;</div> 
        <div class="grid_3 right">name</div> 
        <div class="grid_3 right">date</div>
        <div class="grid_11">post</div> 
        <div class="grid_2 center">replies</div> 
        <div class="grid_2 center omega">forwards</div> 
    </div> 
{/if}

<div class="individual-tweet post clearfix">
    <div class="grid_1 alpha">
        <a href="http://twitter.com/{$t->author_username}"><img src="{$t->author_avatar}" class="avatar"></a>
    </div>
    <div class="grid_3 right small">
        <a href="http://twitter.com/{$t->author_username}">@{$t->author_username}</a>
        {if $t->author->follower_count > 0}<br />Followers: {$t->author->follower_count|number_format}{/if}
    </div>
    <div class="grid_3 right small">
        <a href="http://twitter.com/{$t->author_username}/status/{$t->post_id}">{$t->adj_pub_date|relative_datetime}</a>
    </div>
    <div class="grid_11">
		{if $t->link->is_image}<div class="pic"><a href="{$t->link->url}"><img src="{$t->link->expanded_url}" /></a></div>{/if}

		<p>{$t->post_text|link_usernames_to_twitter} {if $t->in_reply_to_post_id}[<a href="{$cfg->site_root_path}post/?t={$t->in_reply_to_post_id}">in reply to</a>]{/if}</p>
		
		{if $t->link->expanded_url and !$t->link->is_image}<ul><li><a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">{$t->link->title}</a></li></ul>{/if}
		
		{if $t->author->location}<div class="small gray">Location: {$t->author->location}</div>{/if}
    </div>
    <div class="grid_2 center">
		{if $t->mention_count_cache > 0}<span class="reply-count"><a href="{$site_root}public.php?t={$t->post_id}">{$t->mention_count_cache}<!-- repl{if $t->mention_count_cache eq 1}y{else}ies{/if}--></a></span>{else}&nbsp;{/if} 
    </div>
    <div class="grid_2 center omega">
		{if $t->retweet_count_cache > 0}<span class="reply-count"><a href="{$site_root}public.php?t={$t->post_id}">{$t->retweet_count_cache}<!-- retweet{if $t->retweet_count_cache eq 1}{else}s{/if}--></a></span>{else}&nbsp;{/if} 
	</div>
    
 
		
</div>
