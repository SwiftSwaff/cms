<?php
include getenv("DOCUMENT_ROOT") . "/admin/content/content.php";
class Schedule extends Content {
    public $gameID;
    
    protected function setVars() {
        $this->vars = array(
            "divisionID"   => null,
            "datetime"     => null,
            "isExhibition" => null,
            "homeTeamID"   => null,
            "awayTeamID"   => null
        );
    }
    
    protected function setURLs() {
        $this->archiveURL = "/admin/content/schedule?action=archive";
        $this->addURL     = "/admin/content/schedule?action=add";
        $this->editURL    = "/admin/content/schedule?action=edit&id=";
        $this->deleteURL  = "/admin/content/schedule?action=delete&id=";
    }
    
    public function __construct() {
        $this->gameID = !empty($_GET["id"]) ? $_GET["id"] : null;
        parent::__construct();
    }
    
    protected function archive() {
        $userDivID = $_SESSION["DivisionID"];
        $userTeamID = $_SESSION["TeamID"];
        $divisions = parseLeagueDivisions("IncompleteHTML", "CompleteHTML");
        
        $query = "SELECT S.ID, S.DivisionID, S.Time, S.Complete, H.Name, S.HomeTeamScore, A.Name, S.AwayTeamScore "
               . "FROM schedule S "
               . "INNER JOIN teams H on H.ID = S.HomeTeamID "
               . "INNER JOIN teams A on A.ID = S.AwayTeamID "
               . "INNER JOIN divisions D on D.ID = S.DivisionID "
               . "WHERE D.IsActive = '1' "
               . "ORDER BY S.Time ASC, S.ID ASC ";
        $stmt = DB::getInstance()->makeQuery($query);
        $stmt->bind_result($id, $divisionID, $time, $complete, $homeTeamName, $homeTeamScore, $awayTeamName, $awayTeamScore);
        while ($stmt->fetch()) {
            $dateHTML = date("F jS Y @ h:i A", strtotime($time));
            
            if ($complete == "No") {
                $anchor = "<a class='list-elem' href='{$this->editURL}{$id}'>[{$dateHTML}] {$homeTeamName} vs {$awayTeamName}</a>"
                        . "<a class='delBtn' href='{$this->deleteURL}{$id}'>Delete</a>";
                $divisions[$divisionID]["IncompleteHTML"].= "<li>{$anchor}</li>";
            }
            else {
                $anchor = "<a class='list-elem' href='{$this->editURL}{$id}'>[{$dateHTML}] {$homeTeamName} vs {$awayTeamName}</a>";
                $divisions[$divisionID]["CompleteHTML"].= "<li>{$anchor}</li>";
            }
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
                $viewPanels.= "<div class='viewPanel viewPanel-elem-{$divID}' style='display: {$display};'>"
                          .     "<span class='headerText'>Remaining Games</span>"
                          .     "<ul class='archiveList'>{$divContent["IncompleteHTML"]}</ul>"
                          .     "<a href='{$this->addURL}&divisionID={$divID}' class='addBtn'>Add New Game</a>"
                          .     "<span class='headerText'>Completed Games</span>"
                          .     "<ul class='archiveList'>{$divContent["CompleteHTML"]}</ul>"
                          . "</div>";
            }
        }
        $viewPanels.= "</div>";
        
