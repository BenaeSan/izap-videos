<?php

/*
 *    This file is part of izap-videos plugin for Elgg.
 *
 *    izap-videos for Elgg is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    izap-videos for Elgg is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with izap-videos for Elgg.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Get page components to list a user's or all izap-videos.
 * 
 * @param integer  $container_guid
 * 
 * @return array   array of content for rendering video list 
 * 
 * @version 5.0
 */
function izap_video_get_page_content_list($container_guid = NULL) {

	$return = array();
	$return['filter_context'] = $container_guid ? 'mine' : 'all';
	$options = array(
		'type' => 'object',
		'subtype' => GLOBAL_IZAP_VIDEOS_SUBTYPE,
		'full_view' => false,
		'no_results' => elgg_echo('izap-videos:none'),
	);
	$url_id = elgg_get_logged_in_user_guid();
	$current_user = elgg_get_logged_in_user_entity();
	if ($container_guid) {
		$url_id = $container_guid;
		// access check for closed groups
		izap_group_gatekeeper();
		$options['container_guid'] = $container_guid;
		$container = get_entity($container_guid);
		$return['title'] = elgg_echo('izap-videos:title:user_videos', array($container->name));
		$crumbs_title = $container->name;
		elgg_push_breadcrumb($crumbs_title);

		if ($current_user && ($container_guid == $current_user->guid)) {
			$return['filter_context'] = 'mine';
		} else if (elgg_instanceof($container, 'group')) {
			$return['filter'] = false;
		} else {
			// do not show button or select a tab when viewing someone else's posts
			$return['filter_context'] = 'none';
		}
	} else {
		$return['filter_context'] = 'all';
		$return['title'] = elgg_echo('izap-videos:title:all_videos');
		elgg_pop_breadcrumb();
		elgg_push_breadcrumb(elgg_echo('izap-videos'));
	}
	if (elgg_is_logged_in()) {
		$title = 'Add New Video';
	}

	$url = GLOBAL_IZAP_VIDEOS_PAGEHANDLER . '/add/';

	if (izap_is_onserver_enabled_izap_videos() == 'yes') {
		$url .= $url_id . '/onserver';
	} elseif (izap_is_onserver_enabled_izap_videos() == 'youtube') {
		$url .= $url_id . '/youtube';
	} elseif (izap_is_offserver_enabled_izap_videos() == 'yes') {
		$url .= $url_id . '/offserver';
	} else {
		$url .= $url_id . '/offserver';
	}
	elgg_register_menu_item('title', array(
			'name' => elgg_get_friendly_title($title),
			'href' => $url,
			'text' => $title,
			'link_class' => 'elgg-button elgg-button-action',
		));
	$return['content'] = elgg_list_entities($options);
	return $return;
}

/**
 * Get page components to list of the user's friends' posts.
 *  
 * @param integer  $user_guid
 * 
 * @return array   array of content for rendering friend's video list 
 * 
 * @version 5.0
 */
function izap_video_get_page_content_friends($user_guid = NULL) {
	$user = get_user($user_guid);
	if (!$user) {
		forward(GLOBAL_IZAP_VIDEOS_PAGEHANDLER . '/all');
	}
	$return = array();
	$return['filter_context'] = 'friends';
	$return['title'] = elgg_echo('izap-videos:title:friends');
	$crumbs_title = $user->name;
	elgg_push_breadcrumb($crumbs_title, GLOBAL_IZAP_VIDEOS_PAGEHANDLER . "/owner/{$user->username}");
	elgg_push_breadcrumb(elgg_echo('friends'));
	$title = 'Add New Video';
	$url = GLOBAL_IZAP_VIDEOS_PAGEHANDLER . '/add/';
	if (izap_is_onserver_enabled_izap_videos() == 'yes') {
		$url .= elgg_get_logged_in_user_guid() . '/onserver';
		elgg_register_menu_item('title', array(
			'name' => elgg_get_friendly_title($title),
			'href' => $url,
			'text' => $title,
			'link_class' => 'elgg-button elgg-button-action',
		));
	} elseif (izap_is_onserver_enabled_izap_videos() == 'youtube') {
		$url .= elgg_get_logged_in_user_guid() . '/youtube';
		elgg_register_menu_item('title', array(
			'name' => elgg_get_friendly_title($title),
			'href' => $url,
			'text' => $title,
			'link_class' => 'elgg-button elgg-button-action',
		));
	} elseif (izap_is_offserver_enabled_izap_videos() == 'yes') {
		$url .= elgg_get_logged_in_user_guid() . '/offserver';
		elgg_register_menu_item('title', array(
			'name' => elgg_get_friendly_title($title),
			'href' => $url,
			'text' => $title,
			'link_class' => 'elgg-button elgg-button-action',
		));
	} else {
		$url .= elgg_get_logged_in_user_guid() . '/offserver';
		elgg_register_menu_item('title', array(
			'name' => elgg_get_friendly_title($title),
			'href' => $url,
			'text' => $title,
			'link_class' => 'elgg-button elgg-button-action',
		));
	}
	$options = array(
		'type' => 'object',
		'subtype' => GLOBAL_IZAP_VIDEOS_SUBTYPE,
		'full_view' => false,
		'relationship' => 'friend',
		'relationship_guid' => $user_guid,
		'relationship_join_on' => 'container_guid',
		'no_results' => elgg_echo('izap-videos:none'),
	);

	$return['content'] = elgg_list_entities_from_relationship($options);

	return $return;
}

