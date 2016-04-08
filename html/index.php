<?php
define('BOOT',1);
require_once('../app/init.php');

$address = $_SERVER['REMOTE_ADDR'];
if ($address != '199.27.133.30')
{
    echo 'Under construction!';
    exit;
}

$App = new App();

?>

<form name="test" method="post" action="http://www.sketchbook.cafe/home/testsubmit/">
<input name="name" type="text">
<input type="submit" value="Submit">
</form>

<?php

/*
// S3
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

// Use the us-west-2 region and latest version of each client.
$sharedConfig = [
    'region'  => 'us-west-2',
    'version' => 'latest'
];

// Create an SDK class used to share configuration across clients.
$sdk = new Aws\Sdk($sharedConfig);

// Create an Amazon S3 client using the shared configuration data.
$client = $sdk->createS3();




// Use an Aws\Sdk class to create the S3Client object.
$s3Client = $sdk->createS3();


// Download the contents of the object.
$result = $s3Client->getObject([
    'Bucket' => 'my-bucket',
    'Key'    => 'my-key'
]);

// Print the body of the result by indexing into the result object.
echo $result['Body'];

echo 'hmm';
*/