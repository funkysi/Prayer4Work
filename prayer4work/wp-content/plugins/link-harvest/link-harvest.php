<?php

/*
Plugin Name: Link Harvest
Plugin URI: http://alexking.org/projects/wordpress
Description: This will harvest links from your WordPress database, creating a links list sorted by popularity. Once you have activated the plugin, you can configure your settings and see your <a href="index.php?page=link-harvest.php">list of links</a>. Also see <a href="options-general.php?page=link-harvest.php#aklh_template_tags">how to show the list of links</a> in your blog. Questions on configuration, etc.? Make sure to read the README.
Version: 1.2
Author: Alex King
Author URI: http://alexking.org
*/ 

// Copyright (c) 2006-2007 Alex King. All rights reserved.
// http://alexking.org/projects/wordpress
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// This is an add-on for WordPress
// http://wordpress.org/
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// **********************************************************************

if (!defined('AKLH_LOADED')) : // WP does weird stuff with plugin file loading for activation hooks

define('AKLH_LOADED', true);
define('AKLH_DEBUG', false);

if (AKLH_DEBUG) {
//	ini_set('display_errors', '1');
//	ini_set('error_reporting', E_ALL);
	$logfile = dirname(__FILE__).'/aklh_log.txt';
	if (!is_file($logfile) || !is_writeable($logfile)) {
		die('Debugging is enabled, but there is no wp-content/plugins/aklh_log.txt file or the file is not writeable.');
	}
}

load_plugin_textdomain('link-harvest');

// function aklh_install() {
// 	global $wpdb;
// 	$wpdb->ak_domains = $wpdb->prefix.'ak_domains';
// 	$wpdb->ak_linkharvest = $wpdb->prefix.'ak_linkharvest';
// 	$tables = $wpdb->get_col("
// 		SHOW TABLES
// 	");
// 	if (!in_array($wpdb->ak_linkharvest, $tables) && !in_array($wpdb->ak_domains, $tables)) {
// 		$aklh = new ak_link_harvest;
// 		$aklh->install();
// 	}
// }
// register_activation_hook(__FILE__, 'aklh_install');

if (!function_exists('is_admin_page')) {
	function is_admin_page() {
		if (function_exists('is_admin')) {
			return is_admin();
		}
		if (function_exists('check_admin_referer')) {
			return true;
		}
		else {
			return false;
		}
	}
}

function aklh_init() {
	global $aklh, $wpdb;
	$wpdb->ak_domains = $wpdb->prefix.'ak_domains';
	$wpdb->ak_linkharvest = $wpdb->prefix.'ak_linkharvest';
	
	$aklh = new ak_link_harvest;
	$aklh->get_settings();
}
add_action('init', 'aklh_init');