/**
 * Get page components to edit/create a izap-video post.
 * 
 * @param string  $page
 * @param integer $guid
 * @param string  $revision
 * 
 * @return array  array of content for rendering add/edit video form 
 * 
 * @version 5.0
 */
function izap_video_get_page_content_edit($page, $guid = 0, $revision = NULL) {
	$return = array(
		'filter' => '',
	);
	$form_vars = array();
	$body_vars = array();
	if ($page == 'edit') {
		$izap_video = get_entity((int) $guid);
		$title = elgg_echo('izap_videos:edit') . ":";
		if (elgg_instanceof($izap_video, 'object', GLOBAL_IZAP_VIDEOS_SUBTYPE) && $izap_video->canEdit()) {
			$form_vars['entity'] = $izap_video;
			$form_vars['name'] = "video_upload";
			$title .= ucwords($izap_video->title);
			$izap_video->container_guid = $izap_video->container_guid;
			$body_vars = izap_videos_prepare_form_vars($izap_video, $revision);
			elgg_push_breadcrumb($izap_video->title, $izap_video->getURL($izap_video->getOwnerEntity(), GLOBAL_IZAP_VIDEOS_PAGEHANDLER));
			elgg_push_breadcrumb(elgg_echo('edit'));
			$content = elgg_view_form('izap-videos/save', $form_vars, $body_vars);
		}
	} else {
		elgg_push_breadcrumb(elgg_echo('izap_videos:add'));
		$izap_video->container_guid = $guid;
		$body_vars = izap_videos_prepare_form_vars($izap_video);
		$form_vars = array('enctype' => 'multipart/form-data', 'name' => 'video_upload');
		$title = elgg_echo('izap-videos:add');
		$content = elgg_view_form('izap-videos/save', $form_vars, $body_vars);
	}
	$return['title'] = $title;
	$return['content'] = $content;
	return $return;
}

/**
 * Get page components to upload youtube video.
 * 
 * @param string  $page
 * @param integer $guid
 * @param string  $revision
 * 
 * @return array  array of content for YouTube video uploading
 * 
 * @version 5.0
 */
function izap_video_get_page_content_youtube_upload($page, $guid = 0, $revision = NULL) {
	$return = array(
		'filter' => '',
	);
	$form_vars = array();
	$params = array();

	$video = IzapGYoutube::getAuthSubHttpClient(get_input('token', false));

//get youtube api authorization via users application access.
//	if (get_input('token')) {
	$video = IzapGYoutube::getAuthSubHttpClient(get_input('token', false));

	if ($video instanceof IzapGYoutube) {
		$yt = $video->YoutubeObject();
		$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
		$myVideoEntry->setVideoTitle($_SESSION['youtube_attributes']['title']);
		$description = strip_tags($_SESSION['youtube_attributes']['description']);
		$myVideoEntry->setVideoDescription($description);

		// Note that category must be a valid YouTube category
		$myVideoEntry->setVideoCategory($_SESSION['youtube_attributes']['youtube_cats']);
		$myVideoEntry->SetVideoTags($_SESSION['youtube_attributes']['tags']);
		$tokenHandlerUrl = 'http://gdata.youtube.com/action/GetUploadToken';
		try {
			$tokenArray = $yt->getFormUploadToken($myVideoEntry, $tokenHandlerUrl);
		} catch (Exception $e) {
			if (preg_match("/<code>([a-z_]+)<\/code>/", $e->getMessage(), $matches)) {
				register_error('YouTube Error: ' . $matches[1]);
			} else {
				register_error('YouTube Error: ' . $e->getMessage());
			}
			forward(izap_set_href(array(
				'context' => GLOBAL_IZAP_VIDEOS_PAGEHANDLER,
				'action' => 'add',
				'page_owner' => elgg_get_logged_in_user_guid(),
				'vars' => array('tab' => 'youtube'),
			)));
		}
		$params['token'] = $tokenArray['token'];
		$params['action'] = $tokenArray['url'] . '?nexturl=' . elgg_get_site_url() . GLOBAL_IZAP_VIDEOS_PAGEHANDLER . '/next&scope=https://gdata.youtube.com&session=1&secure=0';
		elgg_push_breadcrumb(elgg_echo('upload'));

		$form_vars = array(
			'enctype' => 'multipart/form-data',
			'name' => 'video_upload',
			'action' => $params['action'],
			'id' => 'izap-video-form',
		);
		$title = elgg_echo('Upload video with title: "' . $_SESSION['youtube_attributes']['title'] . '"');
		$content = elgg_view_form('izap-videos/youtube_upload', $form_vars, $params);
		$return['title'] = $title;
		$return['content'] = $content;
		return $return;
	} else {
		register_error('You must have to grant access for youtube upload');
		forward();
	}
}

