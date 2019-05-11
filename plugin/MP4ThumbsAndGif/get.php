<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/MP4ThumbsAndGif/MP4ThumbsAndGif.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
if (!User::canUpload()) {
    die('{"error":"'.__("Permission denied").'"}');
}
if(empty($_POST['video_id'])){    
    die('{"error":"Video Not found"}');
}
if(empty($_POST['type'])){    
    $type = 'jpg';
}else{   
    $type = $_POST['type'];
}
$video_id = $_POST['video_id'];
$video = new Video("", "", $video_id);
echo MP4ThumbsAndGif::getImage($video->getFilename(), $type);