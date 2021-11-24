<?php
if (!defined('IndexAccessed')) {
    die('Direct access not permitted');
}

$GLOBALS["MetaDescription"] = "";

switch ($slug) {
    case "":
        $showLeftSidebar = false;
        $showRightSidebar = true;
        $showBanner = true;
        $MetaDescription = "The official website of the XLL, complete with news, standings, schedules, stats, and more.";
        include getenv("DOCUMENT_ROOT") . "/pages/home.php";
        break;
    case "news":
        include getenv("DOCUMENT_ROOT") . "/pages/news.php";
        break;
    case "standings":
        include getenv("DOCUMENT_ROOT") . "/pages/standings.php";
        break;
    case "schedule":
        include getenv("DOCUMENT_ROOT") . "/pages/schedule.php";
        break;
    case "rosters":
        include getenv("DOCUMENT_ROOT") . "/pages/rosters.php";
        break;
    case "references":
        $MetaDescription = "Reference documents for past, present, and future XLL players.";
        include getenv("DOCUMENT_ROOT") . "/pages/references.php";
        break;
    case "gallery":
        include getenv("DOCUMENT_ROOT") . "/pages/gallery.php";
        break;
    case "partners":
        include getenv("DOCUMENT_ROOT") . "/pages/partners.php";
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        include getenv("DOCUMENT_ROOT") . "/404.html";
        break;
}
?>