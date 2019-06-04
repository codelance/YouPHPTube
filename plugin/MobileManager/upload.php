<?php
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$object = new stdClass();
$object->error = true;
$object->videos_id = 0;
if (!User::canUpload() && (empty($_GET['user']) || empty($_GET['pass']))) {
    $object->msg = "You need a user";
    die(json_encode($object));
}

$user = $_GET['user'];
$password = $_GET['pass'];

$userObj = new User(0, $user, $password);
$userObj->login(false, true);

if (!User::canUpload()) {
    $object->msg = "You can not upload";
    die(json_encode($object));
}

// A list of permitted file extensions
$allowed = array('mp4', 'avi', 'mov', 'mkv', 'flv', 'mp3', 'wav', 'm4v', 'webm', 'wmv', 'mpg', 'mpeg', 'f4v', 'm4v', 'm4a', 'm2p', 'rm', 'vob', 'mkv');
error_log("MOBILE UPLOAD: Starts");
if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {

    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

    if (!in_array(strtolower($extension), $allowed)) {
        $object->msg = "File extension error (" . $_FILES['upl']['name'] . "), we allow only (" . implode(",", $allowed) . ")";
        error_log("MOBILE UPLOAD: {$object->msg}");
        die(json_encode($object));
    }
    //chack if is an audio
    $type = "video";
    if (strcasecmp($extension, 'mp3') == 0 || strcasecmp($extension, 'wav') == 0) {
        $type = 'audio';
    }

    require_once $global['systemRootPath'] . 'objects/video.php';
    $duration = Video::getDurationFromFile($_FILES['upl']['tmp_name']);

    // check if can upload video (about time limit storage)
    if (!empty($global['videoStorageLimitMinutes'])) {
        $maxDuration = $global['videoStorageLimitMinutes'] * 60;
        $currentStorageUsage = getSecondsTotalVideosLength();
        $thisFile = parseDurationToSeconds($duration);
        $limitAfterThisFile = $currentStorageUsage + $thisFile;
        if ($maxDuration < $limitAfterThisFile) {
            $object->msg = "Sorry, your storage limit has run out."
                    . "<br>[Max Duration: {$maxDuration} Seconds]"
                    . "<br>[Current Srotage Usage: {$currentStorageUsage} Seconds]"
                    . "<br>[This File Duration: {$thisFile} Seconds]"
                    . "<br>[Limit after this file: {$limitAfterThisFile} Seconds]";


            if (!empty($_FILES['upl']['videoId'])) {
                $video = new Video("", "", $_FILES['upl']['videoId']);
                $video->delete();
            }
            error_log("MOBILE UPLOAD: {$object->msg}");
            die(json_encode($object));
        }
    }

    $mainName = preg_replace("/[^A-Za-z0-9]/", "", cleanString($_FILES['upl']['name']));
    $filename = uniqid($mainName . "_YPTuniqid_", true);

    $video = new Video(preg_replace("/_+/", " ", $_FILES['upl']['name']), $filename, 0);
    $video->setDuration($duration);
    if ($type == 'audio') {
        $video->setType($type);
    } else {
        $video->setType("video");
    }
    
    if(!empty($_REQUEST['title'])){
        error_log("Setting Video Title: ".  $_REQUEST['title']);
        $video->setTitle($_REQUEST['title']);
    }

    if(!empty($_REQUEST['description'])){
        error_log("Setting Video Description: ".  $_REQUEST['description']);
        $video->setDescription($_REQUEST['description']);
    }

    if(!empty($_REQUEST['categories_id'])){
        error_log("Setting Video Category: ".  $_REQUEST['categories_id']);
        $video->setCategories_id($_REQUEST['categories_id']);
    }

    if(!empty($_REQUEST['next_video_id'])){
        error_log("Setting Video Next: ".  $_REQUEST['next_video_id']);
        $video->setNext_videos_id($_REQUEST['next_video_id']);
    }

    if(!empty($_REQUEST['mastered'])){
        error_log("Setting Video Mastered: ".  $_REQUEST['mastered']);
        $video->setMasteredEnabled();
    }
    
    //$video->setStatus('e');
    $video->setStatus('a');
    //if (!move_uploaded_file($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_" . $filename)) {
    //    $object->msg = "Error on move_uploaded_file(" . $_FILES['upl']['tmp_name'] . ", " . "{$global['systemRootPath']}videos/original_" . $filename . ")";
    //    error_log("MOBILE UPLOAD ERROR: ".  json_encode($object));
    //    die(json_encode($object));
    //}
    $object->videos_id = $video->save();
    if ($object->videos_id) {
        /**
         * This is when is using in a non uploaded movie
         */
        $aws_s3 = YouPHPTubePlugin::loadPluginIfEnabled('AWS_S3');
        $tmp_name = $_FILES['upl']['tmp_name'];
        $filenameMP4 = $filename . "." . $extension;

        $destinationVideoPath = "{$global['systemRootPath']}videos/{$filenameMP4}";
        if (!move_uploaded_file($tmp_name, $destinationVideoPath)) {
            $object->msg = print_r(sprintf(__("Could not move video file [%s]"), $destinationVideoPath), true);
            die(json_encode($object));
        }
        decideMoveUploadedToVideos($destinationVideoPath, $filenameMP4);

        if ((YouPHPTubePlugin::isEnabled("996c9afb-b90e-40ca-90cb-934856180bb9")) && ($extension == "mp4" || $extension == "webm")) {
            require_once $global['systemRootPath'] . 'plugin/MP4ThumbsAndGif/MP4ThumbsAndGif.php';

            $videoFileName = $video->getFilename();
            if(MP4ThumbsAndGif::getImage($videoFileName, 'jpg')){
                $sourceImagePath = "{$global['systemRootPath']}videos/{$videoFileName}.jpg";

                decideMoveUploadedToVideos($sourceImagePath, $filename . ".jpg");
            }

            if(MP4ThumbsAndGif::getImage($videoFileName, 'gif')) {
                $sourceImagePath = "{$global['systemRootPath']}videos/{$videoFileName}.gif";
                decideMoveUploadedToVideos($sourceImagePath, $filename . ".gif");
            }
        }

        $object->error = false;

        if(!empty($_FILES['upl']['tmp_name'])){
            YouPHPTubePlugin::afterNewVideo($object->videos_id);
        }
    }

    //$video->queue();

    //$object->error = false;
    //$object->msg = "We sent your video to the encoder";
    error_log("MOBILE SUCCESS UPLOAD: ".  json_encode($object));
    die(json_encode($object));
} else {
    error_log("MOBILE UPLOAD: File Not exists - " . json_encode($_FILES));
}