/**
 * Check YouTube response
 * 
 * @version 5.0
 */
function izap_video_get_page_content_youtube_next() {
	$is_status = (get_input('status') == 200) ? true : false;
	if (!$is_status) {
		// redirect the user from where he was trying to upload the video.
		register_error("We did not get expected response from YouTube. You might need to provide appropriate youtube category.");
		forward(izap_set_href(array(
			'context' => GLOBAL_IZAP_VIDEOS_PAGEHANDLER,
			'action' => 'add',
			'page_owner' => elgg_instanceof(elgg_get_page_owner_entity(), 'group') ? elgg_get_page_owner_entity()->username : elgg_get_logged_in_user_entity()->username,
			'vars' => array('tab' => ($onserver = izap_is_onserver_enabled_izap_videos()) ?
					($onserver == 'yes') ? 'onserver' : 'youtube' :
					'offserver'),
		)));
		exit;
	}
	$id = get_input('id');
	$pass = '%kdkdhSw*jdksl';
	forward(elgg_add_action_tokens_to_url(elgg_get_site_url() . GLOBAL_IZAP_VIDEOS_PAGEHANDLER . '/youtube_response?id=' . $id . '&p=' . $pass));
	exit;
}

/**
 * Show particular saved entity
 * 
 * @param integer  $guid
 * 
 * @return array   array of video content
 * 
 * @version 5.0
 */
function izap_videos_read_content($guid = null) {
	$return = array();
	$izap_video = get_entity($guid);
	if (!$izap_video) {
		forward();
	}
	if (!elgg_instanceof($izap_video, 'object', GLOBAL_IZAP_VIDEOS_SUBTYPE)) {
		exit;
	}
	$return['title'] = ucwords($izap_video->title);
	$return['content'] = elgg_view_entity($izap_video, array('full_view' => true));
	if ($izap_video->comments_on != 'Off') {
		$return['content'] .= elgg_view_comments($izap_video);
	}
	return $return;
}

/**
 * Prepare variables for izap-video save form
 * 
 * @param  array  $video
 * 
 * @return array  array of variables for save form
 * 
 * @version 5.0
 */
function izap_videos_prepare_form_vars($video = NULL) {
	$values = array(
		'title' => NULL,
		'description' => NULL,
		'access_id' => ACCESS_DEFAULT,
		'tags' => NULL,
		'container_guid' => NULL,
		'guid' => NULL,
		'video_url' => NULL
	);
	if ($video) {
		foreach (array_keys($values) as $field) {
			if (isset($video->$field)) {
				$values[$field] = $video->$field;
			}
		}
	}
	if (elgg_is_sticky_form('izap_videos')) {
		$sticky_values = elgg_get_sticky_values('izap_videos');
		foreach ($sticky_values as $key => $value) {
			$values[$key] = $value;
		}
	}

	elgg_clear_sticky_form('izap_videos');

	return $values;
}

/**
 * Check whether operating sysytem is window 
 * 
 * @return boolean true if operating system is window, false if operating system is not window 
 * 
 * @version 5.0
 */
function izap_is_win_izap_videos() {
	if (strtolower(PHP_OS) == 'winnt') {
		return true;
	} else {
		return false;
	}
}

/**
 * Check upload filesize
 * 
 * @param integer  $inputSize
 * 
 * @return string  string of readable size
 * 
 * @version 5.0
 */
function izap_readable_size_izap_videos($inputSize) {
	if (strpos($inputSize, 'M'))
		return $inputSize . 'B';
	$outputSize = $inputSize / 1024;
	if ($outputSize < 1024) {
		$outputSize = number_format($outputSize, 2);
		$outputSize .= ' KB';
	} else {
		$outputSize = $outputSize / 1024;
		if ($outputSize < 1024) {
			$outputSize = number_format($outputSize, 2);
			$outputSize .= ' MB';
		} else {
			$outputSize = $outputSize / 1024;
			$outputSize = number_format($outputSize, 2);
			$outputSize .= ' GB';
		}
	}
	return $outputSize;
}

/**
 * Return admin settings
 * 
 * @param string  $settingName
 * @param array   $values
 * @param boolean $override
 * @param array   $makeArray
 * 
 * @return array  array of admin settings
 * 
 * @version 5.0
 */
