<?php
/*
Plugin Name: Content Filter
Plugin URI: http://wordpress.org/plugins/wp-content-filter/
Description: Censor and filter out profanity, swearing, abusive comments and any other keywords from your site.
Version: 3.1.0
Author: David Gwyer
Author URI: http://www.wpgoplugins.com
*/

/*
	Copyright 2017 David Gwyer (email : david@wpgoplugins.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// pccf_ prefix is derived from [p]ress [c]oders [c]ontent [f]ilter.
register_activation_hook( __FILE__, 'pccf_add_defaults' );
register_uninstall_hook( __FILE__, 'pccf_delete_plugin_options' );
add_action( 'admin_init', 'pccf_init' );
add_action( 'admin_menu', 'pccf_add_options_page' );
add_action( 'plugins_loaded', 'pccf_contfilt' );
add_filter( 'plugin_row_meta', 'pccf_plugin_action_links', 10, 2 );
add_filter( 'plugin_action_links', 'pccf_plugin_settings_link', 10, 2 );
add_action( 'admin_enqueue_scripts', 'pccf_register_admin_scripts' );

// Setup Plugin default options.
global $pccf_defaults;
$pccf_defaults = array(
	'chk_post_title'         => '1',
	'chk_post_content'       => '1',
	'chk_comments'           => '1',
	'txtar_keywords'         => 'Saturn, Jupiter, Pluto',
	'txt_exclude'            => '',
	'rdo_word'               => 'all',
	'drp_filter_char'        => 'star',
	'rdo_case'               => 'insen',
	'chk_default_options_db' => '',
	'rdo_strict_filtering'   => 'strict_on',
);

/**
 * Delete options table entries ONLY when plugin deactivated AND deleted.
 */
function pccf_delete_plugin_options() {
	delete_option( 'pccf_options' );
}

/**
 * Define default option settings.
 */
function pccf_add_defaults() {

	global $pccf_defaults;
	$tmp = get_option( 'pccf_options', $pccf_defaults );

	if ( ( ( isset( $tmp['chk_default_options_db'] ) && '1' == $tmp['chk_default_options_db'] ) ) || ( ! is_array( $tmp ) ) ) {
		update_option( 'pccf_options', $pccf_defaults );
	}
}

/**
 * Init plugin options to white list our options.
 */
function pccf_init() {
	// put the below into a function and add checks all sections (especially radio buttons) have a valid choice (i.e. no section is blank).
	// this is primarily to check newly added options have correct initial values.
	global $pccf_defaults;
	$tmp = get_option( 'pccf_options', $pccf_defaults );

	if ( ! $tmp['rdo_strict_filtering'] ) { // Check strict filtering option has a starting value.
		$tmp['rdo_strict_filtering'] = 'strict_off';
		update_option( 'pccf_options', $tmp );
	}
	register_setting( 'pccf_plugin_options', 'pccf_options', 'pccf_validate_options' );
}

/**
 * Add menu page.
 */
function pccf_add_options_page() {

	$page = add_options_page( 'WP Content Filter Options Page', 'WP Content Filter', 'manage_options', 'wp-content-filter-options', 'pccf_render_form' );

	/* Enqueue scripts and styles for the theme option page */
	add_action( "admin_print_styles-$page", 'pccf_theme_admin_styles' );
}

/**
 * Enqueue styles for theme options page.
 */
function pccf_theme_admin_styles() {

	/* Styles for plugin options page only. */
	wp_enqueue_style( 'pccf-admin-css' );
}

/**
 * Draw the menu page itself .
 */
