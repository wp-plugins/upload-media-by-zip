<?php
/*
Plugin Name: Upload Media by Zip
Plugin URI: http://trepmal.com/plugins/upload-media-by-zip/
Description: Upload a zip file of images and attach to a page/post
Author: Kailey Lampert
Version: 0.3
Author URI: http://kaileylampert.com/
*/
/*
	Copyright (C) 2010  Kailey Lampert

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

$upload_media_by_zip = new upload_media_by_zip( );

class upload_media_by_zip {

	function upload_media_by_zip( ) {

		add_action( 'admin_menu', array( &$this, 'menu' ) );
		add_action( 'admin_init', array( &$this, 'get_title' ) );

		add_filter('media_upload_tabs', array(&$this, 'create_new_tab'));
		add_action('media_buttons', array(&$this, 'context'), 11);
		add_filter('media_upload_uploadzip', array(&$this, 'media_upload_uploadzip'));

	}

	function menu() {
		$page = add_media_page( __( 'Upload Zip Archive', 'upl_med' ), __( 'Upload Zip Archive', 'upl_med' ), 'edit_themes', __FILE__, array( &$this, 'page' ) );
		add_action( 'admin_print_scripts-' . $page, array( &$this, 'scripts' ) );
	}

		function scripts() {
			wp_enqueue_script('jquery');
		}

		function page() {

			echo '<div class="wrap">';
			echo '<h2>' . __( 'Upload Zip', 'upl_med' ) . '</h2>';

			echo self::handler();
			self::form();

			echo '</div>';

		}// end page()

	function get_title() {
		if (isset($_GET['get_page_by_title'])) {
			$p = get_post($_GET['get_page_by_title']);
			if (is_object($p))
			die($p->post_type.': '.$p->post_title);
			else die();
		}
	}

	function media_upload_uploadzip() {
	    $errors = false;
		return wp_iframe( array( &$this, 'media_uploadzip_tab_content' ), 'media', $errors );
	}

		function media_uploadzip_tab_content($errors) {
			global $type;
			$message = self::handler();

			media_upload_header();
			$post_id = isset( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;

			$form_action_url = admin_url("media-upload.php?type=$type&tab=uploadzip&post_id=$post_id");
			$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type );

			echo $message;
			self::form( array('action' => $form_action_url, 'post_id' => $post_id ) );

		}

	function create_new_tab( $tabs ) {
		$tabs['uploadzip'] = __('Upload Zip Archive');
	    return $tabs;
	}

	function context() {
		global $post_ID;
		$button  = '<a class="thickbox" href="'. admin_url( "media-upload.php?post_id={$post_ID}&tab=uploadzip&TB_iframe=1").'" title="Upload and Extract a Zip Archive">';
		$button .= '<img src="'. plugins_url('media-upload-zip.gif', __FILE__) .'" alt="upload zip archive" />';
		$button .= '</a>';
		echo $button;
	}

	function handler() {
		wp_enqueue_script('jquery');
		?><script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){

	$('input[name="post_parent"]').keyup( function() {
		$('#page_title').load('/wp-admin/?get_page_by_title=' + $(this).val() );
	});

	$('#close_box').click( function() {
		$(this).parent().parent().hide();
	});

});
/* ]]> */
</script><?php

		if ( isset( $_FILES[ 'upload-zip-archive' ][ 'name' ] ) && !empty( $_FILES[ 'upload-zip-archive' ][ 'name' ] ) ) {

			$parent = isset($_POST['post_parent']) ? (int) $_POST['post_parent'] : 0;
			$upl_id = media_handle_upload( 'upload-zip-archive', $parent, array(), array('mimes' => array('zip' => 'application/zip'), 'ext' => array('zip'), 'type' => true, 'action' => 'wp_handle_upload') );
			if (is_wp_error($upl_id)) {
				return '<div class="error"><p>'. $upl_id->errors['upload_error']['0'] .'</p></div>';
			}
			$file = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, wp_get_attachment_url( $upl_id ) );

			WP_Filesystem();
			$to = plugins_url( 'temp', __FILE__ );
			$to = str_replace(WP_CONTENT_URL,WP_CONTENT_DIR, $to );
			$return = '';
			$return .= '<div class="updated">';
			$return .= '<ul style="list-style-type: disc; padding: 10px 35px;">';
			$upl_name = get_the_title( $upl_id );
			$return .= '<li id="close_box" style="list-style-type:none;cursor:pointer;float:right;">X</li>';
			$return .= '<li>'. $upl_name .' uploaded</li>';
			if( unzip_file( $file, $to ) ) {
				$return .= '<li>'. $upl_name .' extracted</li>';
				foreach ( glob("$to/*") as $img ) {
					$img_name = basename($img);
					$img_url = str_replace(WP_CONTENT_DIR,WP_CONTENT_URL, $img );
					$file = array( 'file' => $img, 'tmp_name' => $img, 'name' => $img_name );
					if (!is_wp_error( media_handle_sideload($file, $parent, $img_name) ) ) {
						$return .= "<li>$img_name uploaded</li>";
					} else {
						$return .= '<li style="color:#a00;">$img_name could not be uploaded</li>';
					}
				}
				//delete zip file
				$_POST['delete_zip'] = isset($_POST['delete_zip']) ? 1 : 0;
				if ($_POST['delete_zip']) {
					wp_delete_attachment( $upl_id );
					$return .= '<li>'. $upl_name .' deleted</li>';
				}
				$return .= '</ul>';
				$return .= '</div>';
			}
			
			return $return;
		}

	}//end handler()

	function form( $args = array() ) {
		$action = '';
		$tab = false;
		if (count($args) > 0) {
			$tab = true;
			extract($args);
		}
		echo '<form action="'. $action .'" method="post" enctype="multipart/form-data">';
		if ($tab) {
			echo '<h3 class="media-title">'. __( 'Upload a zip file and extract its contents to the Media Library', 'upl_med' ) .'</h3>';
		}
		echo '<p><input type="file" class="button" name="upload-zip-archive" id="upload-zip-archive" size="50" /></p>';
		echo '<p><label for="delete_zip"><input type="checkbox" name="delete_zip" id="delete_zip" checked="checked" value="1" /> ' . __( 'Delete zip file after upload?', 'upl_med' ) . '</label></p>';
		if ($tab) {
			echo '<input type="hidden" class="small-text" name="post_parent" value="'. $post_id .'" />';
		} else {
			echo '<p>' . __( 'Attach to (page/post ID)', 'upl_med' ) . ': <input type="text" class="small-text" name="post_parent" /> <span id="page_title"></span></p>';
		}

		echo '<p><input type="hidden" name="submitted-upload-media" /><input type="hidden" name="action" value="wp_handle_upload" />
		<input type="submit" class="button-primary" value="' . __( 'Upload and Extract', 'upl_med' ) . '"/></p>';

		echo '</form>';
	}

}//end class
