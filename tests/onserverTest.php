<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of onserverTest
 *
 * @author monika
 */
class onserverTest extends PHPUnit_Framework_TestCase {

	function testOnserver() {
		$source_path = dirname(__FILE__) . '/test_video.avi';
		$file = array(
			'name' => 'test_video.avi', 
			'tmp_name' => $source_path, 
			'size' => '309042', 
			'error' => '0', 
			'type' => 'video/x-msvideo'
			);

//		$izap_video = new IzapVideo();
//		$tags = "offserver,video";
//		$izap_video->subtype = GLOBAL_IZAP_VIDEOS_SUBTYPE;
//		$izap_video->title = 'title : title';
//		$izap_video->description = 'description : description';
//		$izap_video->owner_guid = 77;
//		$izap_video->access_id = 2;
//		$izap_video->videotype = 'video/x-msvideo';

//		$processed_data = $izap_video->processfile($file);
	}

}