function aklh_plugin_action_links($links, $file) {
	$plugin_file = basename(__FILE__);
	if (basename($file) == $plugin_file) {
		$settings_link = '<a href="options-general.php?page='.$plugin_file.'">'.__('Settings', 'link-harvest').'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}
add_filter('plugin_action_links', 'aklh_plugin_action_links', 10, 2);

class ak_link_harvest {
	var $exclude;
	var $table_length;
	var $harvest_enabled;
	var $options;
	var $default_limit;
	var $timeout;
	var $token;
	
	function ak_link_harvest() {
		$this->exclude = array();
		$this->table_length = 100;
		$this->harvest_enabled = 1;
		$this->default_limit = 5;
		$this->timeout = 2;
		$this->token = 1;
		$this->options = array(
			'exclude' => 'explode'
			, 'table_length' => 'int'
			, 'harvest_enabled' => 'int'
			, 'token' => 'int'
		);
		$this->excluded_file_extensions = array(
			'.mov'
			, '.jpg'
			, '.gif'
			, '.png'
			, '.pdf'
			, '.mpg'
			, '.mp3'
			, '.mpeg'
			, '.avi'
			, '.swf'
			, '.doc'
			, '.xls'
			, '.wmv'
			, '.wmf'
			, '.wma'
			, '.txt'
			, '.m4p'
		);
	}
	
	function install() {
		global $wpdb;
		$tables = $wpdb->get_col("
			SHOW TABLES
		");
		if (in_array($wpdb->ak_linkharvest, $tables) || in_array($wpdb->ak_domains, $tables)) {
			return;
		}
		$result = $wpdb->query("
			CREATE TABLE `$wpdb->ak_domains` (
			`id` int(11) NOT NULL auto_increment,
			`domain` varchar(255) NOT NULL,
			`title` varchar(255) NULL,
			`count` int(11) NOT NULL default '0',
			PRIMARY KEY  (`id`),
			UNIQUE KEY `domain` (`domain`)
			)
		");
		$result = $wpdb->query("
			CREATE TABLE `$wpdb->ak_linkharvest` (
			`id` int(11) NOT NULL auto_increment,
			`post_id` int(11) NOT NULL,
			`url` text NOT NULL,
			`title` varchar(255) NULL,
			`domain_id` varchar(255) NOT NULL,
			`modified` datetime NOT NULL,
			PRIMARY KEY  (`id`),
			KEY `post_id` (`post_id`),
			KEY `domain_id` (`domain_id`)
			)
		");
		add_option('aklh_exclude', '', 'Ignore links to these domains.');
		add_option('aklh_table_length', '100', 'Number of domains to show in the links table.');
		add_option('aklh_harvest_enabled', '1', 'Can we run a link harvest?');
		add_option('aklh_token', '1', 'Use ###linkharvest### to show the links list?');
	}
	
	function clear_data() {
		global $wpdb;
		$wpdb->query("
			TRUNCATE TABLE $wpdb->ak_domains
		");
		$wpdb->query("
			TRUNCATE TABLE $wpdb->ak_linkharvest
		");
	}
	
	function get_settings() {
		foreach ($this->options as $option => $type) {
			$this->$option = get_option('aklh_'.$option);
			switch ($type) {
				case 'explode':
					$this->$option = explode(' ', $this->$option);
					break;
				case 'int':
					$this->$option = intval($this->$option);
					break;
			}
		}
	}
	
	function update_settings() {
		$this->install();
		foreach ($this->options as $option => $type) {
			if (isset($_POST[$option])) {
				switch ($type) {
					case 'explode':
						$value = stripslashes($_POST[$option]);
						break;
					case 'int':
						$value = intval($_POST[$option]);
						break;
					default:
						$value = stripslashes($_POST[$option]);
				}
				update_option('aklh_'.$option, $value);
			}
		}

		header('Location: '.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php&updated=true');
		die();
	}
	
	function excluded_file_type($link) {
		foreach ($this->excluded_file_extensions as $ext) {
			if (substr($link, strlen($ext) * -1) == $ext) {
				return true;
			}
		}
		return false;
	}
	
	function get_links($body) {
// From: http://www.onaje.com/php/article.php4/46
		$href_regex ="<";            // 1 start of the tag
		$href_regex .="\s*";         // 2 zero or more whitespace
		$href_regex .="a";           // 3 the a of the <a> tag itself
		$href_regex .="\s+";         // 4 one or more whitespace
		$href_regex .="[^>]*";       // 5 zero or more of any character that is _not_ the end of the tag
		$href_regex .="href";        // 6 the href bit of the tag
		$href_regex .="\s*";         // 7 zero or more whitespace
		$href_regex .="=";           // 8 the = of the tag
		$href_regex .="\s*";         // 9 zero or more whitespace
		$href_regex .="[\"']?";      // 10 none or one of " or '
		$href_regex .="(";           // 11 opening parenthesis, start of the bit we want to capture
		$href_regex .="[^\"' >]+";   // 12 one or more of any character _except_ our closing characters
		$href_regex .=")";           // 13 closing parenthesis, end of the bit we want to capture
		$href_regex .="[\"' >]";     // 14 closing chartacters of the bit we want to capture
		
		$regex  = "/";         // regex start delimiter
		$regex .= $href_regex; //
		$regex .= "/";         // regex end delimiter
		$regex .= "i";         // Pattern Modifier - makes regex case insensative
		$regex .= "s";         // Pattern Modifier - makes a dot metacharater in the pattern 
					// match all characters, including newlines
		$regex .= "U";         // Pattern Modifier - makes the regex ungready

		$urls = array();

		if (preg_match_all($regex, $body, $links)) {
			foreach ($links[1] as $link) {
				if (in_array(substr($link, 0, 7), array('http://', 'https:/')) && !$this->excluded_file_type($link)) {
					$urls[] = trim($link);
				}
			}
		}
		
		return $urls;
	}
	
	function get_domain_id($domain) {
		global $wpdb;
		$domain_id = $wpdb->get_var("
			SELECT id
			FROM $wpdb->ak_domains
			WHERE domain = '$domain'
		");
		if ($domain_id && !is_null($omain_id)) {
			return $domain_id;
		}
		else {
			$id = $this->add_domain($domain);
			if ($id != false) {
				return $id;
			}
		}
		return false;
	}

	function get_domain_ids($domains) {
		global $wpdb;
		if (!is_array($domains) || count($domains) == 0) {
			return false;
		}
		$domain_ids = array();
		$results = $wpdb->get_results("
			SELECT id, domain
			FROM $wpdb->ak_domains
			WHERE domain IN ('".implode("', '", $domains)."')
		");
		if ($results && count($results) > 0) {
			foreach ($results as $data) {
				$domain_ids[$data->domain] = $data->id;
			}
		}
		$missing = array();
		foreach ($domains as $domain) {
			if (!isset($domain_ids[$domain])) {
				$domain_ids[$domain] = $this->add_domain($domain);
			}
		}
		return $domain_ids;
	}
	
	function process_content($content, $post_id) {
		$links = $this->get_links($content);
		if ($links == false) {
			return;
		}
		
		aklh_log('Found '.count($links).' links in post id: '.$post_id);

		$domains = array();
		$domain_count = array();
		$harvest = array();
		
		foreach ($links as $link) {
// FeedBurner hack, working around this:
// http://alexking.org/blog/2006/12/01/why-i-dont-use-feedburner
			if (!empty($link)) {
				if (strstr($link, '/feeds.feedburner.com/') || strstr($link, '/~r/')) {
					require_once(ABSPATH.WPINC.'/class-snoopy.php');
					$snoop = new Snoopy;
					$snoop->maxlength = 2000;
					$snoop->read_timeout = $this->timeout;
					$snoop->fetch($link);
					if (!empty($snoop->lastredirectaddr)) {
						$link = $snoop->lastredirectaddr;
					}
				}
				$domain = $this->get_domain($link);
				if (!empty($domain)) {
					foreach ($this->exclude as $exclude) {
						if (strstr($domain, $exclude)) {
							return;
						}
					}
					if (isset($domain_count[$domain])) {
						$domain_count[$domain]++;
					}
					else {
						$domains[] = $domain;
						$domain_count[$domain] = 1;
					}
					$harvest[] = array($link, $domain);
				}

				aklh_log('Processed link: '.$link);

			}
			else {

				aklh_log('Skipped link: '.$link);

			}
		}
		
		if (count($domains) > 0) {
			$domain_ids = $this->get_domain_ids($domains);

			foreach ($harvest as $data) {
				$link = $data[0];
				$domain = $data[1];
				$this->add_link($link, $domain_ids[$domain], $post_id);
			}
			
			foreach ($domain_ids as $domain => $domain_id) {
				$this->set_domain_counter(null, $domain_id, $domain_count[$domain], '+');
			}
		}

	}
	
	function add_link($url, $domain_id, $post_id) {

		aklh_log('About to add link for post id: '.$post_id.' - '.$url);

		global $wpdb;
		$title = stripslashes($this->get_page_title($url));
		$result = $wpdb->query("
			INSERT INTO $wpdb->ak_linkharvest
			( post_id
			, url
			, title
			, domain_id
			, modified
			)
			VALUES
			( '".mysql_real_escape_string($post_id)."'
			, '".mysql_real_escape_string($url)."'
			, '".mysql_real_escape_string($title)."'
			, '".mysql_real_escape_string($domain_id)."'
			, '".current_time('mysql',1)."'
			)
		");
		if (!$result) {

			aklh_log('Failed to add link for post id: '.$post_id.' - '.$url);

			return false;
		}
		else {

			aklh_log('Added link for for post id: '.$post_id.' - '.$url);

			return true;
		}
	}
	
	function add_domain($domain) {
		global $wpdb;
		$title = stripslashes($this->get_page_title('http://'.$domain));
		$result = $wpdb->query("
			INSERT INTO $wpdb->ak_domains
			( domain
			, title
			, count
			)
			VALUES
			( '".mysql_real_escape_string($domain)."'
			, '".mysql_real_escape_string($title)."'
			, '0'
			)
		");
		if (!$result) {

			aklh_log('Failed to add domain: '.$domain);

			return false;
		}
		else {

			aklh_log('Added domain: '.$domain);

			return mysql_insert_id();
		}
	}
	
	function set_domain_counter($domain = null, $domain_id = null, $count, $mod = '+') {
		global $wpdb;
		if (!is_null($domain)) {
			$where = " WHERE domain = '".mysql_real_escape_string($domain)."' ";
		}
		else if (!is_null($domain_id)) {
			$where = " WHERE id = '".intval($domain_id)."' ";
		}
		if (isset($where)) {
			$result = $wpdb->query("
				UPDATE $wpdb->ak_domains
				SET count = count $mod ".intval($count)."
				$where
			");
			if ($result != false) {
				return true;
			}
		}
		return false;
	}
	
	function harvest_posts($post_ids = array(), $start = 0, $limit = null, $delete_old = false) {
		global $wpdb;
		if (is_array($post_ids) && count($post_ids) > 0) {
			if ($delete_old) {
				foreach ($post_ids as $post_id) {
					$links = $wpdb->get_results("
						SELECT *
						FROM $wpdb->ak_linkharvest
						WHERE post_id = $post_id
					");
					if (count($links) > 0) {
						$domains = array();
						foreach ($links as $link) {
							if (!isset($domains[$link->domain_id])) {
								$domains[$link->domain_id] = 1;
							}
							else {
								$domains[$link->domain_id]++;
							}
						}
						if (count($domains) > 0) {
							foreach ($domains as $id => $count) {
								$update = $wpdb->query("
									UPDATE $wpdb->ak_domains
									SET count = count - $count
									WHERE id = $id
								");
							}
						}
						$delete = $wpdb->query("
							DELETE
							FROM $wpdb->ak_linkharvest
							WHERE post_id = $post_id
						");
					}
				}
			}
			$posts = $wpdb->get_results("
				SELECT *
				FROM $wpdb->posts
				WHERE (
					post_status = 'publish'
					OR post_status = 'static'
				)
				AND ID IN (".implode(',', $post_ids).")
			");
		}
		else {
			if ($start == 0) {
				$this->clear_data();
			}
			if (is_null($limit)) {
				$limit = $this->default_limit;
			}
			$posts = $wpdb->get_results("
				SELECT *
				FROM $wpdb->posts
				WHERE (
					post_status = 'publish'
					OR post_status = 'static'
				)
				AND (
					post_content LIKE '%http://%'
					OR post_content LIKE '%https://%'
				)
				ORDER BY ID
				LIMIT $start, $limit
			");
		}
		foreach ($posts as $post) {
			if (function_exists('wp_is_post_revision') && wp_is_post_revision($post->ID)) {
				continue;
			}

			aklh_log('== Start processing post id: '.$post->ID);

			$this->process_content($post->post_content, $post->ID);
			$external = get_post_meta($post->ID, 'external_link', true);
			if (!empty($external)) {
				$this->process_content($external, $post->ID);
			}
			$via = get_post_meta($post->ID, 'via_link', true);
			if (!empty($via)) {
				$this->process_content($via, $post->ID);
			}

			aklh_log('== Done processing post id: '.$post->ID."\n");

		}
	}
	
	function post_delete($post_id) {
		global $wpdb;
		$links = $wpdb->get_results("
			SELECT *
			FROM $wpdb->ak_linkharvest
			WHERE post_id = '$post_id'
		");
		$urls = array();
		foreach ($links as $link) {
			$urls[] = $link->url;
		}
		$domains = $this->domain_counts($urls);
		foreach ($domains as $domain => $count) {
			$this->set_domain_counter($domain, null, $count, $mod = '-');
		}
		$wpdb->query("
			DELETE
			FROM $wpdb->ak_linkharvest
			WHERE post_id = '$post_id'
		");
	}
	
	function domain_counts($links) {
		$domain_counts = array();
		foreach ($links as $link) {
			$domain = $this->get_domain($link);
			if (!empty($domain)) {		
				if (isset($domain_count[$domain])) {
					$domain_count[$domain]++;
				}
				else {
					$domain_count[$domain] = 1;
				}
			}
		}
		return $domain_counts;
	}
	
	function get_domain($link) {
		$domain = '';
		$domain = str_replace(array('http://', 'https://', 'www.'), array(), $link);
		$end = strpos($domain, '/');
		if ($end === false) {
			$end = strlen($domain);
		}
		$domain = substr($domain, 0, $end);
		return $domain;
	}
	
	function get_page_title($url) {
// getting web page title code found here
// http://www.drquincy.com/resources/tutorials/webserverside/getremotewebpageinfo/#complete

		$title = '';

		require_once(ABSPATH.WPINC.'/class-snoopy.php');
		$snoop = new Snoopy;
		$snoop->maxlength = 2000;
		$snoop->read_timeout = $this->timeout;
		$snoop->fetch($url);

		$start = '<title>';
		$end = '<\/title>';
		preg_match("/$start(.*)$end/si", $snoop->results, $match);
		if (isset($match[1])) {
			$title = $match[1];
			$title = str_replace("\r", "\n", $title);
			if (strstr($title, "\n")) {
				$parts = explode("\n", $title);
				$title = $parts[0];
			}
			$title = strip_tags($title);
			$title = wp_filter_kses(trim($title));
		}

		return $title;
	}
	
	function show_harvest($limit = 50, $type = 'table') {
		global $wpdb;
		$domains = $wpdb->get_results("
			SELECT *
			FROM $wpdb->ak_domains
			ORDER BY count DESC
			LIMIT $limit
		");
		$i = 1;
		switch ($type) {
			case 'table':
				print('
<table class="aklh_harvest">
	<thead>
		<tr>
			<th>'.__('Web Site', 'link-harvest').'</th>
			<th>'.__('# of Links', 'link-harvest').'</th>
			<th><span class="hide">'.__('Show Links', 'link-harvest').'</span></th>
			<th><span class="hide">'.__('Show Posts', 'link-harvest').'</span></th>
		</tr>
	</thead>
	<tbody>
				');
				if (count($domains) == 0) {
					print('
		<tr><td colspan="4">'.__('No links harvested yet. (<a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?ak_action=harvest">Start Harvesting</a>)', 'link-harvest').'</td></tr>
					');
				}
				else {
					foreach ($domains as $domain) {
						if (!empty($domain->title)) {
							$title = $domain->title.' ('.$domain->domain.')';
						}
						else {
							$title = '('.$domain->domain.')';
						}
						if ($i % 2 == 0) {
							$class = ' class="alternate"';
						}
						else {
							$class = '';
						}
						print('
		<tr id="aklh_table_row_'.$domain->id.'"'.$class.'>
			<td><a href="http://'.$domain->domain.'">'.$title.'</a><div id="domain_'.$domain->id.'"></div></td>
			<td class="count">'.htmlspecialchars($domain->count).'</td>
			<td class="action"><a href="javascript:void(aklh_show_for_domain(\''.$domain->id.'\', \'links\'));">'.__('Show Links', 'link-harvest').'</td>
			<td class="action"><a href="javascript:void(aklh_show_for_domain(\''.$domain->id.'\', \'posts\'));">'.__('Show Posts', 'link-harvest').'</td>
		</tr>
						');
						$i++;
					}
				}
				print('
	</tbody>
</table>
				');
				break;
			case 'list':
				$items = array();
				foreach ($domains as $domain) {
					if (!empty($domain->title)) {
						$title = htmlspecialchars($domain->title).' ('.$domain->domain.')';
					}
					else {
						$title = '('.$domain->domain.')';
					}
					$items['http://'.$domain->domain] = $title;
				}
				print('
<ol class="aklh_harvest">'.$this->get_list_nodes($items).'</ol>
				');
				break;
		}
	}
	
	function get_list_nodes($items) {
		$output = '';
		foreach ($items as $url => $title) {
			$output .= '<li><a href="'.$url.'">'.$title.'</a></li>'."\n";
		}
		return $output;
	}
	
	function options_form() {
		switch ($this->harvest_enabled) {
			case '1':
				$enabled = ' checked="checked"';
				$disabled = '';
				break;
			case '0':
				$enabled = '';
				$disabled = ' checked="checked"';
				break;
		}
		if (get_option('aklh_token') == '0') {
			$token_options = '<option value="1">'.__('Yes', 'link-harvest').'</option><option value="0" selected="selected">'.__('No', 'link-harvest').'</option>';
		}
		else {
			$token_options = '<option value="1" selected="selected">'.__('Yes', 'link-harvest').'</option><option value="0">'.__('No', 'link-harvest').'</option>';
		}
		print('
			<div class="wrap">
				<h2>'.__('Link Harvest Options', 'link-harvest').'</h2>
				<form name="ak_linkharvest" action="'.get_bloginfo('wpurl').'/wp-admin/options-general.php" method="post">
					<fieldset class="options">
						<p>'.__('You may want to exclude certain domains from your harvested links. For example, you may have a number of links to pages/posts on your own site and including your own links in the harvest will make your own site appear in your <a href="index.php?page=link-harvest.php">links list</a>.', 'link-harvest').'</p>
						<p><label for="exclude">'.__('Domains to exclude from link harvesting (separated by spaces, example: <code>example.com '.$this->get_domain(get_bloginfo('home')).'</code>):', 'link-harvest').'</label></p>
						<p><textarea name="exclude" id="exclude">'.htmlspecialchars(implode(' ', $this->exclude)).'</textarea></p>
						<p>
							<label for="table_length">'.__('Number of domains to show in report table:', 'link-harvest').'</label>
							<input type="text" size="5" name="table_length" id="table_length" value="'.$this->table_length.'" />
						</p>
						<p>
							<label for="aklh_token">'.__('Enable <a href="#token">token method</a> for showing the links list:', 'link-harvest').'</label>
							<select name="aklh_token" id="token">'.$token_options.'</select>
						</p>
						<p>'.__('Once the initial link harvest is complete, it is a good idea to disable link harvesting as it can be resource intensive for your server. This is done for you automatically after a successful harvest, but you can manually control it here.', 'link-harvest').'</p>
						<ul>
							<li><input type="radio" name="harvest_enabled" value="1" id="harvest_enabled_y" '.$enabled.'/> <label for="harvest_enabled_y"><strong>Enable</strong> link harvest actions</label></li>
							<li><input type="radio" name="harvest_enabled" value="0" id="harvest_enabled_n" '.$disabled.'/> <label for="harvest_enabled_n"><strong>Disable</strong> link harvest actions</label></li>
						</ul>
						<input type="hidden" name="ak_action" value="update_linkharvest_settings" />
					</fieldset>
					<p class="submit">
						<input type="submit" name="submit" value="'.__('Update Link Harvest Settings', 'link-harvest').'" />
					</p>
				</form>
				<h2>'.__('Harvest Links', 'link-harvest').'</h2>
				<form>
					<fieldset>
						<p>'.__('When you are ready to harvest (or re-harvest) your links, press this button', 'link-harvest').'</p>
						<p class="submit">
							<input type="button" name="recount" value="'.__('(Re) Harvest All Links', 'link-harvest').'" onclick="if (jQuery(\'#harvest_enabled_y:checked\').size()) { location.href=\''.get_bloginfo('wpurl').'/wp-admin/options-general.php?ak_action=harvest\'; } else { alert(\'Please enable link harvesting, save your settings, then try again.\'); }" />
						</p>					
					</fieldset>
				</form>
				<h2>'.__('Backfill Empty Titles', 'link-harvest').'</h2>
				<p>'.__('If a few domains or pages did not get proper titles the first time around, you can try filling them in here.', 'link-harvest').'</p>
				<ul style="margin-bottom: 40px;">
					<li><a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?ak_action=backfill_domains">'.__('Backfill empty domain titles', 'link-harvest').'</a></li>
					<li><a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?ak_action=backfill_pages">'.__('Backfill empty page titles', 'link-harvest').'</a></li>
				</ul>
				<div id="aklh_template_tags">
					<h2>'.__('Showing the Links List', 'link-harvest').'</h2>
					<h3 id="token">'.__('Token Method', 'link-harvest').'</h3>
					<p>'.__('If you have enabled the token method above, you can simply add <strong>###linkharvest###</strong> to any post or page and your links list will be inserted at that place in the post/page.', 'link-harvest').'</p>
					<h3 id="template">'.__('Template Tag Method', 'link-harvest').'</h3>
					<p>'.__('You can always add a template tag to your theme (in a page template perhaps) to show your links list.', 'link-harvest').'</p>
					<dl>
						<dt><code>aklh_show_harvest($limit = 10, $type = &quot;table&quot; or &quot;list&quot;)</code></dt>
						<dd>
							<p>'.__('Put this tag outside of <a href="http://codex.wordpress.org/The_Loop">The Loop</a> (perhaps in your sidebar?) to show a list (like the archives/categories/links list) of the sites you link to most. All arguments are optional, the defaults are included in the example above.', 'link-harvest').'</p>
							<p>Examples:</p> 
							<ul>
								<li><code>&lt;?php aklh_show_harvest(50); ?> (table)</code></li>
								<li><code>
									&lt;li>&lt;h2>Links&lt;/h2><br />
									&nbsp;&nbsp;	&lt;?php aklh_top_links(10); ?> (list)<br />
									&lt;/li>
								</code></li>
							</ul>
						</dd>
					</dl>
				</div>
				<h2>'.__('README', 'link-harvest').'</h2>
				<p>'.__('Find answers to common questions here.', 'link-harvest').'</p>
				<iframe id="ak_readme" src="http://alexking.org/projects/wordpress/readme?project=link-harvest"></iframe>
			</div>
		');
	}
	
	function show_processing_page($type) {
		global $wpdb;
		switch ($type) {

			case 'harvest':
				$count = $wpdb->get_var("
					SELECT count(ID)
					FROM $wpdb->posts
					WHERE (
						post_status = 'publish'
						OR post_status = 'static'
					)
					AND post_content LIKE '%http://%'
				");
				$js = '
	var count = '.$count.';
	function harvest_links(start, limit) {
		jQuery("#submit").hide();
		jQuery("#progress, #cancel").show();
		jQuery("#count").load(
			"'.get_bloginfo('wpurl').'/wp-admin/options-general.php",
			{
				"ak_action": "harvest_posts",
				"start": start,
				"limit": limit
			},
			harvest_progress
		);
	}
	function harvest_progress() {
		var progress = jQuery("#count").html();
		if (progress == "") {
			harvest_error();
			return;
		}
		var processed_count = parseInt(progress);
		if (processed_count > 0 && processed_count < count) {
			harvest_links(processed_count, '.$this->default_limit.');
		}
		else if (processed_count >= count) {
			jQuery("#progress, #cancel").hide();
			jQuery("#complete").show();
		}
		else {
			harvest_error();
			return;
		}
	}
	function harvest_error() {
		jQuery("#progress, #cancel").hide();
		jQuery("#complete").show();
	}
				';
				$body = '
		<h1>'.__('Harvest Links', 'link-harvest').'</h1>
				';
				if ($count == 0) {
					$body .= '
		<p>'.__('Oops, didn\'t find any links to harvest.', 'link-harvest').'</p>
					';
				}
				else {
					$body .= '
		<p>'.__('Harvesting links from your posts and pages can take a little while. Once you click the <strong>Start Link Harvest</strong> button below, all of your posts and pages will be scanned for links and those links will be pulled out and stored in the Link Harvest database so we can do cool things with them for you.', 'link-harvest').'</p>
		<p>'.__('Note: This is a one time step, future posts and pages will be added to the Link Harvest as you create them (and removed if you delete them).', 'link-harvest').'</p>
		<p id="progress" class="center">'.__('Processed: ', 'link-harvest').'<strong><span id="count">0</span> / '.$count.'</strong> '.__('posts and pages.', 'link-harvest').'</p>
		<form action="#" method="get" onsubmit="return false;">
			<fieldset>
				<legend>'.__('Harvest Links', 'link-harvest').'</legend>
				<p id="submit" class="center"><input type="button" name="harvest_button" value="'.__('Start Link Harvest', 'link-harvest').'" onclick="harvest_links(0, '.$this->default_limit.'); return false;" /></p>
				<p id="cancel" class="center"><input type="button" name="cancel_button" value="'.__('Cancel', 'link-harvest').'" onclick="location.href=\''.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php\';" />
			</fieldset>
		</form>
		<p id="complete" class="center">'.__('The link harvest has completed successfully. View your <a href="'.get_bloginfo('wpurl').'/wp-admin/index.php?page=link-harvest.php">links list</a>.', 'link-harvest').'</p>
		<p id="error" class="center">'.__('The link harvest failed, make sure you have harvesting enabled in your <a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php">options</a>.', 'link-harvest').'</p>
		<p class="center"><a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php">'.__('Back to WordPress Admin', 'link-harvest').'</a></p>
					';
				}
				break;

			case 'backfill_domains':
				$count = $wpdb->get_var("
					SELECT count(id)
					FROM $wpdb->ak_domains
					WHERE title = ''
				");
				$js = '
	function backfill_titles(last, limit) {
		jQuery("#submit").hide();
		jQuery("#progress, #cancel").show();
		var url = "'.get_bloginfo('wpurl').'/wp-admin/options-general.php";
		var pars = "ak_action=backfill_domain_titles&last=" + last + "&limit=" + limit;
		jQuery("#last").load(
			"'.get_bloginfo('wpurl').'/wp-admin/options-general.php",
			{
				"ak_action": "backfill_domain_titles",
				"last": last,
				"limit": limit
			},
			backfill_progress
		);
	}
	function backfill_progress() {
		var last = jQuery("#last").html();
		if (last == "") {
			backfill_error();
			return;
		}
		if (last == "done") {
			jQuery("#progress, #cancel").hide();
			jQuery("#complete").show();
			return;
		}
		var counter = jQuery("#count");
		var processed_count = parseInt(counter.html());
		processed_count++;
		counter.html(processed_count);
		backfill_titles(last, 1);
	}
	function backfill_error() {
		jQuery("#progress, #cancel").hide();
		jQuery("#error").show();
	}
				';
				$body = '
		<h1>'.__('Backfill Empty Domain Titles', 'link-harvest').'</h1>
				';
				if ($count == '0') {
					$body .= '
		<p>'.__('Oops, didn\'t find any domains without titles.', 'link-harvest').'</p>
					';
				}
				else {
					$body .= '
		<p>'.sprintf(__('Sometimes a web page can be slow to load (for whatever reason). This will go through the <strong>%s</strong> domain(s) that have empty titles and try to fill them in for you.', 'link-harvest'), $count).'</p>
		<p id="progress" class="center">'.__('Processed: ', 'link-harvest').'<strong><span id="count">0</span> / '.$count.'</strong> '.__('empty domain titles.', 'link-harvest').'</p>
		<p id="last">0</p>
		<form action="#" method="get" onsubmit="return false;">
			<fieldset>
				<legend>'.__('Backfill Empty Domain Titles', 'link-harvest').'</legend>
				<p id="submit" class="center"><input type="button" name="backfill_button" value="'.__('Start', 'link-harvest').'" onclick="backfill_titles(0, 1); return false;" /></p>
				<p id="cancel" class="center"><input type="button" name="cancel_button" value="'.__('Cancel', 'link-harvest').'" onclick="location.href=\''.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php\';" />
			</fieldset>
		</form>
		<p id="complete" class="center">'.__('The domain title backfill has completed successfully. View your <a href="'.get_bloginfo('wpurl').'/wp-admin/index.php?page=link-harvest.php">updated links list</a>.', 'link-harvest').'</p>
		<p id="error" class="center">'.__('The domain title backfill failed, make sure you have harvest actions enabled in your <a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php">options</a>.', 'link-harvest').'</p>
					';
				}
				$body .= '
		<p class="center"><a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php">'.__('Back to WordPress Admin', 'link-harvest').'</a></p>
				';
				break;

			case 'backfill_pages':
				$count = $wpdb->get_var("
					SELECT count(id)
					FROM $wpdb->ak_linkharvest
					WHERE title = ''
				");
				$js = '
	function backfill_titles(last, limit) {
		jQuery("#submit").hide();
		jQuery("#progress, #cancel").show();
		jQuery("#last").load(
			"'.get_bloginfo('wpurl').'/wp-admin/options-general.php",
			{
				"ak_action": "backfill_page_titles",
				"last": last,
				"limit": limit
			},
			backfill_progress
		);
	}
	function backfill_progress() {
		var last = jQuery("#last").html();
		if (last == "") {
			backfill_error();
			return;
		}
		if (last == "done") {
			jQuery("#progress, #cancel").hide();
			jQuery("#complete").show();
			return;
		}
		var counter = jQuery("#count");
		var processed_count = parseInt(counter.html());
		processed_count++;
		counter.html(processed_count);
		backfill_titles(last, 1);
	}
	function backfill_error() {
		jQuery("#progress").hide();
		jQuery("#cancel, #error").show();
	}
				';
				$body = '
		<h1>'.__('Backfill Empty Page Titles', 'link-harvest').'</h1>
				';
				if ($count == '0') {
					$body .= '
		<p>'.__('Oops, didn\'t find any pages without titles.', 'link-harvest').'</p>
					';
				}
				else {
					$body .= '
		<p>'.sprintf(__('Sometimes a web page can be slow to load (for whatever reason). This will go through the <strong>%s</strong> page(s) that have empty titles and try to fill them in for you.', 'link-harvest'), $count).'</p>
		<p id="progress" class="center">'.__('Processed: ', 'link-harvest').'<strong><span id="count">0</span> / '.$count.'</strong> '.__('empty page titles.', 'link-harvest').'</p>
		<p id="last">0</p>
		<form action="#" method="get" onsubmit="return false;">
			<fieldset>
				<legend>'.__('Backfill Empty Page Titles', 'link-harvest').'</legend>
				<p id="submit" class="center"><input type="button" name="backfill_button" value="'.__('Start', 'link-harvest').'" onclick="backfill_titles(0, 1); return false;" /></p>
				<p id="cancel" class="center"><input type="button" name="cancel_button" value="'.__('Cancel', 'link-harvest').'" onclick="location.href=\''.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php\';" />
			</fieldset>
		</form>
		<p id="complete" class="center">'.__('The page title backfill has completed successfully. View your <a href="'.get_bloginfo('wpurl').'/wp-admin/index.php?page=link-harvest.php">updated links list</a>.', 'link-harvest').'</p>
		<p id="error" class="center">'.__('The page title backfill failed, make sure you have harvest actions enabled in your <a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php">options</a>.', 'link-harvest').'</p>
					';
				}
				$body .= '
		<p class="center"><a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php">'.__('Back to WordPress Admin', 'link-harvest').'</a></p>
				';
				break;
		}
		if (!$this->harvest_enabled) {
			$body = '
				<h1>'.__('Link Harvest', 'link-harvest').'</h1>
				<p>'.__('Oops, harvest actions aren\'t enabled. Turn them on <a href="'.get_bloginfo('wpurl').'/wp-admin/options-general.php?page=link-harvest.php">in your options</a>', 'link-harvest').'</p>
			';
		}
		header("Content-type: text/html");
		print('
<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>'.__('Link Harvest', 'link-harvest').'</title>
	<script src="'.get_bloginfo('wpurl').'/wp-includes/js/jquery/jquery.js" type="text/javascript"></script>
	<script type="text/javascript">
	'.$js.'
	</script>
	<style type="text/css">
	body {
		background: #efefef;
		font: 12px Verdana, sans-serif;
		line-height: 150%;
		text-align: center;
	}
	.center {
		text-align: center;
	}
	form, fieldset {
		border: 0;
		margin: 0 auto;
		padding: 0;
	}
	legend {
		display: none;
	}
	input {
		font-weight: bold;
		padding: 10px 20px;
	}
	#body {
		background: #fff;
		border: 1px solid #ccc;
		margin: 20px auto;
		padding: 20px;
		text-align: left;
		width: 400px;
	}
	h1, #submit, #cancel {
		margin: 0 auto;
		padding: 10px 0;
	}
	#cancel {
		display: none;
	}
	#progress {
		background: #ffc;
		border: 1px solid #999;
		display: none;
		font-size: 14px;
		padding: 10px;
	}
	#complete {
		background: #3c6;
		border: 1px solid #999;
		display: none;
		font-size: 14px;
		font-weight: bold;
		padding: 10px;
	}
	#error {
		background: #f66;
		border: 1px solid #999;
		display: none;
		font-size: 14px;
		font-weight: bold;
		padding: 10px;
	}
	#last {
		display: none;
	}
	</style>
</head>
<body>
	<div id="body">
'.$body.'
	</div>
</body>
</html>
		');
		die();
	}
}

// -- HOOKABLE FUNCTIONS

function aklh_publish($post_id) {
	global $aklh;
	$aklh->harvest_posts(array($post_id));
}

function aklh_post_delete($post_id) {
	global $aklh;
	$aklh->post_delete($post_id);
}

function aklh_options_form() {
	global $aklh;
	$aklh->options_form();
}

function aklh_options() {
	if (function_exists('add_options_page')) {
		add_options_page(
			__('Link Harvest Options', 'link-harvest')
			, __('Link Harvest', 'link-harvest')
			, 10
			, basename(__FILE__)
			, 'aklh_options_form'
		);
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page(
			'index.php'
			, __('Link Harvest', 'link-harvest')
			, __('Link Harvest', 'link-harvest')
			, 0
			, basename(__FILE__)
			, 'aklh_admin_show_harvest'
		);
	}
}

wp_enqueue_script('jquery');

function aklh_head() {
	print('
		<script type="text/javascript" src="'.get_bloginfo('wpurl').'/index.php?ak_action=lh_js"></script>
		<link rel="stylesheet" type="text/css" href="'.get_bloginfo('wpurl').'/index.php?ak_action=lh_css" />
	');
}

function aklh_admin_head() {
	print('
		<script type="text/javascript" src="'.get_bloginfo('wpurl').'/index.php?ak_action=lh_js"></script>
		<link rel="stylesheet" type="text/css" href="'.get_bloginfo('wpurl').'/index.php?ak_action=lh_css" />
	');
}

function aklh_admin_show_harvest() {
	global $aklh;
	print('
		<div class="wrap">
			<h2>'.__('Link Harvest', 'link-harvest').'</h2>
			<p>'.__('Set domains to exclude and how many links to display in this list on the <a href="options-general.php?page=link-harvest.php">options page</a>.', 'link-harvest').'</p>
	');
	$aklh->show_harvest();
	print('
		</div>
	');
}

function aklh_request_handler() {
	global $wpdb, $aklh;
	if (!empty($_POST['ak_action'])) {

		ini_set('display_errors', '0');
		ini_set('error_reporting', E_ERROR);

		switch($_POST['ak_action']) {
			case 'update_linkharvest_settings': 
				$aklh = new ak_link_harvest;
				$aklh->update_settings();
				break;
			case 'harvest_posts':
				ini_set('display_errors', '0');
				ini_set('error_reporting', E_PARSE);

				@set_time_limit(999999999999999999999);
				
				if ($aklh->harvest_enabled != 1) {
					die('disabled');
				}
				if (!empty($_POST['start'])) {
					$start = intval($_POST['start']);
				}
				else {
					$start = 0;
				}
				if (!empty($_POST['limit'])) {
					$limit = intval($_POST['limit']);
				}
				else {
					$limit = $aklh->default_limit;;
				}
				
				if ($start == 0) {
					aklh_log("\n\n\n".'Starting a new harvest'."\n".'PHP Version is: '.phpversion());
					$plugins = get_option('active_plugins');
					if (is_array($plugins) && count($plugins) > 0) {
						foreach ($plugins as $plugin) {
							aklh_log('Active plugin: '.$plugin);
						}
					}
				}

				aklh_log("\n".'Starting with offset "'.$start.'"'."\n");

				$aklh->harvest_posts(null, $start, $limit);

				$count = $wpdb->get_var("
					SELECT count(ID)
					FROM $wpdb->posts
					WHERE (
						post_status = 'publish'
						OR post_status = 'static'
					)
					AND (
						post_content LIKE '%http://%'
						OR post_content LIKE '%https://%'
					)
				");
				$completed = ($start + $limit);
				if ($completed >= $count) {
					update_option('aklh_harvest_enabled', '0');
					$completed = $count;
				}
				
				aklh_log('Returning "'.''.$completed.'"'."\n");
				
				die(''.$completed);
				break;
			case 'backfill_domain_titles':
				$aklh->timeout = 6;
				if (!empty($_POST['last'])) {
					$last = intval($_POST['last']);
				}
				else {
					$last = 0;
				}
				if (!empty($_POST['limit'])) {
					$limit = intval($_POST['limit']);
				}
				else {
					$limit = 1;
				}
				$domains = $wpdb->get_results("
					SELECT *
					FROM $wpdb->ak_domains
					WHERE title = ''
					AND id > $last
					ORDER BY id
					LIMIT $limit
				");
				if (count($domains) > 0) {
					foreach ($domains as $domain) {
						$title = $aklh->get_page_title('http://'.$domain->domain);
						if (empty($title)) {
							$title = $aklh->get_page_title('http://www.'.$domain->domain);
						}
						if (!empty($title)) {
							$result = $wpdb->query("
								UPDATE $wpdb->ak_domains
								SET title = '".mysql_real_escape_string($title)."'
								WHERE id = '$domain->id'
							");
						}
					}
					$completed = $domain->id;
				}
				else {
					$completed = 'done';
				}
				die(''.$completed);
				break;
			case 'backfill_page_titles':
				$aklh->timeout = 6;
				if (!empty($_POST['last'])) {
					$last = intval($_POST['last']);
				}
				else {
					$last = 0;
				}
				if (!empty($_POST['limit'])) {
					$limit = intval($_POST['limit']);
				}
				else {
					$limit = 1;
				}
				$links = $wpdb->get_results("
					SELECT *
					FROM $wpdb->ak_linkharvest
					WHERE title = ''
					AND id > $last
					ORDER BY id
					LIMIT $limit
				");
				if (count($links) > 0) {
					foreach ($links as $link) {
						$title = $aklh->get_page_title($link->url);
						if (!empty($title)) {
							$result = $wpdb->query("
								UPDATE $wpdb->ak_linkharvest
								SET title = '".mysql_real_escape_string($title)."'
								WHERE id = '$link->id'
							");
						}
					}
					$completed = $link->id;
				}
				else {
					$completed = 'done';
				}
				die(''.$completed);
				break;
		}
	}
	if (!empty($_GET['ak_action'])) {
		switch($_GET['ak_action']) {
			case 'harvest':
				$aklk = new ak_link_harvest;
				$aklk->get_settings();
				$aklk->show_processing_page('harvest');
				break;
			case 'backfill_domains':
				$aklk = new ak_link_harvest;
				$aklk->get_settings();
				$aklk->show_processing_page('backfill_domains');
				break;
			case 'backfill_pages':
				$aklk = new ak_link_harvest;
				$aklk->get_settings();
				$aklk->show_processing_page('backfill_pages');
				break;
			case 'show_links':
				if (empty($_GET['domain_id'])) {
					die();
				}
				$domain_id = intval($_GET['domain_id']);
				$aklk = new ak_link_harvest;
				$aklk->get_settings();
				$links = $wpdb->get_results("
					SELECT DISTINCT(url), title
					FROM $wpdb->ak_linkharvest
					WHERE domain_id = '$domain_id'
					ORDER BY post_id DESC
				");
				print('
					<a href="javascript:void(jQuery(\'#domain_'.$domain_id.'\').slideUp());" class="close">Close</a>
					<h4>'.__('Links', 'link-harvest').'</h4>
					<ul>
				');
				if (count($links) > 0) {
					foreach ($links as $link) {
						if (!empty($link->title)) {
							$title = $link->title;
						}
						else {
							$title = substr($link->url, 0, 60);
						}
						print('
							<li><a href="'.$link->url.'">'.$title.'</a></li>
						');
					}
				}
				else {
					print('
						<li>'.__('(none found)', 'link-harvest').'</li>
					');
				}
				print('
					</ul>
				');
				die();
				break;
			case 'show_posts':
				if (empty($_GET['domain_id'])) {
					die();
				}
				$domain_id = intval($_GET['domain_id']);
				$aklk = new ak_link_harvest;
				$aklk->get_settings();
				$posts = $wpdb->get_results("
					SELECT p.*
					FROM $wpdb->posts p
					LEFT JOIN $wpdb->ak_linkharvest lh
					ON p.ID = lh.post_id
					WHERE lh.domain_id = '$domain_id'
					AND (
						post_status = 'publish'
						OR post_status = 'static'
					)
					GROUP BY p.ID
					ORDER BY p.post_date DESC
				");
				print('
					<a href="javascript:void(jQuery(\'#domain_'.$domain_id.'\').slideUp());" class="close">Close</a>
					<h4>'.__('Posts and Pages', 'link-harvest').'</h4>
					<ul>
				');
				if (count($posts) > 0) {
					global $post;
					foreach ($posts as $post) {
						print('
							<li><a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a> ('.get_the_time('Y-m-d').')</li>
						');
					}
				}
				else {
					print('
						<li>'.__('(none found)', 'link-harvest').'</li>
					');
				}
				print('
					</ul>
				');
				die();
				break;
			case 'lh_css':
				header("Content-type: text/css");
?>
table.aklh_harvest {
}
table.aklh_harvest th, table.aklh_harvest td {
	padding: 3px;
}
table.aklh_harvest td {
	vertical-align: top;
}
table.aklh_harvest th span.hide {
	display: none;
}
table.aklh_harvest td.count {
	text-align: right;
	width: 10%;
}
table.aklh_harvest td.action {
	text-align: center;
	width: 10%;
}
table.aklh_harvest td a.close {
	display: block;
	float: right;
}
table.aklh_harvest td span.loading {
	color: #999;
	display: block;
	padding: 5px;
}
#aklh_template_tags dl {
	margin-left: 10px;
}
#aklh_template_tags dl dt {
	font-weight: bold;
	margin: 0 0 5px 0;
}
#aklh_template_tags dl dd {
	margin: 0 0 15px 0;
	padding: 0 0 0 15px;
}
#exclude {
	height: 60px;
	width: 80%;
}
#ak_readme {
	height: 300px;
	width: 95%;
}
<?php
				die();
				break;
			case 'lh_js':
				header("Content-type: text/javascript");
?>
function aklh_show_for_domain(domain_id, type) {
	switch (type) {
		case 'posts':
		case 'links':
			var pars = {
				"ak_action": "show_" + type,
				"domain_id": domain_id
			};
	}
	var target = jQuery('#domain_' + domain_id);
	target.html('<span class="loading">Loading...</span>').show();
	jQuery.get("<?php bloginfo('wpurl'); ?>/index.php", pars, function(data) {
		target.hide().html(data).slideDown();
	});
}
<?php
			die();
			break;
		}
	}
}
add_action('init', 'aklh_request_handler');

function aklh_the_content($content) {
	if (strstr($content, '###linkharvest###')) {
		$content = str_replace('###linkharvest###', aklh_get_harvest(), $content);
	}
	return $content;
}
if ($aklh->token) {
	add_action('the_content', 'aklh_the_content');
}

function aklh_the_excerpt($content) {
	return str_replace('###linkharvest###', '', $content);;
}
if ($aklh->token) {
	add_action('the_excerpt', 'aklh_the_excerpt');
}

function aklh_get_harvest($count = 50) {
	ob_start();
	aklh_show_harvest($count);
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

// -- TEMPLATE FUNCTIONS

function aklh_show_harvest($count = 50) {
	global $aklh;
	$aklh->show_harvest($count, 'table');
	print('
		<p id="aklh_credit">'.__('Powered by ', 'link-harvest').'<a href="http://alexking.org/projects/wordpress">Link Harvest</a>.</p>
	');
}
function aklh_top_links($count = 10) {
	global $aklh;
	$aklh->show_harvest($count, 'list');
	print('
		<p id="aklh_credit">'.__('Powered by ', 'link-harvest').'<a href="http://alexking.org/projects/wordpress">Link Harvest</a>.</p>
	');
}

// debug logging

function aklh_log($msg) {
	if (!AKLH_DEBUG) {
		return;
	}
	$logfile = dirname(__FILE__).'/aklh_log.txt';
	$file = fopen($logfile, 'a');
	if ($file) {
		if (!fwrite($file, $msg."\n")) {
			die('Error writing to log file.');
		}
	}
}

// -- GET HOOKED

add_action('init', 'aklh_init');
add_action('wp_head', 'aklh_head');

add_action('admin_menu', 'aklh_options');
add_action('admin_head', 'aklh_admin_head');

add_action('publish_post', 'aklh_publish', 999);
add_action('delete_post', 'aklh_post_delete');

add_action('publish_page', 'aklh_publish', 999);
add_action('delete_page', 'aklh_post_delete');

endif; // end weirdness check

?>