function izap_admin_settings_izap_videos($settingName, $values = '', $override = false, $makeArray = false) {
	$send_array = array(
		'name' => $settingName,
		'value' => $values,
		'plugin' => GLOBAL_IZAP_VIDEOS_PLUGIN,
	);
	return izap_plugin_setting($send_array);
}

/**
 * Get plugin settings
 * 
 * @param array $supplied_array
 * 
 * @return string string of plugin settings
 * 
 * @version 5.0
 */
function izap_plugin_setting($supplied_array) {
	$default = array(
		'override' => FALSE,
		'make_array' => FALSE,
	);
	$input = array_merge($default, (array) $supplied_array);
	// get old values
	$old_value = elgg_get_plugin_setting($input['name'], $input['plugin']);
	//make new value
	if (is_array($input['value'])) {
		$new_value = implode('|', $input['value']);
	} else {
		$new_value = $input['value'];
	}
	if ((!(bool) $old_value && !empty($new_value)) || $input['override']) {
		if (!elgg_set_plugin_setting($input['name'], $new_value, $input['plugin'])) {
			return FALSE;
		} else {
			$return = $new_value;
		}
	}
	if ((bool) $old_value !== FALSE) {
		$old_array = explode('|', $old_value);
		if (count($old_array) > 1) {
			$return = $old_array;
		} else {
			$return = $old_value;
		}
	}
	if (!is_array($return) && $input['make_array'] && (bool) $return) {
		$new_return_val[] = $return;
		$return = $new_return_val;
	}
	return $return;
}

/**
 * Checks if onserver videos are enabled in admin settings
 * 
 * @return string   string of enable/disable onserver settings
 * 
 * @version 5.0
 */
function izap_is_onserver_enabled_izap_videos() {
	$settings = izap_plugin_setting(array(
		'name' => 'onserver_enabled_izap_videos',
		'plugin' => GLOBAL_IZAP_VIDEOS_PLUGIN,
	));

	if ((string) $settings === 'no') {
		return FALSE;
	}

	return $settings;
}

/**
 * Check whether offserver videos are enabled in admin settings
 * 
 * @return string   string of enable/disable offserver settings
 * 
 * @version 5.0
 */
function izap_is_offserver_enabled_izap_videos() {
	$setting = izap_plugin_setting(array(
		'name' => 'Offserver_enabled_izap_videos',
		'plugin' => GLOBAL_IZAP_VIDEOS_PLUGIN,
	));
	if ((string) $setting === 'no') {
		return false;
	}
	return $setting;
}

/**
 * Return form action
 * 
 * @global array  $CONFIG
 * @param string  $file
 * @param string  $plugin
 * 
 * @return string string of form's action
 * 
 * @version 5.0
 */
function izap_get_form_action($file, $plugin) {
	global $CONFIG;
	return $CONFIG->wwwroot . 'action/' . $plugin . '/' . $file;
}

/**
 * This function triggers the queue
 *
 * @version 5.0
 */
function izap_trigger_video_queue() {
	$PHPpath = izap_get_php_path_izap_videos();
	$file_path = elgg_get_plugins_path() . GLOBAL_IZAP_VIDEOS_PLUGIN . '/izap_convert_video.php';
	if (!izap_is_queue_running_izap_videos()) {
		if (izap_is_win_izap_videos()) {
			pclose(popen("start \"MyProcess\" \"cmd /C " . $PHPpath . " " . $file_path, "r"));
		} else {
			exec($PHPpath . ' ' . $file_path . ' izap web > /dev/null 2>&1 &', $output);
		}
	}
}

/**
 * This function checks if the queue is running or not
 *
 * @return boolean true if yes or false if no
 * 
 * @version 5.0
 */
function izap_is_queue_running_izap_videos() {
	$queue_object = new izapQueue();
	$numberof_process = $queue_object->check_process();
	if ($numberof_process > 0) {
		return true;
	} else {
		return false;
	}
}

/**
 * Return the file's extension if file found
 * 
 * @param string  $filename
 * 
 * @return mixed file extension if found else false
 * 
 * @version 5.0
 */
function izap_get_file_extension($filename) {
	if (empty($filename)) {
		return false;
	}
	$filename1 = explode('.', $filename);
	return strtolower(end($filename1));
}

/**
 * This function return the path of PHP Interpreter
 *
 * @return string path for php interpreter
 * 
 * @version 5.0
 */
function izap_get_php_path_izap_videos() {
	$path = izap_admin_settings_izap_videos('izapPhpInterpreter');
	$path = html_entity_decode($path);
	if (!$path)
		$path = '';
	return $path;
}

/**
 * Grant access
 * 
 * @version 5.0
 */
function izap_get_access_izap_videos() {
	izap_access_override(array('status' => true));
}

/**
 * Remove access
 * 
 * @version 5.0
 */
function izap_remove_access_izap_videos() {
	izap_access_override(array('status' => false));
}

