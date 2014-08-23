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

class IzapVideo extends ElggFile {

    protected $format = '.flv';

    protected function initializeAttributes() {
        parent::initializeAttributes();

        $this->attributes['subtype'] = "izap_video";
    }

    public function __construct($guid = NULL) {
        parent::__construct($guid);
        // set some initial values so that old videos can work
    }

    /**
     * set tmp path for upload video
     */
    public function get_tmp_path($video_name) {
        $setFileName = 'izap_videos/tmp/' . $video_name;
        return $setFileName;
    }

    /**
     * 
     * @param type $file_path input path for ffmpeg processing
     */
    public function processOnserverVideo($source_path, $dest_path) {
        $returnvalue = new stdClass();

        $destination_path = $dest_path . time() . $this->format;
        $file_name = end(explode('/', $destination_path));
        $source_file = end(explode('/', $source_path));

        //tmp file
        $this->setFilename($this->get_tmp_path($source_file));
        $this->open('write');
        $this->write(file_get_contents($source_path));
        $this->tmpfile = $this->getFilenameOnFilestore();
  
    }

}
