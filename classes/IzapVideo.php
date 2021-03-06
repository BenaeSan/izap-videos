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
 * Saveing and conversion process for videos 
 * 
 * @version 5.0
 */
class IzapVideo extends ElggFile {

  protected $format = '.flv';

  protected function initializeAttributes() {
    parent::initializeAttributes();

    $this->attributes['subtype'] = 'izap_video';
  }

  /**
   * 
   * @param integer  $guid
   * 
   * @version 5.0
   */
  public function __construct($guid = NULL) {
    parent::__construct($guid);
    // set some initial values so that old videos can work
  }

  /**
   * Return tmp path for upload video
   * 
   * @param string  $name
   * 
   * @return string
   * 
   * @version 5.0
   */
  public function getTmpPath($name) {
    $setFileName = 'izap_videos/tmp/' . $name;
    return $setFileName;
  }

  /**
   * Save Video
   * 
   * @param array  $data
   * 
   * @return boolean
   * 
   * @version 5.0
   * 
   * @todo saveVideo function should be more flaxible for unit tests
   */
  public function saveVideo($data = array(), $new) {
    foreach ($data as $key => $value) {
      if ($value != '') {
        $this->$key = $value;
      }
    }
    if (($new) && ($this->videoprocess == 'offserver' || $this->videoprocess == 'onserver' || $this->videoprocess == 'youtube')) {
      switch ($this->videoprocess) {
        case 'offserver':
          include_once (dirname(dirname(__FILE__)) . '/actions/izap-videos/offserver.php');
          $saved = $this;
          break;
        case 'youtube':
          include_once (dirname(dirname(__FILE__)) . '/actions/izap-videos/youtube.php');
          forward(REFERRER);
          break;
        case 'onserver':
          include_once (dirname(dirname(__FILE__)) . '/actions/izap-videos/onserver.php');
          //before start converting
          $this->converted = 'no';
          if ($saved = $this->save()) {
            $get_guid = $this->getGUID();
            $get_entity = get_entity($get_guid);
            if (file_exists($get_entity->videofile)) {
              $this->videosrc = elgg_get_site_url() . 'izap_videos_files/file/' . $get_entity->guid . '/' . elgg_get_friendly_title($get_entity->title) . '.flv';
              if (izap_get_file_extension($get_entity->videofile) != 'flv') {
                izap_save_fileinfo_for_converting_izap_videos($get_entity->videofile, $get_entity, $get_entity->access_id, $this);
              } elseif (izap_get_file_extension($get_entity->videofile) == 'flv') {
                $this->converted = 'yes';
              }
              //change access id to submit by user after converting video
              $this->access_id = $data['access_id'];
              $saved = $this;
            }
          }
          break;
      }
    } else {
      $saved = $this;
    }
    return $saved;
  }

  /**
   * Process upload file
   * 
   * @param array  $file
   * 
   * @return mixed int if any error occured else array
   * 
   * @version 5.0
   */
  public function processFile($file) {
    $returnvalue = new stdClass();

    $filename = strtolower(str_replace(' ', '_', $file['name']));
    $tmpname = $file['tmp_name'];
    $file_err = $file['error'];
    $file_type = $file['type'];
    $file_size = $file['size'];

    if ($file_err > 0) {
      return 104;
    }

    // if file is of zero size
    if ($file_size == 0) {
      return 105;
    }
    $returnvalue->videotype = $file_type;
    $set_video_name = $this->getTmpPath(time() . $filename);
    $this->setFilename($set_video_name);
    $this->open("write");
    $this->write(file_get_contents($tmpname));
    $returnvalue->videofile = $this->getFilenameOnFilestore();

    // take snapshot from video
    if (IZAP_VIDEO_UNIT_TEST === True) {
      global $CONFIG;
      $returnvalue->videofile = $CONFIG->dataroot . 'test_video.avi';
      $image = new izapConvert($returnvalue->videofile);
    } else {
      $image = new izapConvert($returnvalue->videofile);
    }

    if ($image->get_thumbnail_from_video()) {
      $retValues = $image->getValues(TRUE);
      if ($retValues['imagename'] != '' && $retValues['imagecontent'] != '') {
        $set_original_thumbnail = $this->getTmpPath('original_' . $retValues['imagename']);
        $this->setFilename($set_original_thumbnail);
        $this->open("write");
        if ($this->write($retValues['imagecontent'])) {
          if (IZAP_VIDEO_UNIT_TEST === True) {
            $returnvalue->orignal_thumb = $CONFIG->dataroot . $retValues['imagename'];
          } elseif ($this->write($retValues['imagecontent'])) {
            $orignal_file_path = $this->getFilenameOnFilestore();
            $thumb = get_resized_image_from_existing_file($orignal_file_path, 650, 500);
            $set_thumb = $this->getTmpPath($retValues['imagename']);
            $this->setFilename($set_thumb);
            $this->open("write");
            $this->write($thumb);

            // $this->close();
            $returnvalue->orignal_thumb = $set_original_thumbnail;
            $returnvalue->thumb = $set_thumb;
          }
        }
      }
    }
    return $returnvalue;
  }

  /**
   * Return path for full page video play
   * 
   * @return string
   * 
   * @version 5.0
   */
  public function getURL($owner = null, $handler = 'videos') {
    if(!$owner){
      $owner = $this->getOwnerEntity();
    }
    return elgg_get_site_url() . $handler . '/play/' . $owner->username . '/' . $this->guid . '/' . elgg_get_friendly_title($this->title);
  }

  /**
   * Save YouTube video in database
   * 
   * @param array  $url
   * 
   * @version 5.0
   */
  public function saveYouTubeVideoData($url) {
    $videoValues = input($url, $this);
    $this->converted = 'yes';
    if ($_SESSION['youtube_attributes']) {
      if($_SESSION['youtube_attributes']['container_guid']){
        $this->container_guid = $_SESSION['youtube_attributes']['container_guid'];
      }
      if($_SESSION['youtube_attributes']['access_id']){
        $this->access_id = $_SESSION['youtube_attributes']['access_id'];
      }
      if($_SESSION['youtube_attributes']['tags']){
        $this->tags = $_SESSION['youtube_attributes']['tags'];
      }
      unset($_SESSION['youtube_attributes']);
    }
  }

  /**
   * Validate title
   * 
   * @param string $title
   * 
   * @version 5.0
   */
  public function checkTitle($title) {
    if (empty($title)) {
      throw new IzapVideoException('Please enter the title');
    }
  }

  /**
   * Validate url
   * 
   * @param string $url
   * 
   * @version 5.0
   */
  public function checkUrl($url) {
    if (empty($url)) {
      throw new IzapVideoException('Please enter the video url');
    }
  }

  /**
   * Validate uploaded file
   * 
   * @param file $file
   * 
   * @version 5.0
   */
  public function checkFile($file) {
    if ($file['size'] == 0) {
      throw new IzapVideoException('Please select a valid video to upload');
    }
  }

}
