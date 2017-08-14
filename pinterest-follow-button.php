<?php
/*
  Plugin Name: Pinterest "Follow" Button
  Plugin URI: http://pinterestplugin.com
  Description: Add a Pinterest "Follow" button to your sidebar with this widget. Also includes a shortcode.
  Author: Phil Derksen
  Author URI: http://pinterestplugin.com
  Version: 1.1.2
  License: GPLv2
  Copyright 2012 Phil Derksen (phil@pinterestplugin.com)
*/

/***************************
* Global Constants
***************************/

define( 'PFB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

//Plugin install/activation

function pfb_install() {
	//Deactivate plugin if WP version too low
    if ( version_compare( get_bloginfo( 'version' ), '3.0', '<' ) ) {
        deactivate_plugins( basename( __FILE__ ) );
    }
}

register_activation_hook( __FILE__, 'pfb_install' );

//Add settings page to admin menu
//Use $page variable to load CSS/JS ONLY for this plugin's admin page

function pfb_create_menu() {
	$page = add_submenu_page( 'options-general.php', 'Pinterest "Follow" Button Settings', 'Pinterest "Follow" Button', 
		'manage_options', __FILE__, 'pfb_create_settings_page' );
	add_action( 'admin_print_styles-' . $page, 'pfb_add_admin_css_js' );
}

add_action( 'admin_menu', 'pfb_create_menu' );

//Add Admin CSS/JS

function pfb_add_admin_css_js() {	
	wp_enqueue_script( 'jquery' );

	wp_enqueue_style( 'pinterest-follow-button', plugins_url( '/css/pinterest-follow-button-admin.css' , __FILE__ ) );
	wp_enqueue_script( 'pinterest-follow-button', plugins_url( '/js/pinterest-follow-button-admin.js', __FILE__ ), array( 'jquery' ) );
}

//Create settings page (just instructions)

function pfb_create_settings_page() {
	?>
	
	<div class="wrap">
		<a href="http://pinterestplugin.com/" target="_blank"><div id="pinterest-button-icon-32" class="icon32"
			style="background: url(<?php echo plugins_url( '/img/pinterest-button-icon-med.png', __FILE__ ); ?>) no-repeat;"></div></a>
		<h2><?php _e( 'Pinterest "Follow" Button Settings', 'pfb' ); ?></h2>
		
		<div class="error">
			<p>
				<strong>Feb 1, 2014 Update Notice:</strong> This plugin will not recieve any further updates. It is recommended to delete it and use the new Pinterest Widgets plugin instead.
			</p>
			<p>
				<strong><?php printf( '<a href="%s">Upgrade to Pinterest Widgets now</a>',
					add_query_arg( array(
						'tab'  => 'search',
						'type' => 'term',
						's'    => urlencode('pinterest widgets')
					), admin_url( 'plugin-install.php' ) )
				); ?></strong>
				(look for author name "Phil Derksen")
			</p>
		</div>
		
		<div id="poststuff" class="metabox-holder">
		
			<div id="post-body">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						<div class="postbox">
							<div class="handlediv pfb-handlediv" title="Click to toggle"><br /></div>
							<h3 class="hndle pfb-hndle">Shortcode Instructions</h3>
							
							<div class="inside">
								<p>
									<em>If you just want to add the button to your sidebar, go to Appearance &rarr;
										<a href="<?php echo admin_url( 'widgets.php' ); ?>">Widgets</a></em>
								</p>
								<p>
									Use the shortcode <code>[pinterest-follow]</code> to display the button within your content.
								</p>
								<p>
									Use the function <code><?php echo htmlentities('<?php echo do_shortcode(\'[pinterest-follow]\'); ?>'); ?></code>
									to display within template or theme files.
								</p>
								<p><strong>Shortcode parameters</strong></p>
								<p>
									- username: Pinterest username<br/>
									- button_type: 1-8 (default: 1) -- the official button images from Pinterest<br/>
									- image_url: URL of a custom image button (leave out button_type attribute)<br/>
									- new_window: false (default), true -- if true opens Pinterest profile in a new window<br/>
									- float: none (default), left, right<br/>
									- remove_div: false (default), true -- if true removes surrounding div tag, which also removes float setting
								</p>
								<p><strong>Examples</strong></p>
								<p>
									<code>[pinterest-follow username="philderksen" button_type="3"]</code><br/>
									<code>[pinterest-follow username="philderksen" image_url="http://www.mysite.com/myimage.jpg" 
										new_window="true" float="right"]</code><br/>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php
}

//Add Public CSS/JS

function pfb_add_public_css_js() {
	wp_enqueue_style( 'pinterest-follow-button', plugins_url( '/css/pinterest-follow-button.css' , __FILE__ ) );
}

add_action( 'wp_enqueue_scripts', 'pfb_add_public_css_js' );

//Render "Follow" button image tag

function pfb_btn_img_tag( $img_url ) {
    return '<img src="' . $img_url . '" alt="Follow Me on Pinterest" />';
}

//Get Pinterest's official "Follow" button image tag based on number button type (1-8)
//Doesn't work with https without browser warning
//Removed specified width and height from official embed html

function pfb_official_btn_img_tag( $btn_type ) {
    $img_filename;
    
	//Check for valid button type (1-8)
	switch ( $btn_type ) {
		case 1:
			$img_filename = 'follow-on-pinterest-button.png';
			break;
		case 2:
			$img_filename = 'pinterest-button.png';
			break;
		case 3:
			$img_filename = 'big-p-button.png';
			break;
		case 4:
			$img_filename = 'small-p-button.png';
			break;
		case 5:
			$img_filename = 'about/buttons/follow-me-on-pinterest-button.png';
			break;
		case 6:
			$img_filename = 'about/buttons/pinterest-button.png';
			break;
		case 7:
			$img_filename = 'about/buttons/big-p-button.png';
			break;
		case 8:
			$img_filename = 'about/buttons/small-p-button.png';
			break;
	}

    return pfb_btn_img_tag ( 'http://passets-cdn.pinterest.com/images/' . $img_filename );
}

//Render "Follow" Button base html (button and link) for public site

function pfb_btn_public( $pfb_username, $btn_type, $img_url, $new_window, $float ) {
    $img_tag;
    
    if ( $btn_type >= 1 && $btn_type <= 8 ) {
        //Official button image
        $img_tag = pfb_official_btn_img_tag( $btn_type );
    }
    elseif ( $img_url != '' ) {
        //Custom button image
        $img_tag = pfb_btn_img_tag( $img_url );
    }
    else {
        //Default button image if no valid $btn_type and no $img_url
        $img_tag = pfb_official_btn_img_tag( 1 );
    }
    
	$btn_and_link = '<a href="http://pinterest.com/' . urlencode( $pfb_username ) . '/" title="Follow Me on Pinterest" ' .
		( $new_window ? 'target="_blank"' : '' ) . '>' . $img_tag . '</a>';

	return $btn_and_link;
}

//Add Pinterest Follow Button Widget

class Pfb_Follow_Button_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'pfb-clearfix', 'description' => __( 'Add a Pinterest "Follow" button to your sidebar with this widget.') );
		$control_ops = array('width' => 400);  //doesn't use height
		parent::__construct('pfb_follow_button', __('Pinterest "Follow" Button'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$pfb_username = $instance['pfb_username'];
		$pfb_img_option = $instance['pfb_follow_button_radio'];
		$pfb_url_of_img = $instance['pfb_url_of_img'];
		$float = empty( $instance['float_follow'] ) ? 'none' : $instance['float_follow'];
		$new_window = (bool)$instance['new_window'];
		$pfb_remove_div = (bool)$instance['remove_div'];
		
		$baseBtn = pfb_btn_public( $pfb_username, $pfb_img_option, $pfb_url_of_img, $new_window, $float_follow );
		
		echo $before_widget;
        
		if ( $title )
			echo $before_title . $title . $after_title;
            
		if ( $pfb_remove_div ) {
			echo $baseBtn;
		}
		else {
			//Surround with div tag
			$float_class = '';
			
			if ( $float == 'left' ) {
				$float_class = 'pfb-float-left';
			}
			elseif ( $float == 'right' ) {
				$float_class = 'pfb-float-right';
			}
		
			echo '<div class="pinterest-follow-btn-wrapper-widget ' . $float_class . '">' . $baseBtn . '</div>';
		}
	
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'pfb_username' => '', 'pfb_follow_button_radio' => '1', 
			'float_follow' => 'none') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['pfb_username'] = strip_tags($new_instance['pfb_username']);
		$instance['pfb_follow_button_radio'] = strip_tags($new_instance['pfb_follow_button_radio']);
		$instance['pfb_url_of_img'] = strip_tags($new_instance['pfb_url_of_img']);
		$instance['float_follow'] = $new_instance['float_follow'];
		$instance['new_window'] = ( $new_instance['new_window'] ? 1 : 0 );
		$instance['remove_div'] = ( $new_instance['remove_div'] ? 1 : 0 );
		
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'pfb_username' => '', 'pfb_follow_button_radio' => '1', 
			'float_follow' => 'none') );
		$title = strip_tags($instance['title']); 
		$pfb_username = strip_tags($instance['pfb_username']);
		$pfb_follow_button_radio = $instance['pfb_follow_button_radio'];
		$pfb_url_of_img = strip_tags($instance['pfb_url_of_img']);
        ?>
        
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title (optional):'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" 
				type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('pfb_username'); ?>"><?php _e('Pinterest Username (required):'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('pfb_username'); ?>" name="<?php echo $this->get_field_name('pfb_username'); ?>" 
				type="text" value="<?php echo esc_attr($pfb_username); ?>" />
		</p>
		
        <p><label>Button image:</label></p>
        
		<table>
			<tr>
				<td><input type="radio" <?php checked( $pfb_follow_button_radio, 1 ); ?> name="<?php echo $this->get_field_name('pfb_follow_button_radio'); ?>" value="1" /></td>
				<td style="width: 175px;"><?php echo pfb_official_btn_img_tag( 1 ); ?></td>
				<td><input type="radio" <?php checked( $pfb_follow_button_radio, 5 ); ?> name="<?php echo $this->get_field_name('pfb_follow_button_radio'); ?>" value="5"/></td>
                <td><?php echo pfb_official_btn_img_tag( 5 ); ?></td>
			</tr>
			<tr>
				<td><input type="radio" <?php checked( $pfb_follow_button_radio, 2 ); ?> name="<?php echo $this->get_field_name('pfb_follow_button_radio'); ?>" value="2" /></td>
				<td><?php echo pfb_official_btn_img_tag( 2 ); ?></td>
				<td><input type="radio" <?php checked( $pfb_follow_button_radio, 6 ); ?> name="<?php echo $this->get_field_name('pfb_follow_button_radio'); ?>" value="6"/></td>
                <td><?php echo pfb_official_btn_img_tag( 6 ); ?></td>
			</tr>
			<tr>
				<td><input type="radio" <?php checked( $pfb_follow_button_radio, 3 ); ?> name="<?php echo $this->get_field_name('pfb_follow_button_radio'); ?>" value="3"/></td>
                <td><?php echo pfb_official_btn_img_tag( 3 ); ?></td>
				<td><input type="radio" <?php checked( $pfb_follow_button_radio, 7 ); ?> name="<?php echo $this->get_field_name('pfb_follow_button_radio'); ?>" value="7"/></td>
                <td><?php echo pfb_official_btn_img_tag( 7 ); ?></td>
			</tr>
			<tr>
				<td><input type="radio" <?php checked( $pfb_follow_button_radio, 4 ); ?> name="<?php echo $this->get_field_name('pfb_follow_button_radio'); ?>" value="4"/></td>
                <td><?php echo pfb_official_btn_img_tag( 4 ); ?></td>
				<td><input type="radio" <?php checked( $pfb_follow_button_radio, 8 ); ?> name="<?php echo $this->get_field_name('pfb_follow_button_radio'); ?>" value="8"/></td>
                <td><?php echo pfb_official_btn_img_tag( 8 ); ?></td>
			</tr>
			<tr>
				<td><input type="radio" <?php checked( $pfb_follow_button_radio, 0 ); ?> name="<?php echo $this->get_field_name('pfb_follow_button_radio'); ?>" value="0"/></td>
				<td colspan="3">Specify your own image URL:</td>
			</tr>
		</table>

		<p>
			<input class="widefat" id="<?php echo $this->get_field_id('pfb_url_of_img'); ?>" name="<?php echo $this->get_field_name('pfb_url_of_img'); ?>" 
				type="text" value="<?php echo esc_attr($pfb_url_of_img); ?>" />
		</p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('new_window'); ?>" name="<?php echo $this->get_field_name('new_window'); ?>" 
                <?php checked( (bool)$instance['new_window'] ) ?> value="1" />
            <label for="<?php echo $this->get_field_id('new_window'); ?>"><?php _e( 'Open in a new window' ); ?></label>
        </p>
		<p>
            <label for="<?php echo $this->get_field_id('float_follow'); ?>"><?php _e('Align (float):'); ?></label> 
            <select name="<?php echo $this->get_field_name('float_follow'); ?>" id="<?php echo $this->get_field_id('float_follow'); ?>">
                <option value="none" <?php selected( ( $instance['float_follow'] == 'none' ) || ( empty( $instance['float_follow'] ) ) ); ?> >
					<?php _e('none (default)'); ?></option>
                <option value="left" <?php selected( $instance['float_follow'], 'left' ); ?> ><?php _e('left'); ?></option>
                <option value="right" <?php selected( $instance['float_follow'], 'right' ); ?> ><?php _e('right'); ?></option>
            </select>
		</p>
		<p>
			<input class="checkbox" id="<?php echo $this->get_field_id('remove_div'); ?>" name="<?php echo $this->get_field_name('remove_div'); ?>" type="checkbox"
                <?php checked( (bool)$instance['remove_div'] ) ?> value="1" />
			<label for="<?php echo $this->get_field_id('remove_div'); ?>">Remove div tag surrounding this widget button (also removes <strong>float</strong> setting)</label>
		</p>
        <p>
            <a href="<?php echo $url = admin_url( 'admin.php?page=' . PFB_PLUGIN_BASENAME ); ?>"><?php _e( 'Shortcode Instructions', 'pfb' ); ?></a>
        </p>
        
        <?php
	}
}

