<?php
include getenv("DOCUMENT_ROOT") . "/admin/content/content.php";
include getenv("DOCUMENT_ROOT") . "/admin/components/image-helper.php";
$GLOBALS["BaseDirectory"] = getenv("DOCUMENT_ROOT") . "/resources/uploads/images";

function getNewsPhoto($photoDir="") {
    global $BaseDirectory;
    
    $photoDir = str_replace("/resources/uploads/images/", "", $photoDir);
    $datalistHTML = "<label for='photo'>Thumbnail: </label>"
                  . "<input list='photo-choices' id='photo' name='photo' autocomplete='off' value='{$photoDir}'>"
                  . "<datalist id='photo-choices'>";

    $it = new RecursiveIteratorIterator(getImages());
    foreach ($it as $filepath) {
        $strippedFilepath = str_replace("\\", "/", $filepath);
        $strippedFilepath = str_replace(getenv("DOCUMENT_ROOT") . "/resources/uploads/images/", "", $strippedFilepath);

        $baseFilenameArr = explode(".", $strippedFilepath);
        if (end($baseFilenameArr) == "webp") {
            continue;
        }
        
        $datalistHTML.= "<option value='{$strippedFilepath}'>";
    }

    $datalistHTML.= "</datalist><span style='margin-left: 5px; font-style: italic;'>*Empty the field for all selections</span>";

    return $datalistHTML;
}

class News extends Content {
    public $newsID;
    
    protected function setVars() {
        $this->vars = array(
            "title"   => null,
            "photo"   => null,
            "content" => null
        );
    }
    
    protected function setURLs() {
        $this->archiveURL = "/admin/content/news?action=archive";
        $this->addURL     = "/admin/content/news?action=add";
        $this->editURL    = "/admin/content/news?action=edit&id=";
        $this->deleteURL  = "/admin/content/news?action=delete&id=";
    }
    
    public function __construct() {
        $this->newsID = !empty($_GET["id"]) ? $_GET["id"] : null;
        parent::__construct();
    }
    
    protected function archive() {
        $html = "<h1>News Archive</h1>";
        
        $sql = "SELECT id, title, date_published 
                FROM news 
                ORDER BY date_published DESC ";
        $result = DB::getInstance()->preparedQuery($sql);
        if ($result->rowCount() > 0) {
            $html.= "<ul class='archiveList'>";
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $html.= "<li><a class='list-elem' href='{$this->editURL}{$id}'>[{$date_published}] {$title}</a></li>";
            }
            $html.= "</ul>";
        }
        else {
            $html.= "<h2>No News Articles Found</h2>";
        }
        

        $html.= "<a href='{$this->addURL}' class='addBtn'>Add News Article</a>";
        