/**
 * Override access
 * 
 * @param array  $params
 * 
 * @version 5.0
 */
function izap_access_override($params = array()) {
	if ($params['status']) {
		$func = "elgg_register_plugin_hook_handler";
	} else {
		$func = "elgg_unregister_plugin_hook_handler";
	}
	$func_name = "izapGetAccessForAll_izap_videos";
	$func("premissions_check", "all", $func_name, 9999);
	$func("container_permissions_check", "all", $func_name, 9999);
	$func("permissions_check:metadata", "all", $func_name, 9999);
}

/**
 * Elgg hook to override permission check of entities (izap_videos, izapVideoQueue, izap_recycle_bin)
 *
 * @param string  $hook
 * @param string  $entity_type
 * @param array   $returnvalue
 * @param array   $params
 * 
 * @return boolean
 * 
 * @version 5.0
 */
function izap_get_access_for_all_izap_videos($hook, $entity_type, $returnvalue, $params) {
	return true;
}

/**
 * Get videos which are in queue
 * 
 * @version 5.0
 */
function izap_get_queue() {
	$queue_status = (izap_is_queue_running_izap_videos()) ?
		elgg_echo('izap_videos:running') :
		elgg_echo('izap_videos:notRunning');
	$queue_object = new izapQueue();
	echo elgg_view(GLOBAL_IZAP_VIDEOS_PLUGIN . '/queue_status', array(
		'status' => $queue_status,
		'total' => $queue_object->count(),
		'queue_videos' => $queue_object->get(),
		)
	);
	exit;
}

/**
 * A quick way to convert bytes to a more readable format
 * http://in3.php.net/manual/en/function.filesize.php#91477
 *
 * @param integer  $bytes size in bytes
 * @param integer  $precision
 * 
 * @return string  string of readable file size
 * 
 * @version 5.0
 */
function izap_format_bytes($bytes, $precision = 2) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= pow(1024, $pow);
	return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Save file info for video conversion
 * 
 * @param string  $file
 * @param array   $video
 * @param integer $defined_access_id
 * @param array   $izapvideo
 * 
 * @return boolean
 * 
 * @version 5.0
 */
function izap_save_fileinfo_for_converting_izap_videos($file, $video, $defined_access_id = 2, $izapvideo) {
	// this will not let save any thing if there is no file to convert
	if (!file_exists($file) || !$video) {
		return false;
	}
	$queue = new izapQueue();
	$queue->put($video, $file, $defined_access_id, $izapvideo->getURL($izapvideo->getOwnerEntity(), GLOBAL_IZAP_VIDEOS_PAGEHANDLER));
	//set state processing for video
	$izapvideo->converted = 'in_processing';
	//run queue
	izap_trigger_video_queue();
}

/**
 * Fetch videos from queue and send these videos for conversion
 * 
 * @return boolean
 * 
 * @version 5.0
 */
function izap_run_queue_izap_videos() {
	$queue_object = new izapQueue();
	$queue = $queue_object->fetch_videos();
	if (defined('IZAP_VIDEO_UNIT_TEST')) {
		if (IZAP_VIDEO_UNIT_TEST === True) {
			global $CONFIG;
			$converted = izap_convert_video_izap_videos($CONFIG->dataroot . '/test_video.avi', '', '', '', 77);
		}
	} elseif (is_array($queue)) {
		izap_get_all_access();
		foreach ($queue as $pending) {
			$converted = izap_convert_video_izap_videos($pending['main_file'], $pending['guid'], $pending['title'], $pending['url'], $pending['owner_id']);
			$izap_video = get_entity($pending['guid']);
			if (is_array($converted) && $converted['error']) {
				$izap_video->converted = 'no';
				$queue_object->move_to_trash($pending['guid']);
			} else {
				$izap_video->converted = 'yes';
				$queue_object->delete($pending['guid']);
			}
		}
		if ($queue_object->count() > 0) {
			izap_run_queue_izap_videos();
		}
	}
	return true;
}

/**
 * This function gives the FFmpeg video converting command
 *
 * @return string path
 * 
 * @version 5.0
 */
function izap_get_ffmpeg_videoConvertCommand_izap_videos() {
	$path = elgg_get_plugin_setting('izapVideoCommand', GLOBAL_IZAP_VIDEOS_PLUGIN);
	$path = html_entity_decode($path);
	if (!$path)
		$path = '';
	return $path;
}

/**
 * Get thumbanil from uploaded video
 * 
 * @return string   path of ffmpeg command
 * 
 * @version 5.0
 */
function izap_get_ffmpeg_thumbnailCommand() {
	$path = elgg_get_plugin_setting('izapVideoThumb', GLOBAL_IZAP_VIDEOS_PLUGIN);
	$path = html_entity_decode($path);
	if (!$path)
		$path = '';
	return $path;
}

