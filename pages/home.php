<?php
include getenv("DOCUMENT_ROOT") . "/db/db.php";

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

if (isset($_POST["newsPostIdx"])) {
    echo getNews($_POST["newsPostIdx"], $_POST["newsPostNum"], true);
    exit;
}

$main = getNews(0, 4, false);
?>