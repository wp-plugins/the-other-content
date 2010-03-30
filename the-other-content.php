<?php
/*
Plugin Name: The Other Content
Plugin URI: http://www.technokinetics.com/plugins/the-other-content
Description: Add a second editable content area to your Pages.
Version: 2.0
Author: Tim Holt
Author URI: http://www.technokinetics.com/

    Copyright 2009 Tim Holt (tim@technokinetics.com)

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

global $wpdb;

// ACTIVATION

function the_other_content_install() {
	add_option('toc_on_pages', 'on');
	add_option('toc_on_posts', 'off');
	add_option('toc_delete_data_on_deactivation', 'off');
}

register_activation_hook(__FILE__, 'the_other_content_install');

// DEACTIVATION

function the_other_content_uninstall() {
	if (get_option('toc_delete_data_on_deactivation') == 'on') {
		delete_option('toc_content_names');
		delete_option('toc_on_pages');
		delete_option('toc_on_posts');
		delete_option('toc_delete_data_on_deactivation');		
	}
	
	/* Doesn't yet delete postmeta entries */
}

register_deactivation_hook(__FILE__, 'the_other_content_uninstall' );

// ADMIN MENU

function the_other_content_admin_menu() {
	if ($_GET['toc_delete']) {
		$content_names = get_option('toc_content_names');
		$content_names = str_replace($_GET['toc_delete'], '', $content_names);
		$content_names = str_replace(',,', ',', $content_names);
		$content_names = trim($content_names, ',');
		update_option('toc_content_names', $content_names);
	}
	
	if (!$_POST['toc_new_name'] == '') {
		$toc_new_name = $_POST['toc_new_name'];
				
		if (get_option('toc_content_names')) {
			update_option('toc_content_names', get_option('toc_content_names') . ',' . $toc_new_name);
		} else {
			update_option('toc_content_names', $toc_new_name);
		}
		
		if ($_POST['toc_new_global_pages']) {
			if (get_option('toc_global_pages')) {
				update_option('toc_global_pages', get_option('toc_global_pages') . ',' . $toc_new_name);
			} else {
				update_option('toc_global_pages', $toc_new_name);
			}
		}
		
		if ($_POST['toc_new_global_posts']) {
			if (get_option('toc_global_posts')) {
				update_option('toc_global_posts', get_option('toc_global_posts') . ',' . $toc_new_name);
			} else {
				update_option('toc_global_posts', $toc_new_name);
			}
		}
	} ?>
	
	<style>
	#toc table { }
	#toc table th { padding: 3px 8px; background: #eee; }
	#toc table td { padding: 3px 8px; background: #ddd; }
	</style>
	
	<div id="toc" class="wrap">
		<h2>The Other Content</h2>
		<p>By default, WordPress gives you one editable content area per page. The Other Content allows you to create additional content areas for some or all of your Pages or Posts. You can create as many additional content areas as you like, and call them whatever you want.</p>
		<p>The Other Content creates two new functions for you to use to display additional content: <code>the_other_content($content_area_name, [$post_id])</code>, and <code>get_the_other_content($content_area_name, [$post_id])</code>.
		<p>To display additional content inside the loop, add <code>the_other_content('Content Area Name')</code> to your theme files. To get additional content without displaying it and store it in a variable $content_area_name, add <code>$content_area_name = get_the_other_content('Content Area Name')</code>. To test whether a page has additional content, test whether <code>get_the_other_content('Content Area Name')</code> returns 'false'.</p>
		<p>To display additional content outside the loop, add <code>the_other_content('Content Area Name', 'ID')</code> to your theme files. To get additional content without displaying it, add <code>$content_area_name = get_the_other_content('Content Area Name', 'ID')</code>.</p>
		<form method="post" action="options-general.php?page=the-other-content/the-other-content.php">
			<h3>Create New Content Area</h3>
			<p>
				<input type="text" id="toc_new_name" name="toc_new_name" value="" />
				<input type="checkbox" id="toc_new_global_pages" name="toc_new_global_pages" /> <label for="toc_new_global_pages">All Pages</label>
				<input type="checkbox" id="toc_new_global_posts" name="toc_new_global_posts" /> <label for="toc_new_global_posts">All Posts</label>
			</p>
			
			<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Create') ?>" />
			</p>
		</form>
		
		<form method="post" action="options.php">
			<h3>Manage Content Areas</h3>
			<table>
				<thead>
					<tr>
						<th>Content Area Name</th>
						<th>Global for Pages</th>
						<th>Global for Posts</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody><?php
					$content_names = get_option('toc_content_names');
					$content_names = explode(',', $content_names);
					foreach ($content_names as $content_name) { ?>
						<tr>
							<td><?php echo $content_name; ?></td>
							<td><?php if (toc_is_global($content_name, 'page')) { ?>Yes<?php } else { ?>No<?php } ?></td>
							<td><?php if (toc_is_global($content_name, 'post')) { ?>Yes<?php } else { ?>No<?php } ?></td>
							<td><a href="options-general.php?toc_delete=<?php echo $content_name; ?>&page=the-other-content/the-other-content.php">delete</a></td>
						</tr><?php
					} ?>
				</tbody>
			</table>
			
			<h3>Plugin Options</h3>
			<p>Do you want to use secondary content on Posts, Pages, or both?</p>
			<ul style="list-style: none;">
				<li><input type="checkbox" id="toc_on_pages" name="toc_on_pages" <?php if (get_option('toc_on_pages') == "on") { echo 'checked="checked" '; } ?>/> <label for="toc_on_pages">Pages</label></li>
				<li><input type="checkbox" id="toc_on_posts" name="toc_on_posts" <?php if (get_option('toc_on_posts') == "on") { echo 'checked="checked" '; } ?>/> <label for="toc_on_posts">Posts</label></li>
			<p>When The Other Content is deactivated, should the data associated with it be deleted from the WordPress database? (N.B. If this box is checked, then The Other Content data will not be recoverable after deactivation.)</p>
			<ul style="list-style: none;">
				<li><input type="checkbox" id="toc_delete_data_on_deactivation" name="toc_delete_data_on_deactivation" <?php if (get_option('toc_delete_data_on_deactivation') == "on") { echo 'checked="checked" '; } ?>/> <label for="toc_delete_data_on_deactivation">Delete plugin data on deactivation</label></li>
			</ul>
			<h3>Feedback</h3>
			<p>If you've found The Other Content useful, then please consider <a href="http://wordpress.org/extend/plugins/the-other-content/">rating it</a>, linking to <a href="http://www.technokinetics.com/">my website</a>, or <a href="http://www.technokinetics.com/donations/">making a donation</a>.</p>
			<p>If you haven't found it useful, then please consider <a href="mailto:tim@technokinetics.com?subject=TOC Bug Report">filing a bug report</a> or <a href="mailto:tim@technokinetics.com?subject=TOC Feature Request">making a feature request</a>.</p>
			<p>Thanks!</p>
			<p>- Tim Holt, <a href="http://www.technokinetics.com/">Technokinetics</a></p>
			
			<p class="submit">
				<?php wp_nonce_field('update-options'); ?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="toc_on_pages,toc_on_posts,toc_delete_data_on_deactivation" />
				<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>		
	</div><?php
}

