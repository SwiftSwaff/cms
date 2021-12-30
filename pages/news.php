<?php
require_once getenv("DOCUMENT_ROOT") . "/db/db.php";
$MetaDescription = "The official source for news on the XLL. Get all the latest XLL news now!";

//TODO: Eliminate re-use of this function shared between home.php & news.php
function getNews($newsPostIdx, $newsPostNum, $fromAJAX) {
    $sql = "SELECT id, title, photo, date_published 
            FROM news 
            ORDER BY date_published DESC 
            LIMIT :i, :num ";
    $params = array(
        ":i"   => array("type" => PDO::PARAM_INT, "value" => $newsPostIdx), 
        ":num" => array("type" => PDO::PARAM_INT, "value" => $newsPostNum)
    );
    $result = DB::getInstance()->preparedQuery($sql, $params);
    if ($result->rowCount() > 0) {
        $articlesHTML = "";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $datePubStr = date("n/j/Y", strtotime($date_published));
            $thumbnail = ($photo != null) ? "<img src='{$photo}' alt='{$title} Thumbnail' class='thumbnail' width='240' height='180'>" : "";
            $articlesHTML.= "<article class='previewArticle homeMode'>"
                          . "<a href='news?id={$id}'>"
                          .     "<div class='previewLeft'>{$thumbnail}</div>"
                          .     "<div class='previewRight'>"
                          .         "<span class='previewDate'>{$datePubStr}</span>"
                          .         "<span class='previewHeaderText'>{$title}</span>"
                          .         "<span class='previewContent'>[...]</span>"
                          .     "</div>"
                          . "</a>"
                          . "</article>";
        }

        $html = "<div id='newsListWrapper'>" . $articlesHTML;
        if (!$fromAJAX) {
            $newsPostIdx += $newsPostNum;
            $html.= "<div id='loadNewsArea'>"
                  .     "<button class='loadNewsBtn'>Load More Articles</button>"
                  .     "<input type='hidden' id='newsIdx' value='{$newsPostIdx}'>"
                  .     "<input type='hidden' id='newsNum' value='{$newsPostNum}'>"
                  . "</div>";
        }
        $html.= "</div>";
        
        return $html;
    }
    else {
        return "<h1 class='pageHeaderText'>News Coming Soon!</h1>";
    }
}

function getNewsPost($newsPostID) {
    $html = "";
    $sql = "SELECT title, content, photo, date_published, date_edited 
            FROM news 
            WHERE id = :id ";
    $params = array(":id" => array("type" => PDO::PARAM_INT, "value" => $newsPostID));
    $result = DB::getInstance()->preparedQuery($sql, $params);
    if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $contentHTML = html_entity_decode($content, ENT_COMPAT, "UTF-8");
        $datePubStr = "Published on " . date("F j, Y", strtotime($date_published)) . " at " . date("g:i a", strtotime($date_published));
        $dateEditStr = ($date_edited != $date_published) ? "Edited on " . date("F j, Y", strtotime($date_edited)) . " at " . date("g:i a", strtotime($date_edited)) . "" : "";
        $bannerIncluded = ($photo != null) ? "style='background-image: url({$photo})'" : "";

        $html.= "<div id='newsPostWrapper'>"
              .     "<div id='header-img' {$bannerIncluded}></div>"
              .     "<div id='headline-section'>"
              .         "<h1>{$title}</h1>"
              .         "<div class='subTextContainer'>"
              .             "<span class='datePublishedText'>{$datePubStr}</span>"
              .             "<span class='dateEditedText'>{$dateEditStr}</span>"
              .         "</div>"
              .     "</div>"
              .     "<div id='content-section'>{$contentHTML}</div>"
              . "</div>";
    }

    global $MetaDescription;
    $MetaDescription = "{$title}";

    return $html;
}


if (isset($_POST["newsPostIdx"])) {
    echo getNews($_POST["newsPostIdx"], $_POST["newsPostNum"], true);
    exit;
}

if (isset($_GET["id"])) {
    $main = getNewsPost($_GET["id"]);
}
else {
    $main = getNews(0, 16, false);
}
?>