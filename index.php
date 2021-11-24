<?php
ini_set("log_errors", 1);
ini_set("error_log", "tmp/php-error.log");
define('IndexAccessed', true);

$path = ltrim($_SERVER['REQUEST_URI'], '/');
$segments = explode('/', $path);

$showBanner = false;
$showLeftSidebar = false;
$showRightSidebar = false;
$metaDesc = "";

$slug = explode("?", strtolower($segments[0]))[0];
$filename = isset($segments[1]) ? strtolower($segments[1]) : null;

ob_start();
include getenv("DOCUMENT_ROOT") . '/router.php';
$content = ob_get_contents();
ob_end_clean();
?>

<!DOCTYPE html>
<html lang='en-US'>
    <head>
        <?php include getenv("DOCUMENT_ROOT") . '/components/meta.php'; ?>
        <?php include getenv("DOCUMENT_ROOT") . '/components/external.php'; ?>
        <title>Official Site of the X-treme Lacrosse League | xtremelaxleague.ca</title>
    </head>
    <body>
        <?php include getenv("DOCUMENT_ROOT") . '/components/header.php'; ?>
        <div id="wrapper">
            <?php if ($showBanner) { ?>
                <div id='banner'>
                    <?php 
                        include getenv("DOCUMENT_ROOT") . '/components/slider.php'; 
                        createSlider("sliderConfig.xml", "main-slider", "mainSlider.js"); 
                    ?>
                </div> 
            <?php } ?>
            <div class='flex-container'>
                <main>
                    <?php echo $main; ?>
                </main>
                <?php 
                if ($showLeftSidebar) { include getenv("DOCUMENT_ROOT") . '/components/left-sidebar.php'; }
                if ($showRightSidebar) { include getenv("DOCUMENT_ROOT") . '/components/right-sidebar.php'; } ?>
            </div>
        </div>
        <?php include getenv("DOCUMENT_ROOT") . '/components/footer.php'; ?>
    </body>
</html>