function add_the_other_content_admin_menu() {
	add_options_page('The Other Content', 'The Other Content', 9, __FILE__, 'the_other_content_admin_menu');
}

add_action('admin_menu', 'add_the_other_content_admin_menu');

// Edit Box

function the_other_content_inner() {
	global $post; ?>	
	<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea"><?php
		if (get_option('toc_content_names')) {
			$content_names = get_option('toc_content_names');
			$content_names = explode(',', $content_names); ?>
			
			<h4>Select Additional Content Areas for this <?php if ($post->post_type == 'page') { ?>Page<?php } else { ?>Post<?php } ?></h4>
			<ul id="toc_show_list"><?php
				foreach ($content_names as $content_name) { ?>
					<li><input type="checkbox" id="toc_show_<?php echo $content_name; ?>" name="toc_show_<?php echo $content_name; ?>" <?php if (get_post_meta($post->ID, '_toc_show_' . $content_name, true) == 'on' || toc_is_global($content_name, $post->post_type)) { echo 'checked="checked" '; } ?> /> <label for="toc_show_<?php echo $content_name; ?>"><?php echo $content_name; ?></label></li><?php
				} ?>
			</ul>
			<div style="clear: both;"></div><?php
			
			foreach ($content_names as $content_name) {
				if (get_post_meta($post->ID, '_toc_show_' . $content_name, true) == 'on' || toc_is_global($content_name, $post->post_type)) {
					echo '<h4>' . $content_name . '</h4>';
					wp_tiny_mce( false , // true makes the editor "teeny"
					    array(
					        "editor_selector" => "toc_" . $content_name
					    )
					); ?>
					<textarea id="toc_<?php echo $content_name; ?>" class="theEditor" name="toc_<?php echo $content_name; ?>"><?php the_other_content($content_name); ?></textarea><?php
				}
			}
		} ?>
	</div><?php
}

function the_other_content_add_fields() {
	if (function_exists('add_meta_box')) {
		if (get_option('toc_on_pages') == 'on') {
			add_meta_box('toc_box', 'The Other Content', 'the_other_content_inner', 'page', 'normal', 'high');
		}
		if (get_option('toc_on_posts') == 'on') {
			add_meta_box('toc_box', 'The Other Content', 'the_other_content_inner', 'post', 'normal', 'high');
		}
	}
}

add_action('admin_menu', 'the_other_content_add_fields');

// SAVE DATA

function save_the_other_content($post_id) {
	if(!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id)) {
		if (get_option('toc_content_names')) {
			$content_names = get_option('toc_content_names');
			$content_names = explode(',', $content_names);
			foreach ($content_names as $content_name) {
				if ($_POST['toc_show_' . $content_name]) {
					update_post_meta($post_id, '_toc_show_' . $content_name, 'on');
				} else {
					delete_post_meta($post_id, '_toc_show_' . $content_name);
				}
			}
			
			foreach ($content_names as $content_name) {
				update_post_meta($post_id, '_toc_' . $content_name, $_POST['toc_' . $content_name]);
			}
		}
	}
}

function toc_is_global($content_name, $post_type) {
	if ($post_type == 'page') {
		$toc_global_pages = get_option('toc_global_pages');
		$toc_global_pages = explode(',', $toc_global_pages);
		if (in_array($content_name, $toc_global_pages)) {
			return true;
		}
		return false;
	}
	
	if ($post_type == 'post') {
		$toc_global_posts = get_option('toc_global_posts');
		$toc_global_posts = explode(',', $toc_global_posts);
		if (in_array($content_name, $toc_global_posts)) {
			return true;
		}
		return false;
	}
}

add_action('save_post', 'save_the_other_content');

function the_other_content($content_name, $post_id = 'null') {
	echo get_the_other_content($content_name, $post_id);
}

function get_the_other_content($content_name, $post_id = 'null') {
	if ($post_id == 'null') {
		global $post;
		$post_id = $post->ID;
	}
	$the_other_content = get_post_meta($post_id, '_toc_' . $content_name, true);
	$the_other_content = apply_filters('the_content', $the_other_content);
	$the_other_content = str_replace(']]>', ']]&gt;', $the_other_content);
	return $the_other_content;
}
?>