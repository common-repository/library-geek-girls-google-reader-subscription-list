<?php
/*
Plugin Name: LibraryGeekGirl's Google Reader Subscription List
Description: Allows insertion of Google Reader links on a page
Author: D Ruth Bavousett
Author URI: http://about.me/druthb
Plugin URI: http://www.librarygeekgirl.net/downloads/
License: GPL3
Version: 1.0
*/
?>
<?php
/*  Copyright 2012  D Ruth Bavousett  (email : druthb@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 3, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*  CREDIT WHERE CREDIT IS DUE:

    I learned a lot -- and cribbed a fair bit of this -- from:
   
    * Timothy Broder's "Google Reader Subscription List" plugin
    * Herman J. Radtke's "Google Reader Subscriptions" widget plugin
*/
/**
 * @todo encrypt the password for storage!
 * @todo cache the results to speed it up a bit!
 */
?>
<?php

/* These three hooks plug in the four functions below to all of WordPress's 
   lovely pre-exising plumbing.
*/
add_action('admin_menu','lgg_reader_create_menu');
add_action('admin_init','lgg_reader_register_settings');
add_shortcode('lgg_reader','lgg_reader_display');

/* add options page */
function lgg_reader_create_menu() {
	add_options_page('Google Reader Options',
				 'Google Reader',
				 'manage_options',
				 'lgg-reader',
				 'lgg_reader_show_options_page');
}

/* actual options page content, called by lgg_reader_create_menu */
function lgg_reader_show_options_page() {

?>
<div class="wrap">
	<h2>Google Reader Subscription List</h2>
	<form method="post" action="options.php">
		<?php settings_fields('lgg-reader'); ?>
		<?php $options = get_option('lgg-reader-options'); ?>
		<table class="form-table">
		<tr valign="top">
			<th scope="row">Google ID</th>
			<td><input type="text" name="lgg-reader-options[user]" value="<?php echo $options['user']; ?>"/></td>
		</tr>
 		<tr valign="top">
  			<th scope="row">Google Password</th>
			<td><input type="password" name="lgg-reader-options[pass]" value="<?php echo $options['pass']; ?>"/></td>
		</tr></table>
		<?php submit_button(); ?></form></div>
<?php

}

/* set up place to store options */
function lgg_reader_register_settings() {
	register_setting('lgg-reader','lgg-reader-options');
}

/* this is the main function, called by shortcode; it returns the HTML to 
   put in the page!
*/
function lgg_reader_display($atts) {
	extract( shortcode_atts( array (
		'include' => '',
		'exclude' => '',
		'hide_header' => 0,
	), $atts ) );
	$options=get_option('lgg-reader-options');

	$url = "https://www.google.com/accounts/ClientLogin?service=reader&Email={$options['user']}&Passwd={$options['pass']}";
	$ch = curl_init($url);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	$output=curl_exec( $ch );
	if ( CURLE_OK !== curl_errno( $ch ) ) return '';

	curl_close( $ch );
	$parts = explode("\n", $output);
	$auth = array();

	foreach ($parts as $part) {
		$tmp = explode('=', $part);
		if ( $tmp[0] && $tmp[1] ) $auth[$tmp[0]] = $tmp[1];
	}

	if ( !isset($auth['Auth']) ) return '';

	$headers = array(
		"Authorization: GoogleLogin auth={$auth['Auth']}",
	);
	$ch = curl_init('http://www.google.com/reader/api/0/subscription/list');
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	$response = curl_exec( $ch );

	if ( CURLE_OK !== curl_errno( $ch ) ) return '';

	curl_close( $ch );

	$feed = simplexml_load_string( $response );
	$hashmap = array();
			
	foreach ($feed->list->object as $this_subscription) {
		$cat   = $this_subscription->list->object->string[1];
				
		if ( '' == $cat ) $cat = 'unfiled';
		$current_category = $hashmap["$cat"];

		if ( null == $current_category ) {
			$current_category = array($this_subscription);
			$hashmap["$cat"] = $current_category;
		} else {
			array_push($current_category, $this_subscription);
   			$hashmap["$cat"] = $current_category;
		}
	}

	ksort($hashmap);

	$include = strtolower($include);
	$exclude = strtolower($exclude);
	if ( '*' == $include || 'all' == $include ) $include = '';
	if ( '' !== $include ) $exclude = '';
	$yes_len = strlen($include);
	$no_len = strlen($exclude);
	$html_output = '';

	foreach ($hashmap as $cat=>$this_category) {
		$lowercat = strtolower( $cat );
		$yes_pos  = strpos( $include, $lowercat );
		$no_pos   = strpos( $exclude, $lowercat );
		if ( ( 0 == $yes_len || false !== $yes_pos ) && ( false === $no_pos ) ) { 
			if ( !$hide_header ) $html_output .= "<h2>$cat</h2><br />";
			$html_output .= '<ul>';
			foreach ( $this_category as $this_subscription ) {
				$url   = $this_subscription->string[3]; 
				$title = $this_subscription->string[1];
				$html_output .= "<li><a href='$url' target='_blank'>$title</a></li>";
			}
			$html_output .= '</ul><br />';
		}
	} 
	return $html_output;
}
?>
