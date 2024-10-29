<?php
/*
Plugin Name: April's Facebook Like Button
Plugin URI: http://springthistle.com/wordpress/plugin_facebooklike
Description: Adds a button (via iframe) which easily lets your visitors Like your posts. <a href="themes.php?page=ahs_facebooklike.php">Edit settings</a>.
Version: 1.4
Author: April Hodge Silver
Author URI: http://springthistle.com
*/

function ahsfl_options() {
	add_submenu_page('themes.php', 'Facebook Like', 'Facebook Like', 8, basename(__FILE__), 'ahsfl_options_page');
}

/**
* Build up all the params for the button
*/
function ahsfl_build_options() {
	// get the post varibale (should be in the loop)
	global $post;
	// get the permalink
    if (get_post_status($post->ID) == 'publish') {
        $url = get_permalink();
    }
    $button = '?href=' . urlencode($url);

	// which style
    if (get_option('ahsfl_version') == 'button_count') {
        $button .= '&amp;layout=button_count';
    } else {
		$button .= '&amp;layout=standard';
	}

	// show faces?
    if (get_option('ahsfl_faces') == 'false') {
        $button .= '&amp;show_faces=false';
    } else {
		$button .= '&amp;show_faces=true';
	}

	// which verb
    if (get_option('ahsfl_verb') == 'recommend') {
        $button .= '&amp;action=recommend';
    } else {
		$button .= '&amp;action=like';
	}

	// which colors
    if (get_option('ahsfl_colorscheme') == 'dark') {
        $button .= '&amp;colorscheme=dark';
    } else if (get_option('ahsfl_colorscheme') == 'evil'){
		$button .= '&amp;colorscheme=evil';
    } else {
		$button .= '&amp;colorscheme=light';
	}

	// which size
    $button .= '&amp;width='.get_option('ahsfl_width');

	// return all the params
	return $button;
}

/**
* Generate the iFrame render of the button
*/
function ahsfl_generate_button() {
	
	// build up the outer style
    $button = '<div class="facebook_like_button" style="' . get_option('ahsfl_style') . '">';

    $button .= '<iframe src="http://www.facebook.com/plugins/like.php' . ahsfl_build_options() . '" ';

	// close off the iframe
	$button .= 'scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:'.get_option('ahsfl_width').'px; height:'.get_option('ahsfl_height').'px"></iframe></div>';
	
	// return the iframe code
    return $button;
}

/**
* Gets run when the content is loaded in the loop
*/
function ahsfl_update($content) {

    global $post;

    // add the manual option, code added by kovshenin
    if (get_option('ahsfl_where') == 'manual') {
        return $content;
	}
    // is it a page
    if (get_option('ahsfl_display_page') == null && is_page()) {
        return $content;
    }
	// are we on the front page
    if (get_option('ahsfl_display_front') == null && is_home()) {
        return $content;
    }
	// are we in a feed
    if (is_feed()) {
        return $content;
	}
	// are we in a feed - for future investigation
    if (is_feed()) {
		$button = ahsfl_generate_static_button();
		$where = 'ahsfl_rss_where';
    } else {
		$button = ahsfl_generate_button();
		$where = 'ahsfl_where';
	}
	// are we displaying in a feed
	if (is_feed() && get_option('ahsfl_display_rss') == null) {
		return $content;
	}

	// are we just using the shortcode
	if (get_option($where) == 'shortcode') {
		return str_replace('[facebook-like-button]', $button, $content);
	} else {
		// if we have switched the button off
		if (get_post_meta($post->ID, 'facebooklikebutton') == null) {
			if (get_option($where) == 'beforeandafter') {
				// adding it before and after
				return $button . $content . $button;
			} else if (get_option($where) == 'before') {
				// just before
				return $button . $content;
			} else {
				// just after
				return $content . $button;
			}
		} else {
			// not at all
			return $content;
		}
	}
}

// Manual output
function facebooklikebutton() {
    if (get_option('ahsfl_where') == 'manual') {
        return ahsfl_generate_button();
    } else {
        return false;
    }
}

