<?php

// Version 3.2.3

function tb_admin_load_scripts() {
    wp_enqueue_script('jq', '/' . PLUGINDIR . '/tweet-blender/js/jquery-1.3.2.min.js');
    wp_enqueue_script('jq-ui', '/' . PLUGINDIR . '/tweet-blender/js/jquery-ui.js',array('jq'));
    wp_enqueue_script('jq-ui-tabs', '/' . PLUGINDIR . '/tweet-blender/js/ui.tabs.js',array('jq','jq-ui'));
    wp_enqueue_script('tweet-blender-admin', '/' . PLUGINDIR . '/tweet-blender/js/admin.js',array('jq','jq-ui','jq-ui-tabs'));
}

function tb_admin_load_styles() {
    wp_enqueue_style('tweet-blender-css', '/' . PLUGINDIR .'/tweet-blender/css/admin.css');
    wp_enqueue_style('jquery-ui-css', '/' . PLUGINDIR . '/tweet-blender/css/jquery-ui/jquery-ui.css');
    wp_enqueue_style('jquery-tabs-css', '/' . PLUGINDIR . '/tweet-blender/css/jquery-ui/ui.tabs.css');
}

// register admin page
add_action('admin_menu', 'tb_admin_menu');
function tb_admin_menu() {
    $pagehook = add_options_page('Tweet Blender Settings', 'Tweet Blender', 'manage_options', __FILE__, 'tb_admin_page');
    add_action( 'admin_print_scripts-' . $pagehook, 'tb_admin_load_scripts' );
    add_action( 'admin_print_styles-' . $pagehook, 'tb_admin_load_styles' );
}

