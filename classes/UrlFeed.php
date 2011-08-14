<?php
/**
 * iZAP izap_videos
 *
 * @package Elgg videotizer, by iZAP Web Solutions.
 * @license GNU Public License version 3
 * @Contact iZAP Team "<support@izap.in>"
 * @Founder Tarun Jangra "<tarun@izap.in>"
 * @link http://www.izap.in/
 * 
 */

class UrlFeed {
  //check out if the site is supported.
  function setUrl($url = '') {
    return $this->capture($url);
  }
  function capture($url) {
    global $IZAPSETTINGS;
    $url = $IZAPSETTINGS->apiUrl . '&url=' .  urlencode($url);
    $curl = new IzapCurl($url);
    $raw_contents = $curl->exec();
    
    $returnObject = json_decode($raw_contents);
    if($returnObject == NULL || $returnObject == FALSE) {
      register_error(elgg_echo('izap_videos:no_response_from_server'));
      forward($_SERVER['HTTP_REFERER']);
      exit;
    }
    // We are not supporting this url.
    if(!$returnObject || empty($returnObject->embed_code)) {
      return $returnObject;
    }

    $obj= new stdClass;
    $obj->title = $returnObject->title;
    $obj->description = $returnObject->description;
    $obj->videoThumbnail = $returnObject->thumb_url;
    $obj->videoSrc = $returnObject->embed_code;
    $obj->videoTags = $returnObject->tags;
    $obj->domain = $returnObject->url;
    $obj->fileName = time().'_'.basename($obj->videoThumbnail);
    $obj->fileContent = file_get_contents($obj->videoThumbnail);
    $obj->type = $returnObject->type;
    return $obj;
  }

}