/**
 * Check file existance and send for conversion
 * 
 * @param string  $file
 * @param integer $videoId
 * @param string  $videoTitle
 * @param string  $videoUrl
 * @param integer $ownerGuid
 * @param integer $accessId
 * 
 * @return mixed  string if video converted successfully, array if video not converted successfully
 * 
 * @version 5.0
 */
function izap_convert_video_izap_videos($file, $videoId, $videoTitle, $videoUrl, $ownerGuid, $accessId = 2) {
	if (file_exists($file)) {
		$queue_object = new izapQueue();
		$video = new izapConvert($file);
		$videofile = $video->izap_video_convert(); //if file converted successfully then change flag from pending to processed
		if (!is_array($videofile)) {
			$queue_object->change_conversion_flag($videoId);
			return $videofile;
		} else {
			$err_message = $videofile['message'];
		}
	} else {
		$err_message = elgg_echo('izap_videos:file not found');
	}
	if (isset($err_message)) {
		$return = array('error' => true, 'message' => $err_message);
	}
	return $return;
}

/**
 * Read video file content
 * 
 * @version 5.0
 */
function izap_read_video_file() {
	$guid = (int) get_input('videoID');
	$entity = get_entity($guid);
	if (!elgg_instanceof($entity, 'object', GLOBAL_IZAP_VIDEOS_SUBTYPE)) {
		exit;
	}
	if ($entity->videofile) {
		$get_video_name = end(explode('/', $entity->videofile));
		$izapvideo_obj = new IzapVideo;
		$set_video_name = $izapvideo_obj->getTmpPath($get_video_name);
		if (izap_get_file_extension($set_video_name) == 'flv') {
			$set_video_name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $set_video_name) . '.flv';
		} else {
			$set_video_name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $set_video_name) . '_c.flv';
		}
		$elggfile_obj = new ElggFile;
		$elggfile_obj->owner_guid = $entity->owner_guid;
		$elggfile_obj->setFilename($set_video_name);

		if (file_exists($elggfile_obj->getFilenameOnFilestore())) {
			$contents = $elggfile_obj->grabFile();
		}
		$content_type = 'video/x-flv';
		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime("+10 days")), true);
		header("Pragma: public", true);
		header("Cache-Control: public", true);
		header("Content-Length: " . strlen($contents));
		header("Content-type: {$content_type}", true);
		echo $contents;
		exit;
	}
}

/**
 * Load video via ajax
 * 
 * @param integer  $guid
 * 
 * @version 5.0
 */
function izap_get_video_player($guid, $height, $width) {
	global $IZAPSETTINGS;
	$entity = get_entity($guid);
	$video_src = elgg_get_site_url() . 'izap_videos_files/file/' . $guid . '/' . elgg_get_friendly_title($entity->title) . '.flv';
	$player_path = $IZAPSETTINGS->playerPath;
	if ($entity->videourl) {
		if (elgg_instanceof($entity, 'object', GLOBAL_IZAP_VIDEOS_SUBTYPE, GLOBAL_IZAP_VIDEOS_CLASS)) {
			preg_match("/((http)s?)/", elgg_get_site_url(), $scheme);
			$current_scheme = $scheme[1];
			$pattern = '/(.* src\s?=\s?\")(http[s]?)(:\/\/.*)/';
			$replacement = '${1}' . $current_scheme . '$3';
			$source = preg_replace($pattern, $replacement, $entity->videosrc);
			$content = izap_get_replaced_height_width_izap_videos($height, $width, $source);
		} else {
			echo elgg_echo('izap_videos:ajaxed_videos:error_loading_video');
		}
	} else {
		if ($entity->converted == 'yes') {
			$content = "
           <object width='" . $width . "' height= '" . $height . "' id='flvPlayer'>
            <param name='allowFullScreen' value='true'>
            <param name='wmode' value='transparent'>
            <param name='allowScriptAccess' value='always'>
            <param name='movie' value='" . $player_path . "?movie=" . $video_src . "&volume=30&autoload=on&autoplay=on&vTitle=" . $entity->title . "&showTitle=yes' >
            <embed src='" . $player_path . "?movie=" . $video_src . "&volume=30&autoload=on&autoplay=on&vTitle=" . $entity->title . "&showTitle=yes' width='100' height='100' allowFullScreen='true' type='application/x-shockwave-flash' allowScriptAccess='always' wmode='transparent'>
           </object>";
		} else {
			$content = izap_add_error($entity->guid);
		}
	}
	echo $content;
	exit;
}

/*
 * Get Offserver Api Key
 * 
 * @version 5.0
 */

//function get_offserver_api_key() {
//	return elgg_get_plugin_setting('izap_api_key', 'izap-videos');
//}

/**
 * Get detail for YouTube video 
 * 
 * @global array  $IZAPSETTINGS
 * @param array   $video_data
 * @param array   $video_object
 * 
 * @return array  array of video object
 * 
 * @version 5.0
 */
