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
 * Convert videos
 * 
 * @version 5.0
 */
elgg_load_library('elgg:izap_video');

class izapConvert {

	private $invideo;
	private $outvideo;
	private $outimage;
	public $format = 'flv';
	private $values = array();

	/**
	 * @param string  $in
	 * 
	 * @version 5.0
	 */
	public function izapConvert($in = '') {
		$this->invideo = $in;
		$extension_length = strlen(izap_get_file_extension($this->invideo));
		$outputPath = substr($this->invideo, 0, '-' . ($extension_length + 1));
		$this->outvideo = $outputPath . '_c.' . $this->format;
		$this->outimage = $outputPath . '_i.png';
	}

	/**
	 * @return array array of converted video if there is no error, else array of errors
	 * 
	 * @version 5.0
	 */
	public function izap_video_convert() {
		if (IZAP_VIDEO_UNIT_TEST === True) {
			$videoCommand = exec("which ffmpeg") . ' -y -i [inputVideoPath] [outputVideoPath]';
		} else {
			$videoCommand = izap_get_ffmpeg_videoConvertCommand_izap_videos();
		}
		$videoCommand = str_replace('[inputVideoPath]', $this->invideo, $videoCommand);
		$videoCommand = str_replace('[outputVideoPath]', $this->outvideo, $videoCommand);
		$videoCommand = $videoCommand . ' 2>&1';
		exec($videoCommand, $out, $err);

		// if file not converted successfully return error message 
		if ($err != 0) {
			$return = array();
			$return['error'] = 1;
			$return['message'] = end($out);
			$return['completeMessage'] = implode(' ', $out);
			return $return;
		}
		$out_video = explode('/', $this->outvideo);
		return end($out_video);
	}

	/**
	 * Create thumbnail from video
	 * 
	 * @return array array of thumbnail
	 * 
	 * @version 5.0
	 */
	public function get_thumbnail_from_video() {
		if (IZAP_VIDEO_UNIT_TEST === True) {
			$thumbnail_cmd = exec("which ffmpeg") . ' -y -i [inputVideoPath] -vframes 1 -ss 00:00:10 -an -vcodec png -f rawvideo -s 320x240 [outputImage]';
		} else {
			$thumbnail_cmd = izap_get_ffmpeg_thumbnailCommand();
		}

		$thumbnail_cmd = str_replace('[inputVideoPath]', $this->invideo, $thumbnail_cmd);
		$thumbnail_cmd = str_replace('[outputImage]', $this->outimage, $thumbnail_cmd);
		$thumbnail_cmd = $thumbnail_cmd . ' 2>&1';
		exec($thumbnail_cmd, $out, $err);

		if ($err != 0) {
			$return = array();
			$return['error'] = 1;
			$return['message'] = end($out);
			return $return;
		}
		return $this->outimage;
	}

	/**
	 * Return created thumbnail component
	 * 
	 * @return array array of thumbnail component
	 * 
	 * @version 5.0
	 */
	public function getValues() {
		$outimg = explode('/', $this->outimage);
		$this->values['imagename'] = end($outimg);
		$this->values['imagecontent'] = file_get_contents($this->outimage);
		return $this->values;
	}

}
