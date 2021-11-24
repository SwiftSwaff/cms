<?php
include getenv("DOCUMENT_ROOT") . "/db.php";
$MetaDescription = "The official source for news on the XLL. Get all the latest XLL news now!";

function getNews($newsPostIdx, $newsPostNum, $fromAJAX) {
    $articlesHTML = "";
    $query = "SELECT ID, Title, Photo, DatePublished, DateEdited "
           . "FROM news "
           . "ORDER BY DatePublished DESC "
           . "LIMIT ?, ? ";
    $types = "dd";
    $params = array($newsPostIdx, $newsPostNum);
    $stmt = DB::getInstance()->makeQuery($query, $types, $params);
    $stmt->bind_result($id, $title, $photoDir, $datePub, $dateEdit);
    while ($stmt->fetch()) {
        $datePubStr = date("n/j/Y", strtotime($datePub));
        $thumbnail = ($photoDir != null) ? "<img src='{$photoDir}' alt='{$title} Thumbnail' class='thumbnail' width=160 height=120>" : "";

        $articlesHTML.= "<article class='previewArticle newsMode'>"
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
    $stmt->close();

    if ($articlesHTML == "") {
        return false;
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

function getNewsPost($newsPostID) {
    $html = "";
    $query = "SELECT Title, Content, Photo, DatePublished, DateEdited "
           . "FROM news "
           . "WHERE ID = ? ";
    $types = "d";
    $params = array($newsPostID);
    $stmt = DB::getInstance()->makeQuery($query, $types, $params);
    $stmt->bind_result($title, $content, $photoDir, $datePub, $dateEdit);
    while ($stmt->fetch()) {
        $contentHTML = html_entity_decode($content, ENT_COMPAT, "UTF-8");
        $datePubStr = "Published on " . date("F j, Y", strtotime($datePub)) . " at " . date("g:i a", strtotime($datePub));
        $dateEditStr = ($dateEdit != $datePub) ? "Edited on " . date("F j, Y", strtotime($dateEdit)) . " at " . date("g:i a", strtotime($dateEdit)) . "" : "";
        $bannerIncluded = ($photoDir != null) ? "style='background-image: url({$photoDir})'" : "";

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
    $stmt->close();

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