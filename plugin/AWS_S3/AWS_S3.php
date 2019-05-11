<?php

use Aws\S3\S3Client;
use Aws\S3\BatchDelete;

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
// they are not compatible
if (!class_exists('Blackblaze_B2')) {
    require_once $global['systemRootPath'] . 'plugin/AWS_S3/aws/aws-autoloader.php';
}

class AWS_S3 extends PluginAbstract {

    public function getDescription() {
        return "Amazon S3 Secure, Durable & Highly-Scalable Object Storage";
    }

    public function getName() {
        return "AWS_S3";
    }

    public function getUUID() {
        return "1ddecbec-91db-4357-bb10-ee08b0913778";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->region = 'us-west-2';
        $obj->bucket_name = "youphptube";
        $obj->key = "";
        $obj->secret = "";
        $obj->endpoint = "";
        $obj->profile = "";
        $obj->useS3DirectLink = true;
        $obj->presignedRequestSecondsTimeout = 43200; //12 hours
        $obj->CDN_Link = "";
        $obj->makeMyFilesPublicRead = false;

        return $obj;
    }

    private function getS3ClientParameters() {
        $obj = $this->getDataObject();
        $parameters = array();
        $parameters['version'] = 'latest';
        $parameters['region'] = $obj->region;
        $parameters['credentials'] = array('key' => $obj->key, 'secret' => $obj->secret);

        if (!empty($obj->endpoint)) {
            $parameters['endpoint'] = $obj->endpoint;
        }

        if (!empty($obj->profile)) {
            $parameters['profile'] = $obj->profile;
        }

        return $parameters;
    }

    public function xsendfilePreVideoPlay() {
        global $global;

        $path_parts = pathinfo($_GET['file']);
        $filename = $path_parts['filename'];
        preg_match("/(.*)(_(SD|HD|Low|.mp3))/i", $path_parts['filename'], $matches);
        if (!empty($matches[1])) {
            $filename = $matches[1];
        }
        //var_dump($filename, $path_parts);exit;
        //$videosExtensions = array('mp4', 'webm', 'jpg', 'gif');
        $videosExtensions = array('mp4', 'webm', 'mp3');
        if (in_array(strtolower($path_parts['extension']), $videosExtensions)) {
            $localFile = $global['systemRootPath'] . 'videos/' . $path_parts['basename'];
            if (!file_exists($localFile) || filesize($localFile) < 1024) {
                $obj = $this->getDataObject();
                $s3File = $this->getURL($path_parts["basename"]);
                header("Location: {$s3File}");
                exit;
            }
        }
    }

    public function getAddress($filename) {
        global $global;
        require_once $global['systemRootPath'] . 'objects/video.php';
        $obj = $this->getDataObject();
        $dir = Video::getStoragePath();
        $address = array('path' => "{$dir}{$filename}", 'url' => $this->getURL($filename));
        return $address;
    }

    public function getURL($filename) {
        $obj = $this->getDataObject();
        if (empty($obj->CDN_Link)) {
            $s3Client = new S3Client($this->getS3ClientParameters());
            if (!$obj->makeMyFilesPublicRead) {
                $cmd = $s3Client->getCommand('GetObject', [
                    'Bucket' => $obj->bucket_name,
                    'Key' => $filename
                ]);

                $request = $s3Client->createPresignedRequest($cmd, "+{$obj->presignedRequestSecondsTimeout} seconds");

                // Get the actual presigned-url
                return (string) $request->getUri();
            } else {
                return $s3Client->getObjectUrl($obj->bucket_name, $filename);
            }
        } else {
            return $obj->CDN_Link . $filename;
        }
    }

    public function removeFiles($filename) {
        if (empty($filename)) {
            return false;
        }
        $file = "original_{$filename}";
        $this->removeFilePath($file);

        $files = "{$filename}";
        $this->removeFilePath($files);
    }

    private function removeFilePath($fileName) {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 3000); // 60 minutes
        global $global;
        require_once $global['systemRootPath'] . 'plugin/AWS_S3/aws/aws-autoloader.php';
        $obj = $this->getDataObject();
        $s3Client = new S3Client($this->getS3ClientParameters());
        $result = $s3Client->deleteMatchingObjects($obj->bucket_name, $fileName);
    }

    public function move_uploaded_file($tmp_name, $filename) {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 3000); // 60 minutes
        global $global;
        $this->copy_to_s3($tmp_name, $filename);
        @unlink($tmp_name);
        file_put_contents("{$global['systemRootPath']}videos/{$filename}", "Dummy File");
    }

    public function copy_to_s3($tmp_name, $filename) {
        global $global;
        $obj = $this->getDataObject();
        $s3Client = new S3Client($this->getS3ClientParameters());
        $parameters = array(
            'Bucket' => $obj->bucket_name,
            'Key' => $filename,
            'SourceFile' => $tmp_name
        );
        if (!empty($obj->makeMyFilesPublicRead)) {
            $parameters['ACL'] = 'public-read';
        }
        // Upload a file.
        $result = $s3Client->putObject($parameters);
        return $result;
    }

}
