<?php
include getenv("DOCUMENT_ROOT") . "/admin/content/content.php";
class Pages extends Content {
    protected function setVars() {
        $this->vars = array(
            "pageName" => null,
            "content"  => null
        );
    }
    
    protected function setURLs() {
        $this->archiveURL = "/admin/content/pages?action=archive";
        $this->addURL     = "";
        $this->editURL    = "/admin/content/pages?action=edit&pageName=";
        $this->deleteURL  = "";
    }
    
    public function __construct() {
        parent::__construct();
        $this->vars["pageName"] = !empty($_GET["pageName"]) ? $_GET["pageName"] : null;
    }
    
    protected function archive() {
        $html = "<h1>Pages Archive</h1>"
              . "<ul class='archiveList' style='width: 300px;'>"
              .     "<li><a class='list-elem' href='{$this->editURL}references'>References</a></li>"
              . "</ul>";
        
        return $html;
    }
    
    protected function add() {
        //do nothing lmao
    }
    
    protected function edit() {
        if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars()) {
                $file = fopen("../../pages/{$this->vars["pageName"]}.html", 'w');
                fwrite($file, $this->vars["content"]);
                fclose($file);
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $contentHTML = file_get_contents("../../pages/{$this->vars["pageName"]}.html");
        $contentHTML = trim(preg_replace('/\s\s+/', ' ', $contentHTML));
        $contentHTML = addslashes($contentHTML);
        $html = returnToPrev($this->archiveURL, "Return to Pages")
              . "<form id='contentForm' action='{$this->editURL}{$this->vars["pageName"]}' method='post'>"
              .     "<div class='contentColumn'>"
              .         renderInputField("pageName", "hidden", $this->vars["pageName"])
              .         "<div class='contentRow'><label for='content'>Page Content</label></div>"
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
              .             "editor.on('change', function() {"
              .                 "editor.save();"
              .             "});"
              .         "}"
              .     "});"
              . "</script>";
        return $html;
    }
    
    protected function delete() {
        //do nothing lmao
    }
}

$content = new Pages();
$html = $content->getHTML($_GET["action"]);
include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>