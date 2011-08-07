<?php
/*
Plugin Name: Upload Media by Zip
Plugin URI: http://trepmal.com/plugins/upload-media-by-zip/
Description: Upload a zip file of images and (optionally) attach to a page/post
Author: Kailey Lampert
Version: 0.9
Author URI: http://kaileylampert.com/

Copyright (C) 2011  Kailey Lampert

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
 * Upload_Media_By_Zip class
 *
 * class used as a namespace
 *
 * @package Upload Media By Zip
 */
class Upload_Media_By_Zip {
	/**
	 * Get hooked into init
	 *
	 * @return void
	 */
	function upload_media_by_zip( ) {
		load_plugin_textdomain( 'upload-media-by-zip', false, dirname( plugin_basename( __FILE__ ) ) .  '/lang' );
		add_action( 'admin_menu', array( &$this, 'menu' ) );
		add_action( 'admin_init', array( &$this, 'get_title' ) );
		add_filter( 'media_upload_tabs', array( &$this, 'create_new_tab') );
		add_action( 'media_buttons', array( &$this, 'context'), 11 );
		add_filter( 'media_upload_uploadzip', array( &$this, 'media_upload_uploadzip') );
	}
	/**
	 * Create admin pages in menu
	 *
	 * @return void
	 */
	function menu() {
		$page = add_media_page( __( 'Upload Zip Archive', 'upload-media-by-zip' ), __( 'Upload Zip Archive', 'upload-media-by-zip' ), 'upload_files', __FILE__, array( &$this, 'page' ) );
		add_action( 'admin_print_scripts-' . $page, array( &$this, 'scripts' ) );
	}
	/**
	 * Load needed scripts
	 *
	 * @return void
	 */
	function scripts() {
		wp_enqueue_script('jquery');
	}
	/**
	 * The Admin Page
	 *
	 */
	function page() {
		echo '<div class="wrap">';
		echo '<h2>' . __( 'Upload Zip Archive', 'upload-media-by-zip' ) . '</h2>';
		echo self::handler();
		self::form();
		echo '</div>';
	}
	/**
	 * Get page title based on ID in $_GET
	 *
	 * Used so JavaScript can make an ajax request for the page title
	 * die()s on page title or nothing
	 *
	 */
	function get_title() {
		if ( isset( $_GET['get_page_by_title'] ) ) {
			$p = get_post( $_GET['get_page_by_title'] );
			if ( is_object( $p ) )
			die( $p->post_type .': '. $p->post_title );
		}
	}
	/**
	 * Add the new tab to the media pop-ip
	 *
	 * @param array $tabs Existing media tabs
	 * @return array $tabs Modified media tabs
	 */
	function create_new_tab( $tabs ) {
		$tabs['uploadzip'] = __( 'Upload Zip Archive', 'upload-media-by-zip' );
	    return $tabs;
	}
	/**
	 * Prepare the tab in the media pop-up
	 *
	 * @return string iframe
	 */
	function media_upload_uploadzip() {
	    $errors = false;
		if ( isset( $_POST['send'] ) ) {

			// Build output
			$html = '';
			$size = $_POST['size'];
			if ( ! empty( $_POST['altsize'] ) )
				$size = explode( ',', $_POST['altsize'] );

			foreach( $_POST['srcs'] as $k => $id ) {
				$html .= wp_get_attachment_image( $id, $size );
				$html .= ' ';
			}
			// Return it to TinyMCE
			return media_send_to_editor( $html );
		}
		return wp_iframe( array( &$this, 'media_uploadzip_tab_content' ), 'media', $errors );
	}
	/**
	 * Media tab content
	 *
	 */
	function media_uploadzip_tab_content( $errors ) {
		global $type;
		$message = self::handler();

		media_upload_header();
		$post_id = isset( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;

		$form_action_url = admin_url("media-upload.php?type=$type&tab=uploadzip&post_id=$post_id");
		$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type );

		if (!empty( $message) ) {
			echo '<form action="" method="post">';
			echo $message;
			echo '<select name="size">';
			$sizes = get_intermediate_image_sizes();
			foreach ( $sizes as $sz ) {
				echo '<option>'. $sz .'</option>';
			}
			echo '</select>';
			echo __( 'Alternate size:', 'upload-media-by-zip' ) .' <input type="text" name="altsize" value"" /> ('. __( 'Example:', 'upload-media-by-zip' ) .' 400,300)';
			echo '<input type="submit" class="button-primary" value="'. __( 'Insert attachments into post', 'upload-media-by-zip' ) .'" name="send" />';
			echo '</form>';
		}

		self::form( array('action' => $form_action_url, 'post_id' => $post_id ) );
	}
	/**
	 * Add new button to Upload/Insert icons
	 *
	 */
	function context() {
		global $post_ID;
		$button  = '<a class="thickbox" href="'. admin_url("media-upload.php?post_id={$post_ID}&tab=uploadzip&TB_iframe=1").'" title="'. __( 'Upload and Extract a Zip Archive', 'upload-media-by-zip' ) .'">';
		$button .= '<img src="'. plugins_url('media-upload-zip.gif', __FILE__) .'" alt="'. __( 'upload zip archive', 'upload-media-by-zip' ) .'" />';
		$button .= '</a>';
		echo $button;
	}
	/**
	 * Move unzipped content from temp folder to media library
	 *
	 * @param string $dir Directory to loop through
	 * @param integer $parent Page ID to be used as attachment parent
	 * @param string $return String to append results to
	 * @return string Results as <li> items
	 */
	function move_from_dir( $dir, $parent, $return = '' ) {

		$dir = trailingslashit( $dir );

		$here = glob("$dir*.*" ); //get files

		$dirs = glob("$dir*", GLOB_ONLYDIR|GLOB_MARK ); //get subdirectories

		//start with subs, less confusing
		foreach ($dirs as $k => $sdir) {
			$return .= self::move_from_dir( $sdir, $parent, $return );
		}

		//loop through files and add them to the media library
		foreach ( $here as $img ) {
			$img_name = basename( $img );
			$title = explode( '.', $img_name );
			array_pop( $title );
			$title = implode( '.', $title );

			$img_url = str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, $img );
			$file = array( 'file' => $img, 'tmp_name' => $img, 'name' => $img_name );
			$img_id = media_handle_sideload( $file, $parent, $title );
			if (!is_wp_error( $img_id ) ) {
				$return .= "<li>($img_id) ". sprintf( __( '%s uploaded', 'upload-media-by-zip' ), $img_name ) ."<input type='hidden' name='srcs[]' value='$img_id' /></li>";
			} else {
				$return .= "<li style='color:#a00;'>". sprintf( __( '%s could not be uploaded.', 'upload-media-by-zip' ), "$img_name ($dir)" );
				if ( is_file( $img ) && unlink( $img ) )
					$return .= __( ' It has been deleted.', 'upload-media-by-zip' );
				$return .= "</li>";
			}

		}

