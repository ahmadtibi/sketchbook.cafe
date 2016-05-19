<?php
// @author          Kameloh
// @lastUpdated     2016-05-17

// Add HTTP Function
function add_http($url)
{
    if (!preg_match("~^(?:f|ht)tps?://~i", $url))
    {
        $url = 'http://' . $url;
    }
    return $url;
}

// Actual Link
$actual_link    = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$arr            = explode('?url=',$actual_link);
$url            = $arr[1];

// A few replacers just in case
$url    = str_replace('"','&#34;',$url);
$url    = str_replace("'",'&#39;',$url);
$url    = str_replace('%20','',$url);
if (isset($url{2000})) {
    echo 'Invalid URL';
    exit;
}

// Parse
$parse      = parse_url($url);
$url_host   = strtolower($parse['host']);
$url_host   = str_replace('www.','.',$url_host);

// If the URL is off the main site then just redirect directly
if (substr($url_host, -16) == '.sketchbook.cafe')
{
    header('Location: '.$url);
    exit;
}

// Add HTTP if it doesn't exist
$url = add_http($url);
?>
<style type="text/css">
body {
    margin: 0px;
}
.out_wrap {
    overflow: hidden;
    text-align: center;
    font-size: 24px;
    padding: 50px;
}

</style>

<div class="out_wrap">
    Hey put a message here warning about outbound links
    <div>
        <a href="<?php echo $url;?>"><?php echo $url;?></a>
    </div>
</div>