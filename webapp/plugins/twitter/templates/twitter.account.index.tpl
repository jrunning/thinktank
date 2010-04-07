<div class="append_20">
<h2 class="subhead">Twitter Configuration</h2>

{if $owner->is_admin}
	<div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
		<p><span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
		As an administrator you can see all accounts in the system.</p>
	</div>
{/if}

{if count($owner_instances) > 0 }
    {foreach from=$owner_instances key=iid item=i name=foo}
        <div class="clearfix">
        <div class="grid_4 right" style="padding-top:.5em;">
            <a href="{$cfg->site_root_path}?u={$i->network_username}">{$i->network_username}</a>
        </div>
        <div class="grid_8">
            <span id="div{$i->network_username}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all
            {if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->network_username}" value="{if $i->is_public}remove from public timeline{else}include on public timeline{/if}" /></span>
        </div>
        <div class="grid_9">
    		<span id="divactivate{$i->network_username}"><input type="submit" name="submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all {if $i->is_active}btnPause{else}btnPlay{/if}" id="{$i->network_username}" value="{if $i->is_active}pause crawling{else}start crawling{/if}" /></span>
        </div>
        </div>
    {/foreach}
{else}
    You have no Twitter accounts configured.
{/if}
</div>

<h2 class="subhead">Add a Twitter account</h2>

<a href="{$oauthorize_link}" class="tt-button ui-state-default tt-button-icon-left ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-e"></span>Authorize ThinkTank on Twitter</a> 
