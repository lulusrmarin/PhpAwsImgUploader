<?php
// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';
require '../include/functions.php';
require "../../Classes/db.php";
require "../include/creds.php";

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$db = new db($creds);

// AWS Specifics
$accessKeyId = 'AKIAJKZKRPODQPFJS2DA';
$secretKey = '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';
$bucket = "mechawrench";
$region = "us-west-1";

//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', 'ACCESS_KEY');
if (!defined('awsSecretKey')) define('awsSecretKey', 'ACCESS_Secret_KEY');

// Set Amazon s3 credentials
$client = S3Client::factory(
    array(
        'credentials' => [
            'key'    => $accessKeyId,
            'secret' => $secretKey
        ],
        'region' => $region,
        'version' => '2006-03-01'
    )
);

$_POST = json_decode(file_get_contents("php://input"),true);
if($_FILES){
    $name = $_FILES['file']['name'];
    $size = $_FILES['file']['size'];
    $tmp = $_FILES['file']['tmp_name'];
    $extension = explode(".", $name)[count(explode(".", $name)) - 1];
    $hash = md5(uniqid(rand(), true));

    $key = "img/{$_REQUEST['make']}/{$_REQUEST['model']}/$hash.$extension";
    $url = "https://$bucket.s3-$region.amazonaws.com/$key";
    addImage(['model' => $_REQUEST['model'], 'url' => $url]);

    try {
        $client->putObject(array(
             'Bucket'=> $bucket,
             'Key' =>  $key,
             'SourceFile' => $tmp
        ));

        $message = "S3 Upload Successful.";
    } catch (S3Exception $e) {
         // Catch an S3 specific exception.
        echo $e->getMessage();
    }

}