function pccf_render_form() {
	?>
	<style>
		a:focus{ box-shadow: none;}
		.pcdm.dashicons { width: 32px; height: 32px; font-size: 32px; }
		.pcdm.dashicons-yes { color: #1cc31c; }
		.pcdm.dashicons-no { color: red; }
	</style>
	<div class="wrap">

		<div style="display:flex;justify-content: space-between;">
			<h2>WP Content Filter Options</h2>
			<div>
				<a target="_blank" title="<?php esc_attr_e( 'We love to develop WordPress plugins!', 'pccf' ); ?>" alt="<?php esc_attr_e( 'WPGO Plugins Site', 'pccf' ); ?>" href="https://wpgoplugins.com/">
					<img src="<?php echo esc_url( plugins_url( '/wp-content-filter/images/wpgo_plugins_logo.png' ) ); ?>">
				</a>
			</div>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( 'pccf_plugin_options' ); ?>
			<?php
			global $pccf_defaults;
			$options = get_option( 'pccf_options', $pccf_defaults );
			?>
			<table class="form-table">

				<tr valign="top">
					<th scope="row">Like the plugin?</th>
					<td>
						<p>Then why not upgrade to Pro and access powerful additional features. Try risk free today with our <span style="font-weight: bold;">100% money back guarantee!</span></p>
						<div style="margin-top:10px;"><input class="button" type="button" value="Upgrade to Pro" onClick="window.open('https://wpgoplugins.com/plugins/content-censor/')"></div>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">Read all about it!</th>
					<td>
						<p>Signup to our plugin newsletter for news and updates about our latest work, and what's coming.</p>
						<div style="margin-top:10px;"><input class="button" type="button" value="Subscribe here..." onClick="window.open('http://eepurl.com/bXZmmD')"></div>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">Buy me a coffee?</th>
					<td>
						<p>If you use this FREE Plugin on your website <b><em>please</em></b> consider making a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=BG4RPM3LLP5GU" target="_blank">donation</a> to support continued development. Thank you.<span style="margin-left:5px;" class="dashicons dashicons-smiley"></span></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">Keep in touch...</th>
					<td>
						<div class="keep-in-touch">
							<span>
								<a href="http://www.twitter.com/dgwyer" title="Follow us on Twitter" target="_blank">
									<img src="<?php echo esc_url( plugins_url( '/wp-content-filter/images/twitter.png' ) ); ?>">
								</a>
							</span>
							<span>
								<a href="https://www.facebook.com/wpgoplugins/" title="Our Facebook page" target="_blank">
									<img src="<?php echo esc_url( plugins_url( '/wp-content-filter/images/facebook.png' ) ); ?>">
								</a>
							</span>
							<span>
								<a href="https://www.youtube.com/channel/UCWzjTLWoyMgtIfpDgJavrTg" title="View our YouTube channel" target="_blank">
									<img src="<?php echo esc_url( plugins_url( '/wp-content-filter/images/yt.png' ) ); ?>">
								</a>
							</span>
							<span>
								<a style="text-decoration:none;" title="Need help with ANY aspect of WordPress? We're here to help!" href="https://wpgoplugins.com/need-help-with-wordpress/" target="_blank">
									<span style="margin-left:-2px;color:#d41515;font-size:39px;line-height:32px;width:39px;height:39px;" class="dashicons dashicons-sos"></span>
								</a>
							</span>
						</div>
					</td>
				</tr>

				<tr valign="top"><td colspan="2"><hr></td></tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Keywords to Remove', 'pccf' ); ?></th>
					<td>
						<textarea name="pccf_options[txtar_keywords]" rows="7" cols="50" type="textarea"><?php echo esc_textarea( $options['txtar_keywords'] ); ?></textarea>

						<p class="description"><?php esc_html_e( 'Separate keywords with commas.', 'pccf' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Exclude Pages', 'pccf' ); ?></th>
					<td>
						<input type="text" class="regular-text code" name="pccf_options[txt_exclude]" value="<?php echo esc_attr( $options['txt_exclude'] ); ?>">

						<p class="description"><?php esc_html_e( 'Enter a comma separated list of page IDs that will be excluded from the content filter.', 'pccf' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Strict Filtering', 'pccf' ); ?></th>
					<td>
						<label>
							<input name="pccf_options[rdo_strict_filtering]" type="radio" value="strict_off" <?php checked( 'strict_off', $options['rdo_strict_filtering'] ); ?>>
							<?php esc_html_e( 'Strict Filtering OFF', 'pccf' ); ?>
							<span style="font-family:lucida console;color:#888;margin-left:119px;"><?php esc_html_e( '[e.g. \'ass\' becomes \'p***able\']', 'pccf' ); ?></span>
						</label><br>
						<label>
							<input name="pccf_options[rdo_strict_filtering]" type="radio" value="strict_on" <?php checked( 'strict_on', $options['rdo_strict_filtering'] ); ?>>
							<?php esc_html_e( 'Strict Filtering ON (recommended)', 'pccf' ); ?>
							<span style="font-family:lucida console;color:#888;margin-left:32px;"><?php esc_html_e( '[e.g. \'ass\' becomes \'passable\']', 'pccf' ); ?></span>
						</label>

						<p class="description"><?php esc_html_e( 'Note: When strict filtering is ON, embedded keywords are no longer filtered.', 'pccf' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">Filter main content</th>
					<td>
						<label><input name="pccf_options[chk_post_content]" type="checkbox" value="1" 
						<?php
						if ( isset( $options['chk_post_content'] ) ) {
								checked( '1', $options['chk_post_content'] );
						}
						?>
							> Post Content/Excerpt
							<?php
							if ( class_exists( 'bbPress' ) ) {
								echo ' (including bbPress content)';
							}
							?>
							</label><br>
						<label><input name="pccf_options[chk_post_title]" type="checkbox" value="1" 
						<?php
						if ( isset( $options['chk_post_title'] ) ) {
								checked( '1', $options['chk_post_title'] );
						}
						?>
							> Post Titles
							<?php
							if ( class_exists( 'bbPress' ) ) {
								echo ' (including bbPress titles)';
							}
							?>
							</label><br>
						<label><input name="pccf_options[chk_comments]" type="checkbox" value="1" 
						<?php
						if ( isset( $options['chk_comments'] ) ) {
								checked( '1', $options['chk_comments'] );
						}
						?>
							> Comments <span class="description">(filters comment author names too)</span></label><br>
						<label><input name="pccf_options[chk_tags]" type="checkbox" value="1" 
						<?php
						if ( isset( $options['chk_tags'] ) ) {
								checked( '1', $options['chk_tags'] );
						}
						?>
							> Tags</label><br>
						<label><input name="pccf_options[chk_tag_cloud]" type="checkbox" value="1" 
						<?php
						if ( isset( $options['chk_tag_cloud'] ) ) {
								checked( '1', $options['chk_tag_cloud'] );
						}
						?>
							> Tag Cloud</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Word Rendering</th>
					<td>
						<label><input name="pccf_options[rdo_word]" type="radio" value="first" <?php checked( 'first', $options['rdo_word'] ); ?>> First letter retained
							<span style="font-family:lucida console;color:#888;margin-left:39px;">[e.g. 'dog' becomes 'd**']</span></label><br>
						<label><input name="pccf_options[rdo_word]" type="radio" value="all" <?php checked( 'all', $options['rdo_word'] ); ?>> All letters removed
							<span style="font-family:lucida console;color:#888;margin-left:40px;">[e.g. 'dog' becomes '***']</span></label><br>
						<label><input name="pccf_options[rdo_word]" type="radio" value="firstlast" <?php checked( 'firstlast', $options['rdo_word'] ); ?>> First/last letter retained
							<span style="font-family:lucida console;color:#888;margin-left:16px;">[e.g. 'dog' becomes 'd*g']</span></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Filter Character</th>
					<td>
						<select name='pccf_options[drp_filter_char]'>
							<option value='star' <?php selected( 'star', $options['drp_filter_char'] ); ?>>[*] Asterisk</option>
							<option value='dollar' <?php selected( 'dollar', $options['drp_filter_char'] ); ?>>[$] Dollar</option>
							<option value='question' <?php selected( 'question', $options['drp_filter_char'] ); ?>>[?] Question</option>
							<option value='exclamation' <?php selected( 'exclamation', $options['drp_filter_char'] ); ?>>[!] Exclamation</option>
							<option value='hyphen' <?php selected( 'hyphen', $options['drp_filter_char'] ); ?>>[-] Hyphen</option>
							<option value='hash' <?php selected( 'hash', $options['drp_filter_char'] ); ?>>[#] Hash</option>
							<option value='tilde' <?php selected( 'tilde', $options['drp_filter_char'] ); ?>>[~] Tilde</option>
							<option value='blank' <?php selected( 'blank', $options['drp_filter_char'] ); ?>>[ ] Blank</option>
						</select>

						<p class="description">'Blank' completely removes the filtered keywords from view.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Case Matching</th>
					<td>
						<label><input name="pccf_options[rdo_case]" type="radio" value="sen" <?php checked( 'sen', $options['rdo_case'] ); ?>> Case Sensitive</label><br>
						<label><input name="pccf_options[rdo_case]" type="radio" value="insen" <?php checked( 'insen', $options['rdo_case'] ); ?>> Case Insensitive (recommended)</label>

						<p class="description">Note: 'Case Insensitive' matching type is better as it captures more words!</p>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2">
						<div style="margin-top:10px;"></div>
					</td>
				</tr>
				<tr valign="top" style="border-top:#dddddd 1px solid;">
					<th scope="row">Database Options</th>
					<td>
						<label><input name="pccf_options[chk_default_options_db]" type="checkbox" value="1" 
						<?php
						if ( isset( $options['chk_default_options_db'] ) ) {
								checked( '1', $options['chk_default_options_db'] );
						}
						?>
							> Restore defaults upon plugin deactivation/reactivation</label>

						<p class="description">Only check this if you want to reset plugin settings upon reactivation.</p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'pccf' ); ?>">
			</p>
		</form>

		<div style="margin: 20px 0;">

			<hr>
			<div>
				<h2 style="margin-bottom:0;">Upgrade to Pro</h2>
				<p>Upgrade and access great new features, such as the batch processor to actively scan your entire site. *NEW* Filter BuddyPress content now too! Try risk free today with our 100% money back guarantee!</p>
			</div>

			<div style="display:flex;">
				<div style="margin:15px 0;"><iframe width="350" height="197" src="https://www.youtube.com/embed/fBtf4QEhP2w" frameborder="0" allowfullscreen></iframe></div>
				<div style="margin-left: 15px;">
					<p><b>New feature in <a style="color:#32cd32;" href="https://wpgoplugins.com/plugins/content-censor/" target="_blank">Content Censor 0.4</a> ignores banned keywords in HTML tags and attributes!</b></p>
					<p>Occasionally you might have a keyword that is used as the source URL for an image or as part of a HTML link. Normally this will be censored too, breaking the link. But in <a style="color:#32cd32;" href="https://wpgoplugins.com/plugins/content-censor/" target="_blank">Content Censor 0.4</a> you can now optionally choose to ignore HTML tags and all their attributes.</p>
				</div>
			</div>
		</div>

	</div>
	<?php
}

/**
 * Sanitize and validate input. Accepts an array, sanitizes the 'txtar_keywords' and 'txt_exclude' fields, and returns a sanitized array.
 *
 * @param array $input The input array to sanitize.
 *
 * @return array The sanitized input array.
 */
function pccf_validate_options( $input ) {
	// Strip html from textboxes.
	$input['txtar_keywords'] = wp_filter_nohtml_kses( $input['txtar_keywords'] );
	$input['txt_exclude']    = wp_filter_nohtml_kses( $input['txt_exclude'] );

	return $input;
}

/**
 * Filter content based on user settings.
 *
 * This function filters content based on the user's settings, which are stored in the 'pccf_options' option. It filters the post content, post title, comments, comment author, post tags, and tag cloud, depending on the user's settings.
 *
 * @return void
 */
function pccf_contfilt() {

	if ( is_admin() ) {
		return;
	} /* Only filter front end content. */

	global $pccf_defaults;
	$tmp = get_option( 'pccf_options', $pccf_defaults );

	if ( isset( $tmp['chk_post_content'] ) ) {
		if ( '1' == $tmp['chk_post_content'] ) {
			add_filter( 'the_content', 'pccf_filter' );
			add_filter( 'get_the_excerpt', 'pccf_filter' );
		}

		/* bbPress specific filtering (only if bbPress is present). */
		if ( class_exists( 'bbPress' ) ) {
			add_filter( 'bbp_get_topic_content', 'pccf_filter' );
			add_filter( 'bbp_get_reply_content', 'pccf_filter' );
		}
	}
	if ( isset( $tmp['chk_post_title'] ) ) {
		if ( '1' == $tmp['chk_post_title'] ) {
			add_filter( 'the_title', 'pccf_filter' );
		}
	}
	if ( isset( $tmp['chk_comments'] ) ) {
		if ( '1' == $tmp['chk_comments'] ) {
			add_filter( 'comment_text', 'pccf_filter' );
		}
	}
	if ( isset( $tmp['chk_comments'] ) ) {
		if ( '1' == $tmp['chk_comments'] ) {
			add_filter( 'get_comment_author', 'pccf_filter' );
		}
	}
	if ( isset( $tmp['chk_tags'] ) ) {
		if ( '1' == $tmp['chk_tags'] ) {
			add_filter( 'term_links-post_tag', 'pccf_filter' );
		}
	}
	if ( isset( $tmp['chk_cloud'] ) ) {
		if ( '1' == $tmp['chk_cloud'] ) {
			add_filter( 'wp_tag_cloud', 'pccf_filter' );
		}
	}
}

/**
 * Filters the content of a post or page to censor and replace keywords.
 *
 * @param string $text The content of the post or page to filter.
 *
 * @return string The filtered content.
 */
function pccf_filter( $text ) {

	global $post;

	// Get comma separated list of page ID's to exclude.
	global $pccf_defaults;
	$tmp = get_option( 'pccf_options', $pccf_defaults );

	$exclude_id_list  = $tmp['txt_exclude'];
	$exclude_id_array = explode( ', ', $exclude_id_list );

	// If current page ID is in exclude list then don't filter.
	if ( isset( $post ) && in_array( $post->ID, $exclude_id_array ) ) {
		return $text;
	}

	$wildcard_filter_type = $tmp['rdo_word'];
	$wildcard_char        = $tmp['drp_filter_char'];

	if ( 'star' == $wildcard_char ) {
		$wildcard = '*';
	} else {
		if ( 'dollar' == $wildcard_char ) {
			$wildcard = '$';
		} else {
			if ( 'question' == $wildcard_char ) {
				$wildcard = '?';
			} else {
				if ( 'exclamation' == $wildcard_char ) {
					$wildcard = '!';
				} else {
					if ( 'hyphen' == $wildcard_char ) {
						$wildcard = '-';
					} else {
						if ( 'hash' == $wildcard_char ) {
							$wildcard = '#';
						} else {
							if ( 'tilde' == $wildcard_char ) {
								$wildcard = '~';
							} else {
								$wildcard = '';
							}
						}
					}
				}
			}
		}
	}

	$filter_type      = $tmp['rdo_case'];
	$db_search_string = $tmp['txtar_keywords'];
	$keywords         = array_map( 'trim', explode( ',', $db_search_string ) ); // Explode and trim whitespace.
	$keywords         = array_unique( $keywords ); // Get rid of duplicates in the keywords textbox.
	$whole_word       = 'strict_off' == $tmp['rdo_strict_filtering'] ? false : true;

	foreach ( $keywords as $keyword ) {
		$keyword = trim( $keyword ); // Remove whitespace chars from start/end of string.
		if ( strlen( $keyword ) > 2 ) {
			$replacement = censor_word( $wildcard_filter_type, $keyword, $wildcard );
			if ( 'insen' == $filter_type ) {
				$text = str_replace_word_i( $keyword, $replacement, $text, $wildcard_filter_type, $keyword, $wildcard, $whole_word );
			} else {
				$text = str_replace_word( $keyword, $replacement, $text, $whole_word );
			}
		}
	}

	return $text;
}

/**
 * Censor a keyword based on the wildcard filter type.
 *
 * @param string $wildcard_filter_type The wildcard filter type ('first', 'all', or 'last').
 * @param string $keyword The keyword to censor.
 * @param string $wildcard The wildcard character to use for censoring.
 *
 * @return string The censored keyword.
 */
function censor_word( $wildcard_filter_type, $keyword, $wildcard ) {

	if ( 'first' == $wildcard_filter_type ) {
		$keyword = substr( $keyword, 0, 1 ) . str_repeat( $wildcard, strlen( substr( $keyword, 1 ) ) );
	} else {
		if ( 'all' == $wildcard_filter_type ) {
			$keyword = str_repeat( $wildcard, strlen( substr( $keyword, 0 ) ) );
		} else {
			$keyword = substr( $keyword, 0, 1 ) . str_repeat( $wildcard, strlen( substr( $keyword, 2 ) ) ) . substr( $keyword, - 1, 1 );
		}
	}

	return $keyword;
}

/**
 * Replace a word in a string with another word, case-insensitive.
 *
 * This function replaces a word in a string with another word, case-insensitive. It uses regular expressions to match the word, and can optionally replace the word with a wildcard string. It also calls the `censor_word()` function to replace the word with a censored version, depending on the user's settings.
 *
 * @param string $needle              The word to replace.
 * @param string $replacement         The replacement word.
 * @param string $haystack            The string to search and replace in.
 * @param string $wildcard_filter_type The wildcard filter type.
 * @param string $keyword             The keyword to filter.
 * @param string $wildcard            The wildcard string to replace the word with.
 * @param bool   $whole_word          Whether to match the whole word or not.
 *
 * @return string The modified string.
 */
function str_replace_word_i( $needle, $replacement, $haystack, $wildcard_filter_type, $keyword, $wildcard, $whole_word = true ) {

	$needle   = str_replace( '/', '\\/', preg_quote( $needle ) ); // Allow '/' in keywords.
	$pattern  = $whole_word ? "/\b$needle\b/i" : "/$needle/i";
	$haystack = preg_replace_callback(
		$pattern,
		function( $m ) use ( $wildcard_filter_type, $keyword, $wildcard ) {
			return censor_word( $wildcard_filter_type, $m[0], $wildcard );
		},
		$haystack
	);

	return $haystack;
}

/**
 * Replace a word in a string with another word.
 *
 * This function replaces all occurrences of a word in a string with another word. It uses regular expressions to match the word, and can optionally match the whole word or just a substring. It returns the modified string.
 *
 * @param string  $needle      The word to replace.
 * @param string  $replacement The replacement word.
 * @param string  $haystack    The string to search and replace in.
 * @param boolean $whole_word  Optional. Whether to match the whole word or just a substring. Default true.
 *
 * @return string The modified string.
 */
function str_replace_word( $needle, $replacement, $haystack, $whole_word = true ) {
	$needle   = str_replace( '/', '\\/', preg_quote( $needle ) ); // Allow '/' in keywords.
	$pattern  = $whole_word ? "/\b$needle\b/" : "/$needle/";
	$haystack = preg_replace( $pattern, $replacement, $haystack );

	return $haystack;
}

/**
 * Add action links to the plugin page.
 *
 * This function adds action links to the plugin page, including a link to the pro version of the plugin. The link is only added if the current plugin is the free version of the plugin.
 *
 * @param array  $links An array of action links.
 * @param string $file  The plugin file path.
 *
 * @return array An array of action links.
 */
function pccf_plugin_action_links( $links, $file ) {

	if ( plugin_basename( __FILE__ ) == $file ) {
		// Add a link to pro plugin.
		$links[] = '<a href="https://wpgoplugins.com/plugins/content-censor/" target="_blank" title="Try out the Pro version today for more great features!"><span style="font-size:14px;line-height:1.3" class="dashicons dashicons-star-filled"></span>Upgrade</a>';
	}

	return $links;
}

/**
 * Add a settings link to the plugin page.
 *
 * This function adds a settings link to the plugin page in the WordPress admin area. It checks if the current plugin file matches the plugin file for WP Content Filter, and if so, it adds a link to the plugin settings page.
 *
 * @param array  $links An array of plugin action links.
 * @param string $file  The plugin file path.
 *
 * @return array The modified array of plugin action links.
 */
function pccf_plugin_settings_link( $links, $file ) {

	if ( plugin_basename( __FILE__ ) == $file ) {
		$pccf_links = '<a href="' . get_admin_url() . 'options-general.php?page=wp-content-filter-options">' . __( 'Settings' ) . '</a>';
		array_unshift( $links, $pccf_links );
	}

	return $links;
}

/**
 * Register admin scripts and styles to be enqueued on the theme options page.
 *
 * This function registers the 'pccf-admin-css' style sheet to be enqueued on the theme options page in the WordPress admin area.
 *
 * @return void
 */
function pccf_register_admin_scripts() {

	// Register theme options page style sheets.
	wp_register_style( 'pccf-admin-css', plugins_url( '/wp-content-filter/css/pccf-admin.css' ), array(), '3.1.0' );
}
