<?php
if (!defined('IndexAccessed')) {
    die('Direct access not permitted');
}
?>
<aside id='right-sidebar'>
    <div id='twitter-widget'>
        <a class="twitter-timeline" href="https://twitter.com/xtremelaxleague?ref_src=twsrc%5Etfw" 
           data-height=450
           data-chrome="nofooter"
           omit_script=true>
        </a>
        <script id="twitter-wjs" async src="https://platform.twitter.com/widgets.js" charset="utf-8">Tweets by XtremeLaxLeague</script>
    </div>
    <div id='instagram-widget'>
        <?php 
            $xml = json_decode(json_encode(simplexml_load_file(getenv("DOCUMENT_ROOT") . "/config/socialConfig.xml")), true);
            $embedded = !empty($xml["instagram"]["featured"]) ? $xml["instagram"]["featured"] : "";
            echo $embedded;
        ?>
    </div>
</aside>