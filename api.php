<?php
// Include the SDK using the Composer autoloader
// Todo create local composer version of this
require '../mechawrench/img_uploader/vendor/autoload.php';
// Todo replace with a better scheme to get root path
require '../include/functions.php';
require "../Classes/db.php";
require "../mechawrench/include/creds.php";

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$db = new db($creds);

// AWS Specifics
$accessKeyId = 'AKIAJJAMRKTZBBSITBXA';
$secretKey = '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';
$bucket = "amillionbillionchan";
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

if($_GET){
    if($_GET['posts']){
        $return['posts'] = $db->execute("SELECT * FROM chan_posts WHERE parent_id = 0 ORDER BY date_created DESC");
    }
    if($_GET['getThread']){
        $return['posts'] = $db->execute("SELECT * FROM chan_posts WHERE parent_id = ? OR id = ? ORDER BY date_created DESC", [$_GET['getThread'],$_GET['getThread']]);
    }    
}

if($return){
    echo json_encode($return);
}

if($_FILES){
    if( $_REQUEST['post'] && $_REQUEST['title'] ){

        $return = [];

        $name = $_FILES['file']['name'];
        $size = $_FILES['file']['size'];
        $tmp = $_FILES['file']['tmp_name'];

        // Create URL
        $extension = explode(".", $name)[count(explode(".", $name)) - 1];
        $hash = md5(uniqid(rand(), true));
        $key = "$hash.$extension";
        $url = "https://$bucket.s3-$region.amazonaws.com/$key";

        // Todo:  Sanitize inserts
        // Todo:  Limit S3 bucket to private webhost IP address and make public for that
        $db->execute("INSERT INTO chan_posts (title, post, parent_id, img_url, email) VALUES (?, ?, ?, ?, ?)",[
            $_REQUEST['title'],
            $_REQUEST['post'],
            isset($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : null,
            $url,
            isset($_REQUEST['email']) ? $_REQUEST['email'] : null
        ]);

        try {
            $client->putObject(array(
                 'Bucket'=> $bucket,
                 'Key' =>  $key,
                 'SourceFile' => $tmp
            ));

            $message = "S3 Upload Successful.";
            $return['status'] = "S3 Upload Successful.";
        } catch (S3Exception $e) {
             // Catch an S3 specific exception.
            echo $e->getMessage();
        }
    }
}