		//We need check for hidden files and remove them so that the directory can be deleted
		foreach( glob("$dir.*") as $k => $hidden ) {
 			if ( is_file( $hidden ) )
 				unlink( $hidden );
		}

		//delete any folders that were unzipped
		if ( basename( $dir ) != 'temp')
			rmdir( $dir );

		return $return;
	}
	/**
	 * Handle the initial zip upload
	 *
	 * @return string HTML Results or Error message
	 */
	function handler() {
		wp_enqueue_script('jquery');
		?><script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){

	$('input[name="post_parent"]').keyup( function() {
		$('#page_title').html('...');
		$('#page_title').load('/wp-admin/?get_page_by_title=' + $(this).val() );
	});

	$('#close_box').click( function() {
		$(this).parent().parent().hide();
	});

});
/* ]]> */
</script><?php

		if ( isset( $_FILES[ 'upload-zip-archive' ][ 'name' ] ) && ! empty( $_FILES[ 'upload-zip-archive' ][ 'name' ] ) ) {

			$parent = isset( $_POST['post_parent'] ) ? (int) $_POST['post_parent'] : 0;
			$upl_id = media_handle_upload( 'upload-zip-archive', $parent, array(), array('mimes' => array('zip' => 'application/zip'), 'ext' => array('zip'), 'type' => true, 'action' => 'wp_handle_upload') );
			if ( is_wp_error( $upl_id ) ) {
				return '<div class="error"><p>'. $upl_id->errors['upload_error']['0'] .'</p></div>';
			}
			$file = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, wp_get_attachment_url( $upl_id ) );

			/*
				If the zipped file cannot be unzipped
				try again after uncommenting the lines
				below marked 1, 2, and 3
			*/
			///*1*/	function __return_direct() { return 'direct'; }
			///*2*/	add_filter( 'filesystem_method', '__return_direct' );
			WP_Filesystem();
			///*3*/	remove_filter( 'filesystem_method', '__return_direct' );

			$to = plugins_url( 'temp', __FILE__ );
			$to = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $to );
			$return = '';
			$return .= '<div class="updated">';
			$return .= '<ul style="list-style-type: disc; padding: 10px 35px;">';
			$upl_name = get_the_title( $upl_id );
			$return .= '<li id="close_box" style="list-style-type:none;cursor:pointer;float:right;">X</li>';
			$return .= '<li>'. $upl_name .' uploaded</li>';
			if( !is_wp_error( unzip_file( $file, $to ) ) ) {
				$return .= '<li>'. sprintf( __( '%s extracted', 'upload-media-by-zip' ), $upl_name ) .'</li>';
				$dirs = array();

				$return .= self::move_from_dir( $to, $parent );

				//delete zip file
				$_POST['delete_zip'] = isset( $_POST['delete_zip'] ) ? 1 : 0;
				if ( $_POST['delete_zip'] ) {
					wp_delete_attachment( $upl_id );
					$return .= '<li>'. $upl_name .' deleted</li>';
				}
				else {
					$return .= '<li>'. sprintf( __( '%s was not deleted', 'upload-media-by-zip' ), $upl_name ) .'</li>';
				}
			} else {
				wp_delete_attachment( $upl_id );
				$return .= '<li>'. sprintf( __( '%s could not be extracted and has been deleted', 'upload-media-by-zip' ), $upl_name ) .'</li>';
			}
			$return .= '</ul>';
			$return .= '</div>';

			return $return;
		}

	}
	/**
	 * The upload form
	 *
	 * @param array $args 'action' URL for form action, 'post_id' ID for preset parent ID
	 */
	function form( $args = array() ) {
		$action = '';
		$tab = false;
		if (count( $args ) > 0) {
			$tab = true;
			extract( $args );
		}

		//This code taken from: wp-admin/includes/media.php
		$upload_size_unit = $max_upload_size =  wp_max_upload_size();
		$sizes = array( 'KB', 'MB', 'GB' );
		for ( $u = -1; $upload_size_unit > 1024 && $u < count( $sizes ) - 1; $u++ )
			$upload_size_unit /= 1024;
		if ( $u < 0 ) {
			$upload_size_unit = 0;
			$u = 0;
		} else {
			$upload_size_unit = (int) $upload_size_unit;
		}

		echo '<form action="'. $action .'" method="post" enctype="multipart/form-data">';
		if ( $tab ) {
			echo '<h3 class="media-title">'. __( 'Upload a zip file and extract its contents to the Media Library', 'upload-media-by-zip' ) .'</h3>';
		}
		echo '<p><input type="file" name="upload-zip-archive" id="upload-zip-archive" size="50" /></p>';
		echo '<p>'. sprintf( __( 'Maximum upload file size: %d%s' ), $upload_size_unit, $sizes[ $u ] ) .'</p>';
		echo '<p><label for="delete_zip"><input type="checkbox" name="delete_zip" id="delete_zip" checked="checked" value="1" /> ' . __( 'Delete zip file after upload?', 'upload-media-by-zip' ) . '</label></p>';
		if ( $tab ) {
			echo '<input type="hidden" class="small-text" name="post_parent" value="'. $post_id .'" />';
		} else {
			echo '<p>' . __( 'Attach to (page/post ID)', 'upload-media-by-zip' ) . ': <input type="text" class="small-text" name="post_parent" /> <span id="page_title"></span></p>';
		}

		echo '<p><input type="hidden" name="submitted-upload-media" /><input type="hidden" name="action" value="wp_handle_upload" />
		<input type="submit" class="button-primary" value="' . __( 'Upload and Extract', 'upload-media-by-zip' ) . '"/></p>';

		echo '</form>';
	}

}//end class
$upload_media_by_zip = new Upload_Media_By_Zip( );
