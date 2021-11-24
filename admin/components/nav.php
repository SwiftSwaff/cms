<?php
function getAdminOptions() {
    $relPath = "/admin";
    
    return <<<HTML
<li class='adminNavHeader'>Analytics</li>
<li class='adminNavOption'><a href='{$relPath}/dashboard'>Dashboard</a></li>
<li class='adminNavHeader'>Manage Data</li>
<li class='adminNavOption'><a href='{$relPath}/content/news?action=archive'>News</a></li>
<li class='adminNavOption'><a href='{$relPath}/content/teams?action=archive'>Teams</a></li>
<li class='adminNavOption'><a href='{$relPath}/content/schedule?action=archive'>Schedule</a></li>
<li class='adminNavOption'><a href='{$relPath}/content/results?action=archive'>Input Scores</a></li>
<li class='adminNavHeader'>Manage Images</li>
<li class='adminNavOption'><a href='{$relPath}/content/uploadImages'>Upload Images</a></li>
<li class='adminNavOption'><a href='{$relPath}/content/main-slider'>Main Slider</a></li>
<li class='adminNavOption'><a href='{$relPath}/content/gallery'>Gallery</a></li>
<li class='adminNavOption'><a href='{$relPath}/content/partners'>Partners</a></li>
<li class='adminNavHeader'>Social Media</li>
<li class='adminNavOption'><a href='{$relPath}/content/social-media'>Links</a></li>
<li class='adminNavOption'><a href='{$relPath}/content/social-featured'>Featured</a></li>
HTML;
}

function getNewscasterOptions() {
    $relPath = getenv("OPHIDIAN_CMS_PATH_REL");
    
    return <<<HTML
<li class='adminNavHeader'>Options</li>
<li class='adminNavOption'><a href='{$relPath}/content/news?action=archive'>Manage News</a></li>
HTML;
}

function getManagerOptions() {
    $relPath = getenv("OPHIDIAN_CMS_PATH_REL");
    
    return <<<HTML
<li class='adminNavHeader'>Options</li>
<li class='adminNavOption'><a href='{$relPath}/content/teams?action=archive'>Edit Team</a></li>
<li class='adminNavOption'><a href='{$relPath}/content/results?action=archive'>Input Results</a></li>
HTML;
}
?>

<aside id='navigation'>
    <ul>
        <?php 
            if ($_SESSION["Role"] == "Admin") { 
                echo getAdminOptions();
            }
            else if ($_SESSION["Role"] == "Manager") {
                echo getManagerOptions();
            }
            else if ($_SESSION["Role"] == "Newscaster") {
                echo getNewscasterOptions();
            }
        ?>
        <li class='adminNavOption'><a href='/'>Return to Site</a></li>
    </ul>
</aside>