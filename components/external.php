<?php
if (!defined('IndexAccessed')) {
    die('Direct access not permitted');
}
$fontURL = "https://fonts.googleapis.com/css2?"
            . "family=Roboto:ital,wght@0,400;0,700;1,400;1,700&Rubik:ital,wght@0,400;0,700;1,400;1,700&"
            . "family=Source+Sans+Pro:ital,wght@0,400;0,700;1,400;1,700&Rubik:ital,wght@0,400;0,700;1,400;1,700&"
            . "family=Rubik:ital,wght@0,400;0,700;1,400;1,700&Rubik:ital,wght@0,400;0,700;1,400;1,700&"
            . "display=swap"
?>

<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
<script type='text/javascript' src='/js/app.js'></script>
<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin/>
<link rel='stylesheet' rel='preload' as='style' href='<?php echo $fontURL ?>'/>
<link rel='stylesheet' rel='stylesheet' href='<?php echo $fontURL ?>' media='print' onload="this.media='all'"/>
<link rel='stylesheet' href='/css/style.css'/>
<link rel='stylesheet' href='/plugins/node_modules/keen-slider/keen-slider.min.css'/>
<link rel='icon' type='image/png' href='/resources/favicon.png'/>