        return parseDivisionSelector() . $viewPanels;
    }
    
    protected function add() {
        if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars()) {
                $insert = "INSERT INTO schedule (Time, DivisionID, IsExhibition, Complete, HomeTeamID, HomeTeamScore, AwayTeamID, AwayTeamScore) VALUES (?, ?, ?, 'No', ?, '0', ?, '0') ";
                $types = "sdddd";
                $params = array($this->vars["datetime"], $this->vars["divisionID"], $this->vars["isExhibition"], $this->vars["homeTeamID"], $this->vars["awayTeamID"]);
                $stmt = DB::getInstance()->makeQuery($insert, $types, $params);
                $stmt->close();
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $divisionID = isset($_GET["divisionID"]) ? $_GET["divisionID"] : null;
        if (!$divisionID) {
            header("location: schedule?action=archive");
            exit;
        }
        
        $teamOptions = "";
        $query = "SELECT ID, Name FROM teams WHERE DivisionID = ? ORDER BY Name ASC";
        $types = "d";
        $params = array($divisionID);
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($id, $name);
        while ($stmt->fetch()) {
            $teamOptions.= "<option value='{$id}'>{$name}</option>";
        }
        $stmt->close();
        
        $html = returnToPrev($this->archiveURL, "Return to Schedule")
              . "<form id='contentForm' action='{$this->addURL}' method='post' style='width: 600px;'>"
              .     "<div class='contentRow'>" . renderInputField("datetime", "datetime-local", "", "Date & Time") . "</div>"
              .     "<div class='contentRow'>" . renderCheckbox("isExhibition", "", "Is Exhibition?") . "</div>"
              .     "<div class='contentRow'>"
              .         "<label for='homeTeamID'>Home Team</label>"
              .         "<select id='homeTeamID' name='homeTeamID'>{$teamOptions}</select>"
              .     "</div>"
              .     "<div class='contentRow'>"
              .         "<label for='awayTeamID'>Away Team</label>"
              .         "<select id='awayTeamID' name='awayTeamID'>{$teamOptions}</select>"
              .     "</div>"
              .     renderInputField("divisionID", "hidden", $divisionID)
              .     renderSubmitBtn("contentSubmit", "Submit")
              . "</form>";
        return $html;
    }
    
    protected function edit() {
       if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars()) {
                $update = "UPDATE schedule SET Time = ?, IsExhibition = ?, HomeTeamID = ?, AwayTeamID = ? WHERE ID = ? ";
                $types = "sdddd";
                $params = array($this->vars["datetime"], $this->vars["isExhibition"], $this->vars["homeTeamID"], $this->vars["awayTeamID"], $this->gameID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $teamOptions = array();
        $query = "SELECT ID, DivisionID, Name FROM teams ORDER BY Name ASC ";
        $stmt = DB::getInstance()->makeQuery($query);
        $stmt->bind_result($teamID, $divisionID, $name);
        while ($stmt->fetch()) {
            $teamOptions[$divisionID][$teamID] = $name;
        }
        $stmt->close();
        
        $html = "";
        $query = "SELECT S.Time, S.DivisionID, S.IsExhibition, H.ID, H.Name, A.ID, A.Name "
               . "FROM schedule S "
               . "INNER JOIN teams H on H.ID = S.HomeTeamID "
               . "INNER JOIN teams A on A.ID = S.AwayTeamID "
               . "WHERE S.ID = ? ";
        $types = "d";
        $params = array($this->gameID);
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($time, $divisionID, $isExhibition, $homeTeamID, $homeTeamName, $awayTeamID, $awayTeamName);
        while ($stmt->fetch()) {
            $homeTeamOptions = "";
            $awayTeamOptions = "";
            foreach ($teamOptions[$divisionID] as $id => $name) {
                $selected = ($homeTeamID == $id) ? "selected" : "";
                $homeTeamOptions.= "<option value='{$id}' {$selected}>{$name}</option>";
                
                $selected = ($awayTeamID == $id) ? "selected" : "";
                $awayTeamOptions.= "<option value='{$id}' {$selected}>{$name}</option>";
            }
            $exhibition = ($isExhibition) ? "checked" : "";
            
            $dateHTML = date("Y-m-d\TH:i", strtotime($time));
            $html = returnToPrev($this->archiveURL, "Return to Schedule")
                  . "<form id='contentForm' action='schedule?action=edit&id={$this->gameID}' method='post' style='width: 600px;'>"
                  .     "<div class='contentRow'>" . renderInputField("datetime", "datetime-local", $dateHTML, "Date & Time") . "</div>"
                  .     "<div class='contentRow'>" . renderCheckbox("isExhibition", $exhibition, "Is Exhibition?") . "</div>"
                  .     "<div class='contentRow'>"
                  .         "<label for='homeTeamID'>Home Team</label>"
                  .         "<select id='homeTeamID' name='homeTeamID'>{$homeTeamOptions}</select>"
                  .     "</div>"
                  .     "<div class='contentRow'>"
                  .         "<label for='awayTeamID'>Away Team</label>"
                  .         "<select id='awayTeamID' name='awayTeamID'>{$awayTeamOptions}</select>"
                  .     "</div>"
                  .     renderInputField("divisionID", "hidden", $divisionID)
                  .     renderSubmitBtn("contentSubmit", "Submit")
                  . "</form>";
        }
        $stmt->close();
        
        return $html;
    }
    
    protected function delete() {
        if ($_SESSION["Role"] != "Admin") {
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $update = "DELETE FROM schedule WHERE ID = ? ";
        $types = "d";
        $params = array($this->gameID);
        $stmt = DB::getInstance()->makeQuery($update, $types, $params);
        $stmt->close();

        header("location: {$this->archiveURL}");
        exit;
    }
}

$content = new Schedule();
$html = $content->getHTML($_GET["action"]);
include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>