// Remove the filter excerpts
function ahsfl_remove_filter($content) {
	if (!is_feed()) {
    	remove_action('the_content', 'ahsfl_update');
	}
    return $content;
}

function ahsfl_get_image($postid,$size='thumbnail') {

	if (function_exists('has_post_thumbnail')) {
		if (has_post_thumbnail($postid)) {
			$image_id = get_post_thumbnail_id();
			$image_url = wp_get_attachment_image_src($image_id,$size);
			$image_url = $image_url[0];
			return $image_url;
		}
	}

    $arrImages =& get_children('post_type=attachment&post_mime_type=image&post_parent='.$postid );
    if ($arrImages) {
        // Get array keys representing attached image numbers
        $arrKeys = array_keys($arrImages);
        // Get the first image attachment
        $iNum = $arrKeys[0];
        // return the url for the attachment
        $imginfo = wp_get_attachment_image_src($iNum,$size);
        return $imginfo[0];
	} else {
		return '';
	}
}

function ahsfl_head() {
	global $post;
	
	if (get_option('ahsfl_defaultimg') == null) 
		$defaultimg = get_bloginfo('stylesheet_directory').'/images/default_icon.jpg'; 
	else $defaultimg = get_option('ahsfl_defaultimg');
	
	$postimg = ahsfl_get_image($post->ID);

	if (empty($postimg)) $postimg = $defaultimg;
	?>

	<meta property="og:title" content="<?php if (is_single() || is_page()) echo $post->post_title; else echo get_bloginfo('name') ?>"/>
	<meta property="og:site_name" content="<?php echo get_bloginfo('name') ?>"/>
	<meta property="og:image" content="<?php if (is_single()) echo $postimg; else echo $defaultimg ?>"/>

	<?php
}

