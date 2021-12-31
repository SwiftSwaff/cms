<?php
include getenv("DOCUMENT_ROOT") . "/admin/content/content.php";
class Teams extends Content {
    public $teamID;
    
    protected function setVars() {
        $this->vars = array(
            "divisionID" => null,
            "teamName"   => null
        );
    }
    
    protected function setURLs() {
        $this->archiveURL = "/admin/content/teams?action=archive";
        $this->addURL     = "/admin/content/teams?action=add";
        $this->editURL    = "/admin/content/teams?action=edit&id=";
        $this->deleteURL  = "/admin/content/teams?action=delete&id=";
    }
    
    public function __construct() {
        $this->teamID = !empty($_GET["id"]) ? $_GET["id"] : null;
        parent::__construct();
    }
    
    protected function archive() {
        $userDivID = $_SESSION["DivisionID"];
        $userTeamID = $_SESSION["TeamID"];
        $divisions = parseLeagueDivisions("HTML");
        
        $sql = "SELECT T.id, T.division_id, T.name 
                FROM teams T 
                INNER JOIN divisions D on D.id = T.division_id 
                WHERE D.is_active = '1' 
                ORDER BY T.name ASC ";
        $result = DB::getInstance()->preparedQuery($sql);
        if ($result->rowCount() > 0) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $divisions[$division_id]["HTML"].= "<li><a class='list-elem' href='{$this->editURL}{$id}'>{$name}</a></li>";
            }

            $viewPanels = "<div class='viewPanels'>";
            $first = true;
            foreach ($divisions as $divID => $divContent) {
                if ($userDivID == 0 || $divID == $userDivID) {
                    $display = "none";
                    if ($first) {
                        $display = "block";
                        $first = false;
                    }
                    $viewPanels.= "<div class='viewPanel viewPanel-elem-{$divID}' style='display: {$display};'>"
                            .     "<span class='headerText'>Teams</span>"
                            .     "<ul class='archiveList'>{$divContent["HTML"]}</ul>"
                            . "</div>";
                }
            }
            $viewPanels.= "</div>";
            
            return parseDivisionSelector() . $viewPanels;
        }
        else {
            return "<h2>No divisions found - please create a division first</h2>";
        }
    }
    
    protected function add() {
        if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars()) {
                $sql = "INSERT INTO teams (division_id, name) VALUES (:division_id, :team_name) ";
                $params = array(
                    ":division_id" => array("type" => PDO::PARAM_INT, "value" => $this->vars["divisionID"]), 
                    ":team_name"   => array("type" => PDO::PARAM_STR, "value" => $this->vars["teamName"])
                );
                DB::getInstance()->preparedQuery($sql, $params);
                
                $team_id = DB::getInstance()->lastInsertId();
                $sql = "INSERT INTO standings (team_id) VALUES (:team_id) ";
                $params = array(":team_id" => array("type" => PDO::PARAM_INT, "value" => $team_id));
                DB::getInstance()->preparedQuery($sql, $params);
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $divOptions = "";
        $sql = "SELECT id, name 
                FROM divisions 
                ORDER BY name ASC ";
        $result = DB::getInstance()->preparedQuery($sql);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $divOptions.= "<option value='{$id}'>{$name}</option>";
        }
        
        return returnToPrev($this->archiveURL, "Return to Teams")
             . "<form id='contentForm' action='{$this->addURL}' method='post'>"
             .     "<label for='divisionID'>Division</label>"
             .     "<select id='divisionID' name='divisionID'>{$divOptions}</select>"
             .     renderInputField("teamName", "text", "", "Team Name")
             .     renderSubmitBtn("contentSubmit", "Submit")
             . "</form>";
    }
    
    protected function edit() {
        if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars()) {
                $sql = "UPDATE teams 
                        SET division_id = :division_id, name = :team_name 
                        WHERE id = :team_id ";
                $params = array(
                    ":division_id" => array("type" => PDO::PARAM_INT, "value" => $this->vars["divisionID"]), 
                    ":team_name"   => array("type" => PDO::PARAM_STR, "value" => $this->vars["teamName"]), 
                    ":team_id"     => array("type" => PDO::PARAM_INT, "value" => $this->teamID)
                );
                DB::getInstance()->preparedQuery($sql, $params);
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $divOptions = array();
        $sql = "SELECT id, name 
                FROM divisions 
                ORDER BY name ASC ";
        $result = DB::getInstance()->preparedQuery($sql);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $divOptions[$id] = $name;
        }
        
        $html = "";
        $sql = "SELECT division_id, name AS team_name
                FROM teams 
                WHERE id = :team_id ";
        $params = array(":team_id" => array("type" => PDO::PARAM_INT, "value" => $this->teamID));
        $result = DB::getInstance()->preparedQuery($sql, $params);
        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $divOptionsList = "";
            foreach ($divOptions as $id => $name) {
                $selected = ($division_id == $id) ? "selected" : "";
                $divOptionsList.= "<option value='{$id}' {$selected}>{$name}</option>";
            }
            return returnToPrev($this->archiveURL, "Return to Teams")
                 . "<form id='contentForm' action='{$this->editURL}{$this->teamID}' method='post'>"
                 .     "<div class='contentRow'>"
                 .         "<label for='divisionID'>Edit Division</label>"
                 .         "<select id='divisionID' name='divisionID'>{$divOptionsList}</select>"
                 .     "</div>"
                 .     "<div class='contentRow'>" . renderInputField("teamName", "text", $team_name, "Edit Team Name") . "</div>"
                 .     renderSubmitBtn("contentSubmit", "Submit")
                 . "</form>";
        }
        else {

        }
    }
    
    protected function delete() {
        if ($_SESSION["Role"] != "Admin") {
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $sql = "DELETE FROM teams WHERE id = :team_id ";
        $params = array(":team_id" => array("type" => PDO::PARAM_INT, "value" => $this->teamID));
        DB::getInstance()->preparedQuery($update, $types, $params);

        header("location: {$this->archiveURL}");
        exit;
    }
}

$content = new Teams();
$html = $content->getHTML($_GET["action"]);
include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>