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
        $html = "<h1>News Archive</h1>"
              . "<ul class='archiveList'>";
        
        $query = "SELECT ID, Title, DatePublished FROM news ORDER BY DatePublished DESC";
        $stmt = DB::getInstance()->makeQuery($query);
        $stmt->bind_result($id, $title, $time);
        while ($stmt->fetch()) {
            $html.= "<li><a class='list-elem' href='{$this->editURL}{$id}'>[{$time}] {$title}</a></li>";
        }
        $stmt->close();
        
        $html.= "</ul>"
              . "<a href='{$this->addURL}' class='addBtn'>Add News Article</a>";
        
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
                
                $insert = "INSERT INTO news (Title, Photo, Content) VALUES (?, ?, ?) ";
                $types = "sss";
                $params = array($this->vars["title"], $photo, $content);
                $stmt = DB::getInstance()->makeQuery($insert, $types, $params);
                $stmt->close();
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
                
                $update = "UPDATE news SET Title = ?, Photo = ?, Content = ? WHERE ID = ? ";
                $types = "sssd";
                $params = array($this->vars["title"], $photo, $content, $this->newsID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $html = "";
        $query = "SELECT Title, Photo, Content FROM news WHERE ID = ?";
        $types = "d";
        $params = array($this->newsID);
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($title, $photoDir, $content);
        while ($stmt->fetch()) {
            $contentHTML = addslashes(html_entity_decode($content, ENT_COMPAT, "UTF-8"));
        
            $html = returnToPrev($this->archiveURL, "Return to News")
                  . "<form id='contentForm' action='{$this->editURL}{$this->newsID}' method='post'>" 
                  .     "<div class='contentRow'>" . renderInputField("title", "text", $title, "Title") . "</div>"
                  .     "<div class='contentRow'>" . getNewsPhoto($photoDir) . "</div>"
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
        $stmt->close();
        
        return $html;
    }
    
    protected function delete() {
        if ($_SESSION["Role"] != "Admin") {
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $update = "DELETE FROM news WHERE ID = ? ";
        $types = "d";
        $params = array($this->newsID);
        $stmt = DB::getInstance()->makeQuery($update, $types, $params);
        $stmt->close();

        header("location: {$this->archiveURL}");
        exit;
    }
}

$content = new News();
$html = $content->getHTML($_GET["action"]);
include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>