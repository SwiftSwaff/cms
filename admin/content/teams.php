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
        
        $query = "SELECT T.ID, T.DivisionID, T.Name "
               . "FROM teams T "
               . "INNER JOIN divisions D on D.ID = T.DivisionID "
               . "WHERE D.IsActive = '1' "
               . "ORDER BY T.Name ASC ";
        $stmt = DB::getInstance()->makeQuery($query);
        $stmt->bind_result($id, $divisionID, $teamName);
        while ($stmt->fetch()) {
            $divisions[$divisionID]["HTML"].= "<li><a class='list-elem' href='{$this->editURL}{$id}'>{$teamName}</a></li>";
        }
        $stmt->close();
        
        $viewPanels = "<div class='viewPanels'>";
        $first = true;
        foreach ($divisions as $divID => $divContent) {
            if ($userDivID == 0 || $divID == $userDivID) {
                $display = "none";
                if ($first) {
                    $display = "block";
                    $first = false;
                }
                $viewPanels.= "<div class='viewPanel viewPanel-elem-" . $divID .  "' style='display: " . $display .";'>"
                          .     "<span class='headerText'>Teams</span>"
                          .     "<ul class='archiveList'>" . $divContent["HTML"] . "</ul>"
                          . "</div>";
            }
        }
        $viewPanels.= "</div>";
        
        return parseDivisionSelector() . $viewPanels;
    }
    
    protected function add() {
        if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars()) {
                $insert = "INSERT INTO teams (DivisionID, Name) VALUES (?, ?) ";
                $types = "ds";
                $params = array($this->vars["divisionID"], $this->vars["teamName"]);
                $stmt = DB::getInstance()->makeQuery($insert, $types, $params);
                
                $insert = "INSERT INTO standings (TeamID) VALUES (?) ";
                $types = "d";
                $params = array($stmt->insert_id);
                $stmt = DB::getInstance()->makeQuery($insert, $types, $params);
                $stmt->close();
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $divOptions = "";
        $query = "SELECT ID, Name FROM divisions ORDER BY Name ASC";
        $stmt = DB::getInstance()->makeQuery($query);
        $stmt->bind_result($id, $name);
        while ($stmt->fetch()) {
            $divOptions.= "<option value='{$id}'>{$name}</option>";
        }
        $stmt->close();
        
        $html = returnToPrev($this->archiveURL, "Return to Teams")
              . "<form id='contentForm' action='{$this->addURL}' method='post'>"
              .     "<label for='divisionID'>Division</label>"
              .     "<select id='divisionID' name='divisionID'>{$divOptions}</select>"
              .     renderInputField("teamName", "text", "", "Team Name")
              .     renderSubmitBtn("contentSubmit", "Submit")
              . "</form>";
        return $html;
    }
    
    protected function edit() {
        if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars()) {
                $update = "UPDATE teams SET DivisionID = ?, Name = ? WHERE ID = ? ";
                $types = "dsd";
                $params = array($this->vars["divisionID"], $this->vars["teamName"], $this->teamID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $divOptions = array();
        $query = "SELECT ID, Name FROM divisions ORDER BY Name ASC";
        $stmt = DB::getInstance()->makeQuery($query);
        $stmt->bind_result($id, $name);
        while ($stmt->fetch()) {
            $divOptions[$id] = $name;
        }
        $stmt->close();
        
        $html = "";
        $query = "SELECT DivisionID, Name FROM teams WHERE ID = ?";
        $types = "d";
        $params = array($this->teamID);
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($divID, $teamName);
        while ($stmt->fetch()) {
            $divOptionsList = "";
            foreach ($divOptions as $id => $name) {
                $selected = ($divID == $id) ? "selected" : "";
                $divOptionsList.= "<option value='{$id}' {$selected}>{$name}</option>";
            }
            $html = returnToPrev($this->archiveURL, "Return to Teams")
                  . "<form id='contentForm' action='{$this->editURL}{$this->teamID}' method='post'>"
                  .     "<div class='contentRow'>"
                  .         "<label for='divisionID'>Edit Division</label>"
                  .         "<select id='divisionID' name='divisionID'>{$divOptionsList}</select>"
                  .     "</div>"
                  .     "<div class='contentRow'>" . renderInputField("teamName", "text", $teamName, "Edit Team Name") . "</div>"
                  .     renderSubmitBtn("contentSubmit", "Submit")
                  . "</form>";
            
        }
        return $html;
    }
    
    protected function delete() {
        if ($_SESSION["Role"] != "Admin") {
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $update = "DELETE FROM teams WHERE ID = ? ";
        $types = "d";
        $params = array($this->teamID);
        $stmt = DB::getInstance()->makeQuery($update, $types, $params);
        $stmt->close();

        header("location: {$this->archiveURL}");
        exit;
    }
}

$content = new Teams();
$html = $content->getHTML($_GET["action"]);
include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>