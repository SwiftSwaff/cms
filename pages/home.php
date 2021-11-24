<?php
include getenv("DOCUMENT_ROOT") . "/db.php";

function getNews($newsPostIdx, $newsPostNum, $fromAJAX) {
    $html = "";
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
        $thumbnail = ($photoDir != null) ? "<img src='{$photoDir}' alt='{$title} Thumbnail' class='thumbnail' width='240' height='180'>" : "";

        $html.= "<article class='previewArticle homeMode'>"
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

    if (!$fromAJAX) {
        $newsPostIdx += $newsPostNum;
        $html.= "<div id='loadNewsArea'>"
              .     "<button class='loadNewsBtn'>Load More Articles</button>"
              .     "<input type='hidden' id='newsIdx' value='{$newsPostIdx}'>"
              .     "<input type='hidden' id='newsNum' value='{$newsPostNum}'>"
              . "</div>";
    }

    return $html;
}

if (isset($_POST["newsPostIdx"])) {
    echo getNews($_POST["newsPostIdx"], $_POST["newsPostNum"], true);
    exit;
}

$main = getNews(0, 4, false);
?>