function ahsfl_options_page() {
?>
    <div class="wrap">
    <div class="icon32" id="icon-options-general"><br/></div><h2>Settings for Facebook 'Like' button</h2>
    <form method="post" action="options.php">
    <?php
        // New way of setting the fields, for WP 2.7 and newer
        if(function_exists('settings_fields')){
            settings_fields('ahsfl-options');
        } else {
            wp_nonce_field('update-options');
            ?>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="ahsfl_where,ahsfl_style,ahsfl_version,ahsfl_colorscheme,ahsfl_display_page,ahsfl_display_front,ahsfl_display_rss,ahsfl_display_feed,ahsfl_verb,ahsfl_defaultimg,ahsfl_faces" />
            <?php
        }
    ?>
		<style type="text/css">.form-table { width: 700px } .form-table th { width: 100px }</style>
        <table class="form-table">
            <tr>
	            <tr>
	                <th scope="row" valign="top">
	                    Display
	                </th>
	                <td>
	                    <input type="checkbox" value="1" <?php if (get_option('ahsfl_display_page') == '1') echo 'checked="checked"'; ?> name="ahsfl_display_page" id="ahsfl_display_page" group="ahsfl_display"/>
	                    <label for="ahsfl_display_page">Display the button on pages</label>
	                    <br/>
	                    <input type="checkbox" value="1" <?php if (get_option('ahsfl_display_front') == '1') echo 'checked="checked"'; ?> name="ahsfl_display_front" id="ahsfl_display_front" group="ahsfl_display"/>
	                    <label for="ahsfl_display_front">Display the button on the front page (home)</label>
	                    <!--<br/>
	                    <input type="checkbox" value="1" <?php if (get_option('ahsfl_display_rss') == '1') echo 'checked="checked"'; ?> name="ahsfl_display_rss" id="ahsfl_display_rss" group="ahsfl_display"/>
	                    <label for="ahsfl_display_rss">Display the image button in your feed</label>-->
	                </td>
	            </tr>
                <th scope="row" valign="top">
                    Position
                </th>
                <td>
                	<select name="ahsfl_where">
                		<option <?php if (get_option('ahsfl_where') == 'before') echo 'selected="selected"'; ?> value="before">Before</option>
                		<option <?php if (get_option('ahsfl_where') == 'after') echo 'selected="selected"'; ?> value="after">After</option>
                		<option <?php if (get_option('ahsfl_where') == 'beforeandafter') echo 'selected="selected"'; ?> value="beforeandafter">Before and After</option>
                		<option <?php if (get_option('ahsfl_where') == 'shortcode') echo 'selected="selected"'; ?> value="shortcode">Shortcode [facebook-like-button]</option>
                		<option <?php if (get_option('ahsfl_where') == 'manual') echo 'selected="selected"'; ?> value="manual">Manual</option>
                	</select>
					<br /><span class="description">If you choose "manual," just put <code>&lt;?php echo ahsfl_generate_button(); ?></code><br />in your theme file where you want your button to show up.</span>
					</td>
            </tr>
<!--            <tr>
                <th scope="row" valign="top">
                    RSS Position
                </th>
                <td>
                	<select name="ahsfl_rss_where">
                		<option <?php if (get_option('ahsfl_rss_where') == 'before') echo 'selected="selected"'; ?> value="before">Before</option>
                		<option <?php if (get_option('ahsfl_rss_where') == 'after') echo 'selected="selected"'; ?> value="after">After</option>
                		<option <?php if (get_option('ahsfl_rss_where') == 'beforeandafter') echo 'selected="selected"'; ?> value="beforeandafter">Before and After</option>
                		<option <?php if (get_option('ahsfl_where') == 'shortcode') echo 'selected="selected"'; ?> value="shortcode">Shortcode [facebook-like-button]</option>
                	</select>
                </td>
            </tr>-->
            <tr>
                <th scope="row" valign="top">
                    Type
                </th>
                <td>
                    <input type="radio" value="standard" <?php if (get_option('ahsfl_version') == 'standard') echo 'checked="checked"'; ?> name="ahsfl_version" id="ahsfl_version_standard" group="ahsfl_version"/>
                    <label for="ahsfl_version_standard">Standard (button with full message)</label>
                    <br/>
                    <input type="radio" value="button_count" <?php if (get_option('ahsfl_version') == 'button_count') echo 'checked="checked"'; ?> name="ahsfl_version" id="ahsfl_version_button_count" group="ahsfl_version" />
                    <label for="ahsfl_version_button_count">Small (button and count)</label>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    Verb to use
                </th>
                <td>
                    <input type="radio" value="like" <?php if (get_option('ahsfl_verb') == 'like') echo 'checked="checked"'; ?> name="ahsfl_verb" id="ahsfl_verb_like" group="ahsfl_verb"/>
                    <label for="ahsfl_verb_like">Like</label>
                    <br/>
                    <input type="radio" value="recommend" <?php if (get_option('ahsfl_verb') == 'recommend') echo 'checked="checked"'; ?> name="ahsfl_verb" id="ahsfl_verb_recommend" group="ahsfl_verb" />
                    <label for="ahsfl_verb_recommend">Recommend</label>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    Show faces below button?
                </th>
                <td>
                    <input type="radio" value="true" <?php if (get_option('ahsfl_faces') == 'true') echo 'checked="checked"'; ?> name="ahsfl_faces" id="ahsfl_faces_true" group="ahsfl_faces"/>
                    <label for="ahsfl_faces_true">Yes, show faces</label>
                    <br/>
                    <input type="radio" value="false" <?php if (get_option('ahsfl_faces') == 'false') echo 'checked="checked"'; ?> name="ahsfl_faces" id="ahsfl_faces_true" group="ahsfl_faces" />
                    <label for="ahsfl_faces_false">No, do not show faces</label>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    Color Scheme
                </th>
                <td>
                    <input type="radio" value="light" <?php if (get_option('ahsfl_colorscheme') == 'light') echo 'checked="checked"'; ?> name="ahsfl_colorscheme" id="ahsfl_colorscheme_light" group="ahsfl_colorscheme"/>
                    <label for="ahsfl_colorscheme_light">Light</label>
                    <br/>
                    <input type="radio" value="dark" <?php if (get_option('ahsfl_colorscheme') == 'dark') echo 'checked="checked"'; ?> name="ahsfl_colorscheme" id="ahsfl_colorscheme_dark" group="ahsfl_colorscheme" />
                    <label for="ahsfl_colorscheme_dark">Dark</label>
                    <br/>
                    <input type="radio" value="evil" <?php if (get_option('ahsfl_colorscheme') == 'evil') echo 'checked="checked"'; ?> name="ahsfl_colorscheme" id="ahsfl_colorscheme_evil" group="ahsfl_colorscheme" />
                    <label for="ahsfl_colorscheme_evil">Evil</label>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="ahsfl_width">Width &amp; Height</label></th>
                <td>
                    <input type="text" value="<?php echo htmlspecialchars(get_option('ahsfl_width')); ?>" name="ahsfl_width" id="ahsfl_width" size="5" />px wide &nbsp; by &nbsp; <input type="text" value="<?php echo htmlspecialchars(get_option('ahsfl_height')); ?>" name="ahsfl_height" id="ahsfl_height" size="5" />px high
                    <span class="description"><br />What width do you want it to be? Generally, you'll want 200-450 for the standard widget and 100 for the small version. The default height is 40px; if you're using the small version of the button 20px is tall enough; if you're showing faces, you'll want more height.</code></span>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="ahsfl_style">Styling</label></th>
                <td>
                    <input type="text" value="<?php echo htmlspecialchars(get_option('ahsfl_style')); ?>" name="ahsfl_style" id="ahsfl_style" size="60" />
                    <span class="description"><br />Add style to the div that surrounds the button E.g. <code>float: left; margin-right: 10px;</code>.<br />Or you can add <code>.facebook_like_button</code> to your stylesheet.</span>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="ahsfl_defaultimg">Default Image</label></th>
                <td>
                    <input type="text" value="<?php echo htmlspecialchars(get_option('ahsfl_defaultimg')); ?>" name="ahsfl_defaultimg" id="ahsfl_defaultimg" size="60" />
                    <span class="description"><br />What is the default image for your website, in case the single post doesn't have an image, or in case the user wants to 'like' a whole page. Use a full URL, starting with <code>http://</code></span>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
    </div>
<?php
}



