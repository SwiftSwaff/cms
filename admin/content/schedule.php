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
        
        $sql = "SELECT S.id, S.division_id, S.game_time, S.is_complete, H.Name AS home_team_name, A.Name AS away_team_name 
                FROM schedule S 
                INNER JOIN teams H on H.id = S.home_team_id 
                INNER JOIN teams A on A.id = S.away_team_id 
                INNER JOIN divisions D on D.id = S.division_id 
                WHERE D.is_active = '1' 
                ORDER BY S.game_time ASC, S.id ASC ";
        $result = DB::getInstance()->preparedQuery($sql);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $dateHTML = date("F jS Y @ h:i A", strtotime($game_time));
        
            if ($is_complete == "No") {
                $anchor = "<a class='list-elem' href='{$this->editURL}{$id}'>[{$dateHTML}] {$home_team_name} vs {$away_team_name}</a>"
                        . "<a class='delBtn' href='{$this->deleteURL}{$id}'>Delete</a>";
                $divisions[$division_id]["IncompleteHTML"].= "<li>{$anchor}</li>";
            }
            else {
                $anchor = "<a class='list-elem' href='{$this->editURL}{$id}'>[{$dateHTML}] {$home_team_name} vs {$away_team_name}</a>";
                $divisions[$division_id]["CompleteHTML"].= "<li>{$anchor}</li>";
            }
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
                $sql = "INSERT INTO schedule (game_time, division_id, is_exhibition, is_complete, home_team_id, home_team_score, away_team_id, away_team_score) 
                        VALUES (:game_time, :division_id, :is_exhibition, 'No', :home_team_id, '0', :away_team_id, '0') ";
                $params = array(
                    ":game_time"     => array("type" => PDO::PARAM_STR, "value" => $this->vars["datetime"]),
                    ":division_id"   => array("type" => PDO::PARAM_INT, "value" => $this->vars["divisionID"]),
                    ":is_exhibition" => array("type" => PDO::PARAM_INT, "value" => $this->vars["isExhibition"]),
                    ":home_team_id"  => array("type" => PDO::PARAM_INT, "value" => $this->vars["homeTeamID"]),
                    ":away_team_id"  => array("type" => PDO::PARAM_INT, "value" => $this->vars["awayTeamID"])
                );
                DB::getInstance()->preparedQuery($sql, $params);
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
        $sql = "SELECT id, name 
                FROM teams 
                WHERE division_id = :division_id 
                ORDER BY name ASC ";
        $params = array(":division_id" => array("type" => PDO::PARAM_INT, "value" => $divisionID));
        $result = DB::getInstance()->preparedQuery($sql, $params);
        if ($result->rowCount() > 0) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $teamOptions.= "<option value='{$id}'>{$name}</option>";
            }

            return returnToPrev($this->archiveURL, "Return to Schedule")
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
        }
        else {
            return "<h2>No divisions found - please create a division first</h2>";
        }
    }
    
    protected function edit() {
       if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars()) {
                $sql = "UPDATE schedule 
                        SET game_time = :game_time, is_exhibition = :is_exhibition, home_team_id = :home_team_id, away_team_id = :away_team_id  
                        WHERE id = :game_id ";
                $params = array(
                    ":game_time"     => array("type" => PDO::PARAM_STR, "value" => $this->vars["datetime"]),
                    ":is_exhibition" => array("type" => PDO::PARAM_INT, "value" => $this->vars["isExhibition"]),
                    ":home_team_id"  => array("type" => PDO::PARAM_INT, "value" => $this->vars["homeTeamID"]),
                    ":away_team_id"  => array("type" => PDO::PARAM_INT, "value" => $this->vars["awayTeamID"]),
                    ":game_id"       => array("type" => PDO::PARAM_INT, "value" => $this->gameID)
                );
                DB::getInstance()->preparedQuery($sql, $params);
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $teamOptions = array();
        $sql = "SELECT id, division_id, name 
                FROM teams 
                ORDER BY name ASC ";
        $result = DB::getInstance()->preparedQuery($sql);
        if ($result->rowCount() > 0) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $teamOptions[$division_id][$id] = $name;
            }
        }
        else {
            return "<h2>No divisions found - please create a division first</h2>";
        }
        
        $html = "";
        $sql = "SELECT S.game_time, S.division_id, S.is_exhibition, S.home_team_id, S.away_team_id 
                FROM schedule S 
                INNER JOIN teams H on H.id = S.home_team_id 
                INNER JOIN teams A on A.id = S.away_team_id 
                WHERE S.id = :game_id ";
        $params = array(":game_id" => array("type" => PDO::PARAM_INT, "value" => $this->gameID));
        $result = DB::getInstance()->preparedQuery($sql, $params);
        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $homeTeamOptions = "";
            $awayTeamOptions = "";
            foreach ($teamOptions[$division_id] as $id => $name) {
                $selected = ($home_team_id == $id) ? "selected" : "";
                $homeTeamOptions.= "<option value='{$id}' {$selected}>{$name}</option>";
                
                $selected = ($away_team_id == $id) ? "selected" : "";
                $awayTeamOptions.= "<option value='{$id}' {$selected}>{$name}</option>";
            }
            $exhibition_checked = ($is_exhibition) ? "checked" : "";
            
            $dateHTML = date("Y-m-d\TH:i", strtotime($game_time));

            return returnToPrev($this->archiveURL, "Return to Schedule")
                 . "<form id='contentForm' action='schedule?action=edit&id={$this->gameID}' method='post' style='width: 600px;'>"
                 .     "<div class='contentRow'>" . renderInputField("datetime", "datetime-local", $dateHTML, "Date & Time") . "</div>"
                 .     "<div class='contentRow'>" . renderCheckbox("isExhibition", $exhibition_checked, "Is Exhibition?") . "</div>"
                 .     "<div class='contentRow'>"
                 .         "<label for='homeTeamID'>Home Team</label>"
                 .         "<select id='homeTeamID' name='homeTeamID'>{$homeTeamOptions}</select>"
                 .     "</div>"
                 .     "<div class='contentRow'>"
                 .         "<label for='awayTeamID'>Away Team</label>"
                 .         "<select id='awayTeamID' name='awayTeamID'>{$awayTeamOptions}</select>"
                 .     "</div>"
                 .     renderInputField("divisionID", "hidden", $division_id)
                 .     renderSubmitBtn("contentSubmit", "Submit")
                 . "</form>";
        }
    }
    
    protected function delete() {
        if ($_SESSION["Role"] != "Admin") {
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $sql = "DELETE FROM schedule WHERE id = :game_id ";
        $params = array(":game_id" => array("type" => PDO::PARAM_INT, "value" => $this->gameID));
        DB::getInstance()->preparedQuery($sql, $params);

        header("location: {$this->archiveURL}");
        exit;
    }
}

$content = new Schedule();
$html = $content->getHTML($_GET["action"]);
include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>