//Add function to the widgets_init hook

add_action( 'widgets_init', 'pfb_load_follow_button_widget' );

// Function that registers Follow Button widget

function pfb_load_follow_button_widget() {
	register_widget( 'Pfb_Follow_Button_Widget' );
}

//Register shortcode: [pinterest-follow username="" button_type="" image_url="" new_window="false" float="none" remove_div="false"]

function pfb_button_shortcode_html($attr) {
	$attr['username'] = ( empty( $attr['username'] ) ? '' : $attr['username'] );
	$attr['button_type'] = ( empty( $attr['button_type'] ) ? '' : $attr['button_type'] );
	$attr['image_url'] = ( empty( $attr['image_url'] ) ? '' : $attr['image_url'] );
	$float = ( empty( $attr['float'] ) ? 'none' : $attr['float'] );
	$new_window_bool = ( $attr['new_window'] == 'true' );
	$remove_div_bool = ( $attr['remove_div'] == 'true' );

	$baseBtn = pfb_btn_public( $attr['username'], $attr['button_type'], $attr['image_url'], $new_window_bool, $attr['float'] );
	
	if ( $remove_div_bool ) {
		return $baseBtn;
	}	
	else {
		//Surround with div tag
		$float_class = '';
		
		if ( $float == 'left' ) {
			$float_class = 'pfb-float-left';
		}
		elseif ( $float == 'right' ) {
			$float_class = 'pfb-float-right';
		}
	
		return '<div class="pinterest-follow-btn-wrapper-shortcode ' . $float_class . '">' . $baseBtn . '</div>';
	}
}

add_shortcode( 'pinterest-follow', 'pfb_button_shortcode_html' );