// On access of the admin page, register these variables (required for WP 2.7 & newer)
function ahsfl_init(){
    if(function_exists('register_setting')){
        register_setting('ahsfl-options', 'ahsfl_display_page');
        register_setting('ahsfl-options', 'ahsfl_display_front');
        register_setting('ahsfl-options', 'ahsfl_display_rss');
        register_setting('ahsfl-options', 'ahsfl_style');
        register_setting('ahsfl-options', 'ahsfl_version');
        register_setting('ahsfl-options', 'ahsfl_verb');
        register_setting('ahsfl-options', 'ahsfl_faces');
        register_setting('ahsfl-options', 'ahsfl_colorscheme');
        register_setting('ahsfl-options', 'ahsfl_width');
        register_setting('ahsfl-options', 'ahsfl_height');
        register_setting('ahsfl-options', 'ahsfl_where');
        register_setting('ahsfl-options', 'ahsfl_rss_where');
        register_setting('ahsfl-options', 'ahsfl_defaultimg');
    }
}

// Only all the admin options if the user is an admin
if(is_admin()){
    add_action('admin_menu', 'ahsfl_options');
    add_action('admin_init', 'ahsfl_init');
}

// Set the default options when the plugin is activated
function ahsfl_activate(){
    add_option('ahsfl_where', 'before');
    add_option('ahsfl_rss_where', 'before');
    add_option('ahsfl_style', 'float: right; margin-left: 10px;');
    add_option('ahsfl_version', 'standard');
    add_option('ahsfl_colorscheme', 'light');
    add_option('ahsfl_width', '450');
    add_option('ahsfl_height', '40');
    add_option('ahsfl_verb', 'like');
    add_option('ahsfl_faces', 'false');
    add_option('ahsfl_display_page', '1');
    add_option('ahsfl_display_front', '1');
    add_option('ahsfl_display_rss', '0');
}

add_filter('the_content', 'ahsfl_update', 8);
add_filter('get_the_excerpt', 'ahsfl_remove_filter', 9);

add_action('wp_head', 'ahsfl_head');

register_activation_hook( __FILE__, 'ahsfl_activate');