function input($video_data = array(), &$video_object) {
	global $IZAPSETTINGS;
	$url = $IZAPSETTINGS->apiUrl . '&url=' . urlencode($video_data['url']);
	$curl = new IzapCurl();
	$raw_contents = $curl->get($url)->body;
	$returnObject = json_decode($raw_contents);
	if ($returnObject == NULL || $returnObject == FALSE) {
		register_error(elgg_echo('izap_videos:no_response_from_server'));
		forward($_SERVER['HTTP_REFERER']);
		exit;
	}
	// We are not supporting this url.
	if (!$returnObject || empty($returnObject->embed_code)) {
		return $returnObject;
	}
	$video_object->title = $video_data['title'] ? $video_data['title'] : $returnObject->title;
	$video_object->description = $video_data['description'] ? $video_data['description'] : $returnObject->description;
	$video_object->videothumbnail = $returnObject->thumb_url;
	$video_object->videosrc = $returnObject->embed_code;
//    $video_object->tags = $tags;
//	$video_object->domain = $returnObject->url;
	$video_object->video_type = $returnObject->type;
}

/**
 * Replace height and width for youtube videos
 * 
 * @param integer  $newHeight
 * @param integer  $newWidth
 * @param array    $object
 * 
 * @return array   array of video div with proper height and width
 * 
 * @version 5.0
 */
function izap_get_replaced_height_width_izap_videos($newHeight, $newWidth, $object) {
	$videodiv = preg_replace('/width=["\']\d+["\']/', 'width="' . $newWidth . '"', $object);
	$videodiv = preg_replace('/width:\d+/', 'width:' . $newWidth, $videodiv);
	$videodiv = preg_replace('/height=["\']\d+["\']/', 'height="' . $newHeight . '"', $videodiv);
	$videodiv = preg_replace('/height:\d+/', 'height:' . $newHeight, $videodiv);
	return $videodiv;
}

/**
 * Increment the views when user visits the page
 * 
 * @param elggEntity $entity
 * 
 * @version 5.0
 */
function izap_increase_views($entity) {
	if (is_object($entity)) {
		$entity->total_views++;
	}
}

/**
 * Return the total number of views of the entity
 * 
 * @param elggEntity $entity
 * 
 * @return integer  integer of total views for vedio
 * 
 * @version 5.0
 */
function izap_get_total_views($entity) {
	return (int) $entity->total_views;
}

/**
 * Get categories that are supported by YouTube
 * 
 * @return array  array of all categories
 * 
 * @version 5.0
 */
function izap_get_youtube_categories() {
	$cats = array(
		'Film' => 'Film & Animation',
		'Autos' => 'Autos',
		'Music' => 'Music',
		'Animals' => 'Pets & Animals',
		'Sports' => 'Sports',
		'Travel' => 'Travel & Events',
		'Games' => 'Gaming',
		'Comedy' => 'Comedy',
		'Entertainment' => 'Entertainment',
		'News' => 'News & Politics',
		'Howto' => 'Howto & Style',
		'Education' => 'Education',
		'Tech' => 'Science & Technology',
		'Nonprofit' => 'Nonprofits & Activism',
		'Movies' => 'Movies',
		'Movies_anime_animation' => 'Anime/Animation',
		'Movies_classics' => 'Classics',
		'Movies_comedy' => 'Comedy Movies',
		'Movies_documentary' => 'Documentary',
		'Movies_drama' => 'Drama',
		'Movies_family' => 'Family',
		'Movies_foreign' => 'Foreign',
		'Movies_horror' => 'Horror',
		'Movies_sci_fi_fantasy' => 'Sci-Fi/Fantasy',
		'Movies_thriller' => 'Thriller',
		'Movies_shorts' => 'Shorts',
		'Shows' => 'Shows',
		'Trailers' => 'Trailers');
	asort($cats);
	return $cats;
}

/**
 * Get YouTube video detail for offserver preview 
 * 
 * @version 5.0
 */
function izap_preview() {
	$video_url = array(
		'url' => $_POST['url']
	);
	$izap_video = new IzapVideo();
	$izap_video->saveYouTubeVideoData($video_url);
	$tags = implode(',', $izap_video->tags);
	$video_data = array(
		'title' => $izap_video->title,
		'description' => $izap_video->description,
		'thumbnail' => $izap_video->videothumbnail,
		'tags' => $tags
	);
	echo json_encode($video_data);
	exit;
}

/**
 * Save offserver video after getting responese from YouTube
 * 
 * @version 5.0
 */
