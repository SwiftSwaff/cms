<?php
include getenv("DOCUMENT_ROOT") . "/admin/components/logged-in.php";
include getenv("DOCUMENT_ROOT") . "/db.php";
include getenv("DOCUMENT_ROOT") . "/admin/components/content-helper.php";

abstract class Content {
    public $vars;
    abstract protected function setVars();
    
    public $archiveURL;
    public $addURL;
    public $editURL;
    public $deleteURL;
    abstract protected function setURLs();
    
    public function __construct() {
        $this->setVars();
        $this->setURLs();
    }
    
    public function getHTML($action) {
        $html = "";
        if (!empty($action)) {
            switch ($action) {
                case "archive":
                    $html = $this->archive();
                    break;
                case "add":
                    $html = $this->add();
                    break;
                case "edit":
                    $html = $this->edit();
                    break;
                case "delete":
                    $html = $this->delete();
                    break;
                default:
                    header('HTTP/1.1 404 Not Found');
                    $html.= "404";
                    break;
            }
        }
        return $html;
    }
    
    public function validateVars(...$ignoredVars) {
        $setVars = array();
        foreach ($this->vars as $key => $value) {
            $setVars[$key] = isset($_POST[$key]) ? $_POST[$key] : null;
        }
        
        $this->vars = $setVars;
        foreach ($this->vars as $key => $value) {
            if ($value == null) {
                if (!in_array($key, $ignoredVars)) {
                    echo "Missing variable {$key} from admin post. Please contact admin.";
                    exit;
                    return false;
                }
            }
        }
        return true;
    }
    
    abstract protected function archive();
    abstract protected function add();
    abstract protected function edit();
    abstract protected function delete();
}
?>