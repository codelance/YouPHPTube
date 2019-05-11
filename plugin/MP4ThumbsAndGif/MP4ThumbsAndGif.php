<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class MP4ThumbsAndGif extends PluginAbstract {

    public function getDescription() {
        return "Request to the encoder, an poster image and an animated gif from the Uploaded MP4 File";
    }

    public function getName() {
        return "MP4ThumbsAndGif";
    }

    public function getUUID() {
        return "996c9afb-b90e-40ca-90cb-934856180bb9";
    }
    
    static function getImage($videoFileName, $type){
        global $global, $config;
        error_log("MP4ThumbsAndGif: ($videoFileName), $type");
        require_once $global['systemRootPath'] . 'objects/video.php';
        $destination = "{$global['systemRootPath']}videos/{$videoFileName}.{$type}";
        $videosURL = static::getFirstVideoURL($videoFileName);
        $videoPath = static::getFirstVideoPath($videoFileName);
        $duration = (Video::getItemDurationSeconds(Video::getDurationFromFile($videoPath))/2);
        if (!empty($videosURL)) {
            $url = $videosURL;
            $file_headers = @get_headers($url);
            if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                error_log("MP4ThumbsAndGif: Error on get jpeg poster: Video URL does not exists {$url}");
                return false;
            } else {
                $image = file_get_contents($config->getEncoderURL() . "getImageMP4/" . base64_encode($url) . "/{$type}/{$duration}");
                file_put_contents($destination, $image);
            }
        }else{
            error_log("MP4ThumbsAndGif: Video Not found ($videoFileName), $type");
            return false;
        }
        return true;
    }


    static private function getFirstVideoURL($videoFileName) {
        $types = array('', '_Low', '_SD', '_HD');
        $videosList = getVideosURL($videoFileName);
        foreach ($types as $value) {
            if (!empty($videosList['mp4' . $value]["url"])) {
                return $videosList['mp4' . $value]["url"];
            } else if (!empty($videosList['webm' . $value]["url"])) {
                return $videosList['webm' . $value]["url"];
            }
        }
        return false;
    }

    static private function getFirstVideoPath($videoFileName) {
        $types = array('', '_Low', '_SD', '_HD');
        $videosList = getVideosURL($videoFileName);
        foreach ($types as $value) {
            if (!empty($videosList['mp4' . $value]["path"])) {
                return $videosList['mp4' . $value]["path"];
            } else if (!empty($videosList['webm' . $value]["path"])) {
                return $videosList['webm' . $value]["path"];
            }
        }
        return false;
    }
    
    public function getHeadCode(){
        global $global;
        $baseName = basename($_SERVER['REQUEST_URI']);
        $js = "";
        if($baseName === 'mvideos'){
            $js .= "<script>function mp4ThumbsAndGif(video_id){
                                    modal.showPleaseWait();
                                    \$.ajax({
                                        url: '{$global['webSiteRootURL']}plugin/MP4ThumbsAndGif/get.php',
                                        data: {\"video_id\": video_id, \"type\": \"jpg\"},
                                        type: 'post',
                                        success: function (response) {
                                            \$.ajax({
                                                url: '{$global['webSiteRootURL']}plugin/MP4ThumbsAndGif/get.php',
                                                data: {\"video_id\": video_id, \"type\": \"gif\"},
                                                type: 'post',
                                                success: function (response) {
                                                    console.log(response);
                                                    modal.hidePleaseWait();
                                                }
                                            });
                                        }
                                    });}</script>";
        }
        return $js;
    }
    
    public function getVideosManagerListButton(){
        $btn = '<br><button type="button" class="btn btn-default btn-light btn-sm btn-xs " onclick="mp4ThumbsAndGif(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="Extract images from your video"><i class="far fa-images"></i> Get Jpeg/Gif</button>';
        return $btn;
    }

}