function izap_youtube_response() {
	$id = get_input('id');
	$url = 'https://www.youtube.com/watch?v=' . $id;
	$video_data = array(
		'url' => $url
	);
	$izap_video = new IzapVideo();
	if ($izap_video->guid == 0) {
		$new = true;
	}
	$izap_video->videourl = $url;
	$izap_video->saveYouTubeVideoData($video_data);
	if ($izap_video->save()) {
		if ($new == true) {
			if (is_callable('elgg_create_river_item')) {
				elgg_create_river_item(array(
					'view' => 'river/object/izap_video/create',
					'action_type' => 'create',
					'subject_guid' => elgg_get_logged_in_user_guid(),
					'object_guid' => $izap_video->getGUID(),
				));
			} else {
				add_to_river('river/object/izap_video/create', 'create', elgg_get_logged_in_user_guid(), $izap_video->getGUID());
			}
		}
		elgg_clear_sticky_form('izap_videos');
		system_messages(elgg_echo('izap-videos:Save:success'));
		forward($izap_video->getURL($izap_video->getOwnerEntity(), GLOBAL_IZAP_VIDEOS_PAGEHANDLER));
	}
}

/**
 * Set Hyperlink
 * 
 * @global array  $CONFIG
 * @param  array  $input
 * 
 * @return string string of hyper reference url
 * 
 * @version 5.0
 */
function izap_set_href($input = array()) {
	global $CONFIG;
	// Default Params
	$default = array(
		'trailing_slash' => TRUE,
		'full_url' => TRUE,
	);
	$params = array_merge($default, $input);
	// start url array
	$url_array = array();
	if ($params['context']) {
		$url_array[] = $params['context'];
	} else {
		$url_array[] = elgg_get_context();
	}
	// set which page to call
	$url_array[] = $params['action'];
	// check to set the page owner
	if ($params['page_owner'] !== FALSE) {
		if (isset($params['page_owner'])) {
			$url_array[] = $params['page_owner'];
		} elseif (elgg_get_page_owner_entity()) {
			$url_array[] = elgg_get_page_owner_entity()->guid;
		} elseif (elgg_is_logged_in()) {
			$url_array[] = elgg_get_logged_in_user_guid();
		}
	}
	if (is_array($params['vars']) && sizeof($params['vars'])) {
		foreach ($params['vars'] as $var) {
			$url_array[] = filter_var($var);
		}
	}
	// short circuit for empty values
	foreach ($url_array as $value) {
		if (!empty($value)) {
			$final_array[] = $value;
		}
	}
	// create URL
	$final_url = implode('/', $final_array);
	if ($params['full_url']) {
		$final_url = $CONFIG->wwwroot . $final_url;
	}
	return $final_url;
}

/**
 * Get All Access
 * 
 * @param string  $func_name
 * @param integer $priority
 * 
 * @version 5.0
 */
function izap_get_all_access($func_name = 'izap_access_over_ride', $priority = 99999) {
	elgg_set_ignore_access(true);
	elgg_register_event_handler("enable", "all", $func_name, $priority);
	elgg_register_plugin_hook_handler("permissions_check", "all", $func_name, $priority);
	elgg_register_plugin_hook_handler("container_permissions_check", "all", $func_name, $priority);
	elgg_register_plugin_hook_handler("permissions_check:metadata", "all", $func_name, $priority);
}

/**
 * Return not converted videos
 * 
 * @return array  array of not converted videos
 * 
 * @version 5.0
 */
function izap_get_failed_videos() {
	$records = elgg_get_entities_from_metadata(array(
		'types' => 'object',
		'subtypes' => GLOBAL_IZAP_VIDEOS_SUBTYPE,
		'metadata_names' => 'converted',
		'metadata_values' => 'no',
		'limit' => 99999999
	));
	return $records;
}

/**
 * Check video converted succesfully or not
 * 
 * @param integer $guid
 * 
 * @return string string of video converted status 
 * 
 * @version 5.0
 */
function izap_check_video_status($guid) {
	$video = get_entity($guid);
	echo $video->converted;
	exit;
}

/**
 * Create error div for videos
 * 
 * @param integer  $guid
 * 
 * @return string|boolean  string of error div
 * 
 */
function izap_add_error($guid) {
	$video = get_entity($guid);
	if ($video->converted == 'in_processing') {
		$error = '<p class="notConvertedWrapper" style="background-color: #FFC4C4;width:92%;margin-top: -3px;border-radius:3px;">' . elgg_echo("izap_videos:alert:not-converted") . '</p>';
	} elseif ($video->converted === 'no') {
		$error = '<p class="notConvertedWrapper" style="background-color: #FFC4C4;width:92%;margin-top: -3px;border-radius:3px;">' . elgg_echo("izap_videos:alert:fail-converted") . '</p>';
	} else {
		return False;
	}
	return $error;
}

function izap_gatekeeper() {
	if (is_callable('elgg_gatekeeper')) {
		return elgg_gatekeeper();
	} else {
		return gatekeeper();
	}
}

function izap_group_gatekeeper() {
	if (is_callable('elgg_group_gatekeeper')) {
		return elgg_group_gatekeeper();
	} else {
		return group_gatekeeper();
	}
}