function tb_admin_page() {
 
    global $tb_option_names, $tb_option_names_system, $tb_keep_tweets_options, $tb_languages, $cache_clear_results, $tb_throttle_time_options;

    // Read in existing option values from database
	$tb_o = get_option('tweet-blender');
		
	// set defaults
	if (empty($tb_o) || (isset($tb_o['archive_tweets_num']) && $tb_o['archive_tweets_num'] < 1)) {
		$tb_o['archive_tweets_num'] = 20;
	}

	// get API limit info
	$api_limit_data = null;
	if ($json_data = tb_get_server_rate_limit_json($tb_o)) {
		$json = new Services_JSON();
    	$api_limit_data = $json->decode($json_data);
	}

	// perform maintenance
	if (isset($tb_o['archive_keep_tweets']) && $tb_o['archive_keep_tweets'] > 0) {
		tb_db_cache_clear('WHERE DATEDIFF(now(),created_at) > ' . $tb_o['archive_keep_tweets']);
	}
					
        // See if the user has posted us some information
	if( isset($_POST['tb_new_data']) && $_POST['tb_new_data'] == 'Y' ) {

		// check nonce
		check_admin_referer('tweet-blender_settings-save');

		// if we are disabling cache - clear it
		if (isset($tb_o['advanced_disabled_cache']) && (!$tb_o['advanced_disable_cache'] && $_POST['advanced_disable_cache'])) {
			tb_db_cache_clear();
		}
		// if we are clearing individual cached sources
		if (isset($_POST['delete_cache_src']) && $_POST['delete_cache_src']) {
			$cleared_sources = array();
			foreach ($_POST['delete_cache_src'] as $del_src) {
				tb_db_cache_clear("WHERE source='$del_src'");
				$cleared_sources[] = $del_src;
			}
			if (sizeof($cleared_sources) > 0 ) {
				$cache_clear_results = 'Cleared cached tweets for ' . implode(', ',$cleared_sources);
			}
		}
		
		// check if we are rerouting with oAuth
		if ((isset($_POST['advanced_reroute_on']) && $_POST['advanced_reroute_on']) && (isset($_POST['advanced_reroute_type']) && $_POST['advanced_reroute_type'] == 'oauth')) {
			
			if(!isset($tb_o['oauth_access_token'])) {
				// Create TwitterOAuth object and get request token
				$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
				 
				// Get request token
				$request_token = $connection->getRequestToken(get_bloginfo('url') . '/' . PLUGINDIR . "/tweet-blender/lib/twitteroauth/callback.php");
				 
				if ($connection->http_code == 200) {
					// Save request token to session
					$tb_o['oauth_token'] = $token = $request_token['oauth_token'];
					$tb_o['oauth_token_secret'] = $request_token['oauth_token_secret'];
					update_option('tweet-blender',$tb_o);
					
					$errors[] = "To take advantage of a whitelisted account with oAuth please <a href='javascript:tAuth(\"" . $connection->getAuthorizeURL($token) . "\")' title='Authorize Twitter Access'>use your Twitter account to authorize access</a>.";
				}
				else {
					$errors[] = "Not able to form oAuth authorization request URL. HTTP status code: " . $connection->http_code;
				}					
			}
		}

		if (isset($errors) && sizeof($errors) > 0) {
			$message = '<div class="error"><strong><ul><li>' . join('</li><li>',$errors) . '</li></ul>' . $cache_clear_results . '</strong></div>';
			$tb_o = $_POST;
		}
		else {
			// reset oAuth tokens
			if (isset($_POST['reset_oauth']) && $_POST['reset_oauth']) {
				unset($tb_o['oauth_access_token']);
			}

			// unset archive page ID if archive is disabled
			if(isset($_POST['archive_is_disabled']) && $_POST['archive_is_disabled']) {
				unset($tb_o['archive_page_id']);
				unset($tb_o['archive_is_disabled']);
			}

			// cycle through all possible options
			foreach($tb_option_names as $opt) {
				// if option was set by user - store it
				if(isset($_POST[$opt])) {
					$tb_o[$opt] = $_POST[$opt];
				}
				// else if option was not one that user controls - unset it
				elseif (!array_key_exists($opt,$tb_option_names_system)) {
					$tb_o[$opt] = '';
				}
			}
			
			update_option('tweet-blender',$tb_o);
	        // Put an options updated message on the screen
			$message = '<div class="updated"><p><strong>Settings saved. ' . $cache_clear_results . '</strong></p></div>';
		}	

    }
?>

<script type="text/javascript">
	var lastUsedTabId = <?php if (isset($_POST['tb_tab_index'])) { echo $_POST['tb_tab_index']; } else { echo 0; } ?>;
</script>

<div class="wrap">
	<div id="icon-tweetblender" class="icon32"><br/></div><h2><?php _e('Tweet Blender', 'mt_trans_domain' ); ?></h2>

	<?php if (!empty($message)) { echo $message; }  if (!empty($log_msg)) { echo "<!-- $log_msg -->"; } ?>
	 
	<form name="form1" id="form1" method="post" action="<?php echo str_replace( '%7E', '~', esc_attr($_SERVER['REQUEST_URI'])); ?>">
	<input type="hidden" id="tb_new_data" name="tb_new_data" value="Y">
	<input type="hidden" id="tb_tab_index" name="tb_tab_index" value="">
	<?php
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('tweet-blender_settings-save');
	?>

	<div id="tabs">
    <ul style="height:35px;">
        <li><a href="#tab-1"><span>General</span></a></li>
        <li><a href="#tab-2"><span>Widgets</span></a></li>
        <li><a href="#tab-3"><span>Archive</span></a></li>
        <li><a href="#tab-4"><span>Filters</span></a></li>
        <li><a href="#tab-5"><span>Advanced</span></a></li>
        <li id="statustab"><a href="#tab-6"><span>Status</span></a></li>
        <li><a href="#tab-7"><span>Help</span></a></li>
    </ul>

    <div id="tab-1">
    <!-- General settings -->
		<table class="form-table">
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="general_link_urls">
			<input type="checkbox" name="general_link_urls" <?php checked('on', $tb_o['general_link_urls']); ?>>
			<?php _e("Link http &amp; https URLs insde tweet text", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="general_link_screen_names">
			<input type="checkbox" name="general_link_screen_names" <?php checked('on', $tb_o['general_link_screen_names']); ?>>
			<?php _e('Link @screenname inside tweet text', 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="general_link_hash_tags">
			<input type="checkbox" name="general_link_hash_tags" <?php checked('on', $tb_o['general_link_hash_tags']); ?>>
			<?php _e("Link #hashtags insde tweet text", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<h3>SEO</h3>
			<label for="general_seo_tweets_googleoff">
			<input type="checkbox" name="general_seo_tweets_googleoff" <?php checked('on', $tb_o['general_seo_tweets_googleoff']); ?>>
			<?php _e('Wrap all tweets with googleoff/googleon tags to prevent indexing', 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="general_seo_footer_googleoff">
			<input type="checkbox" name="general_seo_footer_googleoff" <?php checked('on', $tb_o['general_seo_footer_googleoff']); ?>>
			<?php _e('Wrap footer with date and time in all tweets with googleoff/googleon tags to prevent indexing', 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		</table>
	</div>

    <div id="tab-2">
    <!-- Widgets Settings -->
		<table class="form-table">
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="widget_check_sources">
			<input type="checkbox" name="widget_check_sources" <?php checked('on', $tb_o['widget_check_sources']); ?>>
			<?php _e("Check and verify sources when widget settings are saved", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="widget_show_header">
			<input type="checkbox" name="widget_show_header" <?php checked('on', $tb_o['widget_show_header']); ?>>
			<?php _e("Show header with Twitter logo and refresh link for each widget", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="widget_show_photos">
			<input type="checkbox" name="widget_show_photos" <?php checked('on', $tb_o['widget_show_photos']); ?>>
			<?php _e("Show user's photo for each tweet", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="widget_show_user">
			<input type="checkbox" name="widget_show_user" <?php checked('on', $tb_o['widget_show_user']); ?>>
			<?php _e("Show author's username for each tweet", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="widget_show_source">
			<input type="checkbox" name="widget_show_source" <?php checked('on', $tb_o['widget_show_source']); ?>>
			<?php _e("Show tweet source for each tweet", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="widget_show_reply_link">
			<input type="checkbox" name="widget_show_reply_link" <?php checked('on', $tb_o['widget_show_reply_link']); ?>>
			<?php _e("Show reply link for each tweet (on mouse over)", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="widget_show_follow_link">
			<input type="checkbox" name="widget_show_follow_link" <?php checked('on', $tb_o['widget_show_follow_link']); ?>>
			<?php _e("Show follow link for each tweet (on mouse over)", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		</table>
	</div>
	
    <div id="tab-3">
	<!-- Archive Page Settings -->
		<table class="form-table" id="archivesettings">
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="archive_is_disabled">
			<input type="checkbox" id="archive_is_disabled" name="archive_is_disabled" <?php checked('on', $tb_o['archive_is_disabled']); ?>>
			<?php _e('Disable archive page', 'mt_trans_domain' ); ?> 
			</label>
			</th>
		</tr>
		<tr valign="top" <?php if (isset($tb_o['archive_is_disabled']) && $tb_o['archive_is_disabled']) echo 'style="display:none;"'; ?>>
			<th class="th-full" colspan="2" scope="row">
			<label for="archive_auto_page">
			<input type="checkbox" id="archive_auto_page" name="archive_auto_page" <?php checked('on', $tb_o['archive_auto_page']); ?>>
			<?php _e('Create archive page automatically', 'mt_trans_domain' ); ?> 
			</label>
			</th>
		</tr>
		<tr valign="top" <?php if (isset($tb_o['archive_is_disabled']) && $tb_o['archive_is_disabled']) echo 'style="display:none;"'; ?>>
			<th scope="row"><label for="archive_tweets_num"><?php _e('Maximum number of tweets to show', 'mt_trans_domain' ); ?>:
			</label></th>
			<td>
			<select name="archive_tweets_num">
				<?php
				for ($i = 10; $i <= 90; $i+=10) {
					echo '<option value="' . $i . '"';
					if ($i == $tb_o['archive_tweets_num']) {
						echo ' selected';
					}
					echo '>' . $i . '</option>';
				}
				for ($i = 100; $i <= 500; $i+=100) {
					echo '<option value="' . $i . '"';
					if ($i == $tb_o['archive_tweets_num']) {
						echo ' selected';
					}
					echo '>' . $i . '</option>';
				}
			?></select></td>
		</tr>
		<tr valign="top" <?php if (isset($tb_o['archive_is_disabled']) && $tb_o['archive_is_disabled']) echo 'style="display:none;"'; ?>>
			<th scope="row"><label for="archive_keep_tweets"><?php _e('Automatically remove tweets that are older than', 'mt_trans_domain' ); ?>:
			</label></th>
			<td>
			<select name="archive_keep_tweets">
			<?php
				foreach ($tb_keep_tweets_options as $name => $days) {
					echo '<option value="' . $days . '"';
					if ($days == $tb_o['archive_keep_tweets']) {
						echo ' selected';
					}
					echo '>' . $name . '</option>';
				}
			?></select></td>
		</tr>
		<tr valign="top" <?php if (isset($tb_o['archive_is_disabled']) && $tb_o['archive_is_disabled']) echo 'style="display:none;"'; ?>>
			<th class="th-full" colspan="2" scope="row">
			<label for="archive_show_photos">
			<input type="checkbox" name="archive_show_photos" <?php checked('on', $tb_o['archive_show_photos']); ?>>
			<?php _e("Show user's photo for each tweet", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top" <?php if (isset($tb_o['archive_is_disabled']) && $tb_o['archive_is_disabled']) echo 'style="display:none;"'; ?>>
			<th class="th-full" colspan="2" scope="row">
			<label for="archive_show_user">
			<input type="checkbox" name="archive_show_user" <?php checked('on', $tb_o['archive_show_user']); ?>>
			<?php _e("Show author's username for each tweet", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top" <?php if (isset($tb_o['archive_is_disabled']) && $tb_o['archive_is_disabled']) echo ' style="display:none;"'; ?>>
			<th class="th-full" colspan="2" scope="row">
			<label for="archive_show_source">
			<input type="checkbox" name="archive_show_source" <?php checked('on', $tb_o['archive_show_source']); ?>>
			<?php _e("Show tweet source", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top" <?php if (isset($tb_o['archive_is_disabled']) && $tb_o['archive_is_disabled']) echo ' style="display:none;"'; ?>>
			<th class="th-full" colspan="2" scope="row">
			<label for="archive_show_reply_link">
			<input type="checkbox" name="archive_show_reply_link" <?php checked('on', $tb_o['archive_show_reply_link']); ?>>
			<?php _e("Show reply link for each tweet (on mouse over)", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		<tr valign="top" <?php if (isset($tb_o['archive_is_disabled']) && $tb_o['archive_is_disabled']) echo ' style="display:none;"'; ?>>
			<th class="th-full" colspan="2" scope="row">
			<label for="archive_show_follow_link">
			<input type="checkbox" name="archive_show_follow_link" <?php checked('on', $tb_o['archive_show_follow_link']); ?>>
			<?php _e("Show follow link for each tweet (on mouse over)", 'mt_trans_domain' ); ?>
			</label>
			</th>
		</tr>
		</table>
	</div>
	
	<div id="tab-4">
	<!-- Filtering -->
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="filter_limit_per_source"><?php _e('Throttling', 'mt_trans_domain' ); ?>:</label></th>
			<td>
			<?php _e('For each user show a maximum of ', 'mt_trans_domain' ); ?> <select name="filter_limit_per_source">
				<option value="">All</option>
				<?php
				foreach (range(1,10) as $lim) {
					echo '<option value="' . $lim . '"';
					if ($lim == $tb_o['filter_limit_per_source']) {
						echo ' selected';
					}
					echo '>' . $lim . '</option>';
				}
			?></select> tweet(s) within <select name="filter_limit_per_source_time">
				<?php
				foreach ($tb_throttle_time_options as $name => $sec) {
					echo '<option value="' . $sec . '"';
					if ($sec == $tb_o['filter_limit_per_source_time']) {
						echo ' selected';
					}
					echo '>' . $name . '</option>';
				}
			?></select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="filter_lang"><?php _e('Show only tweets in', 'mt_trans_domain' ); ?>:</label></th>
			<td>
			<select name="filter_lang">
				<?php
				foreach ($tb_languages as $code => $lang) {
					echo '<option value="' . $code . '"';
					if ($code == $tb_o['filter_lang']) {
						echo ' selected';
					}
					echo '>' . $lang . '</option>';
				}
			?></select>
			</td>
		</tr>
		<tr valign="top">
	 		<th class="th-full" colspan="2" scope="row">
			<input type="checkbox" name="filter_hide_same_text" <?php checked('on', $tb_o['filter_hide_same_text']); ?>>
			<label for="filter_hide_same_text"><?php _e("Hide tweets that come from different users but have exactly the same text", 'mt_trans_domain' ); ?></label>
			</th>
		</tr>
		<tr valign="top">
	 		<th class="th-full" colspan="2" scope="row">
			<input type="checkbox" name="filter_hide_replies" <?php checked('on', $tb_o['filter_hide_replies']); ?>>
			<label for="filter_hide_replies"><?php _e("Hide tweets that are in reply to other tweets", 'mt_trans_domain' ); ?></label>
			</th>
		</tr>
		<tr valign="top">
	 		<th class="th-full" colspan="2" scope="row">
			<input type="checkbox" name="filter_hide_not_replies" <?php checked('on', $tb_o['filter_hide_not_replies']); ?>>
			<label for="filter_hide_not_replies"><?php _e("Hide tweets that are NOT replies to other tweets", 'mt_trans_domain' ); ?></label>
			</th>
		</tr>
		<tr valign="top">
	 		<th class="th-full" colspan="2" scope="row">
			<input type="checkbox" name="filter_hide_mentions" <?php checked('on', $tb_o['filter_hide_mentions']); ?>>
			<label for="filter_hide_mentions"><?php _e("Hide mentions of users, only show tweets from users themselves", 'mt_trans_domain' ); ?></label>
			</th>
		</tr>
		<!-- FUTURE: location-based selection
		<tr>
			<th scope="row"><label for="filter_location_name"><?php _e('Show only tweets near this place ', 'mt_trans_domain' ); ?>:</label></th>
			<td><input type="text" size="30" name="filter_location_name" value="<?php echo $tb_o['filter_location_name']; ?>"><br/>
				<label for="filter_location_dist">Within </label>
				<select name="filter_location_dist">
				<?php
				foreach (array(5,10,15,20,50,100,200,500) as $dist) {
					echo '<option value="' . $dist . '"';
					if ($dist == $tb_o['filter_location_dist']) {
						echo ' selected';
					}
					echo '>' . $dist . '</option>';
				}
				?></select>
				<select name="filter_location_dist_units">
				<?php
				foreach (array('mi' => 'miles','km' => 'kilometers') as $du => $dist_units) {
					echo '<option value="' . $du . '"';
					if ($du == $tb_o['filter_location_dist_units']) {
						echo ' selected';
					}
					echo '>' . $dist_units . '</option>';
				}
				?></select>
			</td>
		</tr>
		-->
		<tr valign="top">
			<th scope="row"><label for="filter_bad_strings"><?php _e('Exclude tweets that contain these users, words or hashtags', 'mt_trans_domain' ); ?>: </label>
			</th>
			<td valign="top">
			<textarea id="filter_bad_strings" name="filter_bad_strings" rows=2 cols=60 wrap="soft"><?php if (isset($tb_o['filter_bad_strings'])) { echo $tb_o['filter_bad_strings']; } ?></textarea> 
				<br/>
				<span class="setting-description">You can use single keywords, usernames, or phrases. Enclose phrases in quotes. Do not use @ for screen names. Separate with commas. Example: #spam,badword,"entire bad phrase",badUser,anotherBadUser,#badHashTag</span>
			</td>
		</tr>
		</table>
	</div>
	
	<div id="tab-5">
	<!-- Advanced Settings -->
		<table class="form-table">
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="advanced_reroute_on">
			<input type="checkbox" name="advanced_reroute_on" <?php checked('on', $tb_o['advanced_reroute_on']); ?>>
			<?php _e('Re-route Twitter traffic through this server', 'mt_trans_domain' ); ?> 
			</label> (<input type="radio" value="oauth" name="advanced_reroute_type" <?php checked('oauth', $tb_o['advanced_reroute_type']); ?>> user account based with oAuth <input type="radio" value="direct" name="advanced_reroute_type" <?php checked('direct', $tb_o['advanced_reroute_type']); ?>> IP based)<br/>
			<span class="setting-description">This option allows you to reroute all API calls to Twitter via your server. This is to be used ONLY if your server is a white-listed server that has higher connection allowance than each individual user.  Each user can make up to 150 Twitter API connections per hour. Each visitor to your site will have their own limit i.e. their own 150. Checking the box will make all visitors to the site use your server's connection limit, not their own limit. If you did not prearranged with Twitter to have that limit increased that means that it will be 150 for ALL visitors - be careful.</span>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="advanced_show_limit_msg">
			<input type="checkbox" name="advanced_show_limit_msg" <?php checked('on', $tb_o['advanced_show_limit_msg']); ?>>
			<?php _e('Notify user when Twitter API connection limit is reached', 'mt_trans_domain' ); ?> 
			</label><br/>
			<span class="setting-description">
				When the API connection limit is reached and there is no cached data Tweet Blender can't show new tweets. If you check this box the plugin will show a message to user that will tell them that limit has been reached. In addition, the message will show how soon fresh tweets will be available again. If you don't check the box the message will not be shown - the tweets just won't be refreshed until plugin is able to get fresh data.
			</span>
			</th>
		</tr>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="advanced_disable_cache">
			<input type="checkbox" name="advanced_disable_cache" <?php checked('on', $tb_o['advanced_disable_cache']); ?>>
			<?php _e('Disable data caching', 'mt_trans_domain' ); ?> 
			</label><br/>
			<span class="setting-description">
				Every time Tweet Blender refreshes, it stores data it receives from Twitter into a special cache on your server. Once a user reaches his API connection limit TweetBlender starts using cached data. Cached data is centralized and is updated by all users so even if one user is at a limit s/he can still get fresh tweets as cache is updated by other users that haven't yet reached their limit. If you don't want to cache data (to save bandwidth or for some other reason) then check this box. <b>WARNING: clears all cached tweets</b>.
			</span>
			</th>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="general_timestamp_format"><?php _e('Timestamp Format', 'mt_trans_domain' ); ?>:
			</label></th>
			<td><input type="text" name="general_timestamp_format" value="<?php if (isset($tb_o['general_timestamp_format'])) { echo $tb_o['general_timestamp_format']; } ?>"> <span class="setting-description"><br/>
				leave blank = verbose from now ("4 minutes ago")<br/>
				h = 12-hour format of an hour with leading zeros ("08")<br/>
				i = Minutes with leading zeros ("01")<br/>
				s = Seconds, with leading zeros ("01")<br/>
				<a href="http://kirill-novitchenko.com/2009/05/configure-timestamp-format/">additional format options &raquo;</a>
			</span></td>
		</tr>
		<?php if(isset($tb_o['oauth_access_token'])) { ?>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="reset_oauth">
			<input type="checkbox" name="reset_oauth" value="1">
			<?php _e('Clear oAuth Access Tokens', 'mt_trans_domain' ); ?> 
			</label><br/>
			<span class="setting-description">
				To get tweets from private users Tweet Blender needs to login to twitter using your credentials. Once you authorize access, the special tokens are stored in the configuration settings. This is NOT a username or password. Your username/password is NOT stored.  The tokens are tied to a specific Twitter account so if you changed your account or would like to use another account for authentication check this box to have previously saved tokens cleared.
			</span>
			</th>
		</tr>
		<?php } ?>
		<tr valign="top">
			<th class="th-full" colspan="2" scope="row">
			<label for="advanced_no_search_api">
			<input type="checkbox" name="advanced_no_search_api" <?php checked('on', $tb_o['advanced_no_search_api']); ?>>
			<?php _e('Do not use search API for screen names', 'mt_trans_domain' ); ?> 
			</label><br/>
			<span class="setting-description">
				To get tweets for screen names Tweet Blender relies on Twitter Search API; however, sometimes Twitter's search does not return any tweets for a particular account due to some complex internal relevancy rules. If you see tweets for a user when looking at http://twitter.com/{someusername} but you do not see tweets for that same user when you look at http://search.twitter.com/search?q={@someusername} you can try to check this box and have Tweet Blender switch to a different API to retrieve recent tweets. <b>Important: screen names with modifiers (e.g. @user|#topic) will still use Search API.</b>
			</span>
			</th>
		</tr>
		</table>
	</div>

	<div id="tab-6">
	<!-- Status -->
		<table class="form-table">
		<tr>
			<th>API requests from blog server:</th>
			<td><?php
				if ($api_limit_data) {
					echo 'Max is ' . $api_limit_data->hourly_limit . '/hour &middot; ';
					if ($api_limit_data->remaining_hits > 0) {
						echo 'You have <span class="pass">' . $api_limit_data->remaining_hits . '</span> left &middot; ';
					}
					else {
						echo 'You have <span class="fail">0</span> left &middot; ';
					}
					echo "Next reset " . tb_verbal_time($api_limit_data->reset_time_in_seconds);
				}
				else {
					echo '<span class="fail">Check failed</span>';
				}
				if (isset($tb_o['advanced_reroute_on']) && ($tb_o['advanced_reroute_on'] && $tb_o['advanced_reroute_type'] == 'oauth')) {
					echo '<br/>checked with user account (oAuth)';
				}
				else {
					$server_address = '[IP NOT AVAILABLE]';
					if (isset($_SERVER['SERVER_ADDR'])) $server_address = esc_attr($_SERVER['SERVER_ADDR']);
					echo '<br/>checked with IP of your server (' . $server_address . ')';
				}
			?></td>
		</tr>
		<tr>
			<th>API requests from your computer:</th>
			<td id="locallimit"></td>
		</tr>
		<tr>
			<th>oAuth Access Tokens:</th>
			<td><?php 
			if(isset($tb_o['oauth_access_token'])) {
				echo '<span class="pass">Present</span>';
			}
			elseif (!empty($have_private_sources) && !isset($tb_o['oauth_access_token'])) {
				echo '<span class="fail">Not Present</span>';
			}
			else {
				echo 'Not Needed';
			}
			?></td>
		</tr>
		<tr>
			<th>Cache:</th>
			<td>
				<?php	
				
				if ($cached_sources = tb_get_cache_stats()) {
					//print_r($cached_sources);
					// Output each opened file and then close
					foreach ((array)$cached_sources as $cache_src) {
						$s = '';
						if ($cache_src->tweets_num != 1) {
							$s = 's';
						}
						echo '<input type="checkbox" name="delete_cache_src[]" value="' . esc_attr($cache_src->source) . '" /> ';					
				       	echo urldecode($cache_src->source) . " - " . $cache_src->tweets_num . " tweet$s - updated " . tb_verbal_time($cache_src->last_update) . '<br/>';
					}
					echo '<label for="delete_cache_src[]">&nbsp;&uarr; Check boxes above to clear cached tweets from the database</label>';
				}
				elseif ($tb_o['advanced_disable_cache'] == false) {
					echo '<span class="fail">no cached tweets found and caching is ON</span>';
				}
				else {
					echo '<span class="pass">no cached tweets found and caching is OFF</span>';
				}

				?>
			</td>
		</tr>
		</table>
	</div>

	<div id="tab-7">
	Get Satisfaction Community: <a href="http://getsatisfaction.com/tweet_blender">http://getsatisfaction.com/tweet_blender</a><br/>
	Facebook Fan Page: <a href="http://www.facebook.com/pages/Tweet-Blender/96201618006">http://www.facebook.com/pages/Tweet-Blender/96201618006</a><br/>
	Twitter: <a href="http://twitter.com/tweetblender">http://twitter.com/tweetblender</a><br/>
	Homepage: <a href="http://www.tweetblender.com">http://www.tweetblender.com</a><br/>
	</div>

	</div>

	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'mt_trans_domain' ) ?>" />
	</p>
</form>
</div>

<?php
 
}

function tb_get_cache_stats() {
	global $wpdb;
	$table_name = $wpdb->prefix . "tweetblender";
	
	$sql = "SELECT source, COUNT(*) AS tweets_num, UNIX_TIMESTAMP(MAX(created_at)) AS last_update FROM " . $table_name . " GROUP BY source";
	$results = $wpdb->get_results($sql);
	
	return $results;
}

?>