        return $html;
    }
    
    protected function add() {
        if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars("photo")) {
                $photo = "";
                if ($this->vars["photo"] != "") {
                    $photo = "/resources/uploads/images/" . $this->vars["photo"];
                }
                
                $content = htmlentities($this->vars["content"], ENT_COMPAT, "UTF-8");
                
                $sql = "INSERT INTO news (title, photo, content) 
                        VALUES (:title, :photo, :content) ";
                $params = array(
                    ":title"   => array("type" => PDO::PARAM_STR, "value" => $this->vars["title"]),
                    ":photo"   => array("type" => PDO::PARAM_STR, "value" => $photo),
                    ":content" => array("type" => PDO::PARAM_STR, "value" => $content)
                );
                DB::getInstance()->preparedQuery($sql, $params);
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $html = returnToPrev($this->archiveURL, "Return to News")
              . "<form id='contentForm' action='{$this->addURL}' method='post'>" 
              .     "<div class='contentRow'>" . renderInputField("title", "text", "", "Title") . "</div>"
              .     "<div class='contentRow'>" . getNewsPhoto() . "</div>"
              .     "<div class='contentColumn'>"
              .         "<div class='contentRow'><label for='content'>Content</label></div>"
              .         "<textarea id='content' name='content'></textarea>"
              .     "</div>"
              .     renderSubmitBtn("contentSubmit", "Submit")
              . "</form>"
              . "<script src='https://cdn.tiny.cloud/1/7bdchqlo5d90u8szjxt4qcpc1kxa2hhu2zx1wcaz0txjuvb3/tinymce/5/tinymce.min.js' referrerpolicy='origin'></script>"
              . "<script>"
              .     "tinymce.init({"
              .         "selector: 'textarea',"
              .         "indent: false, "
              .         "plugins: 'link', "
              .         "height: 500,"
              .         "menubar: false,"
              .         "setup: function (editor) {"
              .             "editor.on('change', function () {"
              .                 "editor.save();"
              .             "});"
              .         "}"
              .     "});"
              . "</script>";
        return $html;
    }
    
    protected function edit() {
        if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars("photo")) {
                $photo = "";
                if ($this->vars["photo"] != "") {
                    $photo = "/resources/uploads/images/" . $this->vars["photo"];
                }
                $content = htmlentities($this->vars["content"], ENT_COMPAT, "UTF-8");
                
                $sql = "UPDATE news 
                        SET title = :title, photo = :photo, content = :content 
                        WHERE id = :id ";
                $params = array(
                    ":title"   => array("type" => PDO::PARAM_STR, "value" => $this->vars["title"]),
                    ":photo"   => array("type" => PDO::PARAM_STR, "value" => $photo),
                    ":content" => array("type" => PDO::PARAM_STR, "value" => $content),
                    ":id"      => array("type" => PDO::PARAM_INT, "value" => $this->newsID)
                );
                DB::getInstance()->preparedQuery($sql, $params);
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $sql = "SELECT title, photo, content 
                FROM news 
                WHERE id = :id";
        $params = array(":id" => array("type" => PDO::PARAM_INT, "value" => $this->newsID));
        $result = DB::getInstance()->preparedQuery($sql, $params);
        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $contentHTML = addslashes(html_entity_decode($content, ENT_COMPAT, "UTF-8"));
            
            return returnToPrev($this->archiveURL, "Return to News")
                 . "<form id='contentForm' action='{$this->editURL}{$this->newsID}' method='post'>" 
                 .     "<div class='contentRow'>" . renderInputField("title", "text", $title, "Title") . "</div>"
                 .     "<div class='contentRow'>" . getNewsPhoto($photo) . "</div>"
                 .     "<div class='contentColumn'>"
                 .         "<div class='contentRow'><label for='content'>Content</label></div>"
                 .         "<textarea id='content' name='content'></textarea>"
                 .     "</div>"
                 .     renderSubmitBtn("contentSubmit", "Submit")
                 . "</form>"
                 . "<script src='https://cdn.tiny.cloud/1/7bdchqlo5d90u8szjxt4qcpc1kxa2hhu2zx1wcaz0txjuvb3/tinymce/5/tinymce.min.js' referrerpolicy='origin'></script>"
                 . "<script>"
                 .     "tinymce.init({"
                 .         "selector: 'textarea',"
                 .         "indent: false, "
                 .         "plugins: 'link', "
                 .         "height: 500,"
                 .         "menubar: false,"
                 .         "setup: function (editor) {"
                 .             "editor.on('init', function() {"
                 .                 "editor.setContent('{$contentHTML}');"
                 .             "});"
                 .             "editor.on('change', function () {"
                 .                 "editor.save();"
                 .             "});"
                 .         "}"
                 .     "});"
                 . "</script>";
        }
        else {
            return "<h1>This news post does not exist.</h1>";
        }
    }
    
    protected function delete() {
        if ($_SESSION["Role"] != "Admin") {
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $sql = "DELETE FROM news WHERE id = :id ";
        $params = array(":id" => array("type" => PDO::PARAM_INT, "value" => $this->newsID));
        DB::getInstance()->preparedQuery($sql, $params);
        
        header("location: {$this->archiveURL}");
        exit;
    }
}

$content = new News();
$html = $content->getHTML($_GET["action"]);
include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>