<?php
include getenv("DOCUMENT_ROOT") . "/admin/content/content.php";
class Results extends Content {
    public $gameID;
    
    protected function setVars() {
        $this->vars = array(
            "divisionID"    => null, 
            "isExhibition"  => null, 
            "homeTeamID"    => null, 
            "homeTeamScore" => null, 
            "homeTeamPIM"   => null, 
            "overtime"      => null, 
            "awayTeamID"    => null,  
            "awayTeamScore" => null, 
            "awayTeamPIM"   => null
        );
    }
    
    protected function setURLs() {
        $this->archiveURL = "/admin/content/results?action=archive";
        $this->addURL     = "/admin/content/results?action=add&id=";
        $this->editURL    = "/admin/content/results?action=edit&id=";
        $this->deleteURL  = "";
    }
    
    public function __construct() {
        $this->gameID = !empty($_GET["id"]) ? $_GET["id"] : null;
        parent::__construct();
    }
    
    private function add_getPlayers($teamID, $position) {
        $html = "<table><thead>";
        if ($position == "Runner") {
            $html.= "<tr><th>#</th><th>Name</th><th>Played?</th><th>G</th><th>A</th><th>PIM</th></tr>";
        }
        else {
            $html.= "<tr><th>#</th><th>Name</th><th>Played?</th><th>SV</th><th>GA</th><th>PIM</th></tr>";
        }
        $html.= "</thead><tbody>";

        $query = "SELECT P.ID, CONCAT(P.FirstName, ' ', P.LastName) as FullName, P.JerseyNumber "
               . "FROM players P "
               . "WHERE P.TeamID = ? AND P.Position = ? ";
        $types = "ds";
        $params = array($teamID, $position);
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($playerID, $fullName, $jerseyNum);
        while ($stmt->fetch()) {
            $html.= "<tr>"
                  .     "<td>{$jerseyNum}" . renderInputField("playerIDs[]", "hidden", $playerID) . "</td>"
                  .     "<td>{$fullName}" . renderInputField("{$playerID}-P", "hidden", $position) . "</td>"
                  .     "<td>" . renderCheckbox("{$playerID}-1", "checked") . "</td>"
                  .     "<td>" . renderInputField("{$playerID}-2", "number", 0) . "</td>"
                  .     "<td>" . renderInputField("{$playerID}-3", "number", 0) . "</td>"
                  .     "<td>" . renderInputField("{$playerID}-4", "number", 0) . "</td>"
                  . "</tr>";
        }

        $html.= "</tbody></table>";
        return $html;
    }
    private function edit_getPlayers($teamID, $position) {
        $select = "";
        $html = "<table><thead>";
        if ($position == "Runner") {
            $html.= "<tr><th>#</th><th>Name</th><th>Played?</th><th>G</th><th>A</th><th>PIM</th></tr>";
            $select = "GD.Played, GD.Goals, GD.Assists, GD.PIM ";
        }
        else {
            $html.= "<tr><th>#</th><th>Name</th><th>Played?</th><th>SV</th><th>GA</th><th>PIM</th></tr>";
            $select = "GD.Played, GD.Saves, GD.GoalsAgainst, GD.PIM ";
        }
        $html.= "</thead><tbody>";

        $query = "SELECT P.ID, CONCAT(P.FirstName, ' ', P.LastName) as FullName, P.JerseyNumber, {$select} "
               . "FROM players P "
               . "INNER JOIN gamedata GD ON GD.PlayerID = P.ID "
               . "WHERE GD.GameID = ? AND P.TeamID = ? AND P.Position = ?";
        $types = "dds";
        $params = array($this->gameID, $teamID, $position);
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($playerID, $fullName, $jerseyNum, $gp, $col2, $col3, $pim);
        while ($stmt->fetch()) {
            $played = $gp ? "checked" : "";
            $html.= "<tr>"
                  .     "<td>{$jerseyNum}" . renderInputField("playerIDs[]", "hidden", $playerID) . "</td>"
                  .     "<td>{$fullName}" . renderInputField("{$playerID}-P", "hidden", $position) . "</td>"
                  .     "<td>" . renderCheckbox("{$playerID}-1", $played) . "</td>"
                  .     "<td>" . renderInputField("{$playerID}-2", "number", $col2) . "</td>"
                  .     "<td>" . renderInputField("{$playerID}-3", "number", $col3) . "</td>"
                  .     "<td>" . renderInputField("{$playerID}-4", "number", $pim) . "</td>"
                  . "</tr>";
        }

        $html.= "</tbody></table>";
        return $html;
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
                $anchor = "<a class='list-elem' href='{$this->addURL}{$id}'>[{$dateHTML}] {$homeTeamName} ({$homeTeamScore}) vs {$awayTeamName} ({$awayTeamScore})</a>";
                $divisions[$divisionID]["IncompleteHTML"].= "<li>{$anchor}</li>";
            }
            else {
                $anchor = "<a class='list-elem' href='{$this->editURL}{$id}'>[{$dateHTML}] {$homeTeamName} ({$homeTeamScore}) vs {$awayTeamName} ({$awayTeamScore})</a>";
                $divisions[$divisionID]["CompleteHTML"].= "<li>{$anchor}</li>";
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
                $this->processGameData();
                $this->processGameResults();
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $html = "";
        $query = "SELECT S.Time, S.IsExhibition, S.DivisionID, H.ID, H.Name, A.ID, A.Name "
               . "FROM schedule S "
               . "INNER JOIN teams H on H.ID = S.HomeTeamID "
               . "INNER JOIN teams A on A.ID = S.AwayTeamID "
               . "WHERE S.ID = ? ";
        $types = "d";
        $params = array($this->gameID);
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($time, $isExhibition, $divisionID, $homeTeamID, $homeTeamName, $awayTeamID, $awayTeamName);
        while ($stmt->fetch()) {
            $homeTeamData = "<div class='contentRow v-right'>"
                          .     "<h1>{$homeTeamName}</h1>"
                          .     renderInputField("homeTeamScore", "number", "0") 
                          .     renderInputField("homeTeamID", "hidden", $homeTeamID)
                          . "</div>";
                        
            $awayTeamData = "<div class='contentRow v-left'>"
                          .     renderInputField("awayTeamScore", "number", "0") 
                          .     "<h1>{$awayTeamName}</h1>"
                          .     renderInputField("awayTeamID", "hidden", $awayTeamID)
                          . "</div>";
        }
        $stmt->close();
        
        $html = returnToPrev($this->archiveURL, "Return to Results")
              . "<form id='contentForm' action='{$this->addURL}{$this->gameID}' method='post'>"
              .     "<div class='contentRow spacer'>"
              .         "<div class='contentColumn twoCol'>"
              .             $homeTeamData
              .             $this->add_getPlayers($homeTeamID, 'Runner')
              .             $this->add_getPlayers($homeTeamID, 'Goaltender')
              .         "</div>"
              .         "<div class='contentColumn twoCol'>"
              .             $awayTeamData
              .             $this->add_getPlayers($awayTeamID, 'Runner')
              .             $this->add_getPlayers($awayTeamID, 'Goaltender')
              .         "</div>"
              .     "</div>"
              .     "<div class='contentRow v-center'>" . renderCheckbox("overtime", "", "Was Overtime Needed?") . "</div>"
              .     renderInputField("divisionID", "hidden", $divisionID)
              .     renderInputField("homeTeamPIM", "hidden", 0)
              .     renderInputField("awayTeamPIM", "hidden", 0)
              .     renderInputField("isExhibition", "hidden", $isExhibition)
              .     renderSubmitBtn("contentSubmit", "Submit")
              . "</form>";

        return $html;
    }
    
    protected function edit() {
        if (isset($_POST["contentSubmit"])) {
            if ($this->validateVars()) {
                $this->undoGameData();
                $this->undoGameResults();
                $this->processGameData();
                $this->processGameResults();
            }
            header("location: {$this->archiveURL}");
            exit;
        }
        
        $html = "";
        $query = "SELECT S.Time, S.IsExhibition, S.DivisionID, S.Overtime, H.ID, H.Name, S.HomeTeamScore, S.HomeTeamPIM, A.ID, A.Name, S.AwayTeamScore, S.AwayTeamPIM "
               . "FROM schedule S "
               . "INNER JOIN teams H on H.ID = S.HomeTeamID "
               . "INNER JOIN teams A on A.ID = S.AwayTeamID "
               . "WHERE S.ID = ? ";
        $types = "d";
        $params = array($this->gameID);
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($time, $isExhibition, $divisionID, $overtime, $homeTeamID, $homeTeamName, $homeTeamScore, $homeTeamPIM, $awayTeamID, $awayTeamName, $awayTeamScore, $awayTeamPIM);
        while ($stmt->fetch()) {
            $homeTeamData = "<div class='contentRow v-right'>"
                          .     "<h1>{$homeTeamName}</h1>"
                          .     renderInputField("homeTeamScore", "number", $homeTeamScore) 
                          .     renderInputField("homeTeamID", "hidden", $homeTeamID)
                          . "</div>";
                        
            $awayTeamData = "<div class='contentRow v-left'>"
                          .     renderInputField("awayTeamScore", "number", $awayTeamScore) 
                          .     "<h1>{$awayTeamName}</h1>"
                          .     renderInputField("awayTeamID", "hidden", $awayTeamID)
                          . "</div>";
        }
        $stmt->close();

        $checked = ($overtime == "Yes") ? "checked" : "";
        $html = returnToPrev($this->archiveURL, "Return to Results")
              . "<form id='contentForm' action='{$this->editURL}{$this->gameID}' method='post'>"
              .     "<div class='contentRow spacer'>"
              .         "<div class='contentColumn twoCol'>"
              .             $homeTeamData
              .             $this->edit_getPlayers($homeTeamID, 'Runner')
              .             $this->edit_getPlayers($homeTeamID, 'Goaltender')
              .         "</div>"
              .         "<div class='contentColumn twoCol'>"
              .             $awayTeamData
              .             $this->edit_getPlayers($awayTeamID, 'Runner')
              .             $this->edit_getPlayers($awayTeamID, 'Goaltender')
              .         "</div>"
              .     "</div>"
              .     "<div class='contentRow v-center'>" . renderCheckbox("overtime", $checked, "Was Overtime Needed?") . "</div>"
              .     renderInputField("divisionID", "hidden", $divisionID)
              .     renderInputField("homeTeamPIM", "hidden", 0)
              .     renderInputField("awayTeamPIM", "hidden", 0)
              .     renderInputField("isExhibition", "hidden", $isExhibition)
              .     renderSubmitBtn("contentSubmit", "Submit")
              . "</form>";
        
        return $html;
    }
    
    protected function delete() {
        //do nothing lmao
    }
    
    public function processGameData() {
        if (!$this->vars["isExhibition"]) {
            $homeTeamID = $this->vars["homeTeamID"];
            $awayTeamID = $this->vars["awayTeamID"];

            $update = "";
            $gp = $goals = $assists = $points = $saves = $ga = $pim = 0;

            $insert = "INSERT INTO gamedata (GameID, PlayerID, Played, Goals, Assists, Saves, GoalsAgainst, PIM) VALUES ";
            $playerIDs = $_POST["playerIDs"];
            foreach ($playerIDs as $playerID) {
                if ($_POST["{$playerID}-P"] == "Runner") {
                    $gp = $_POST["{$playerID}-1"];
                    $goals = $_POST["{$playerID}-2"];
                    $assists = $_POST["{$playerID}-3"];
                    $points = $goals + $assists;
                    $pim = $_POST["{$playerID}-4"];

                    $insert.= "({$this->gameID}, {$playerID}, {$gp}, {$goals}, {$assists}, 0, 0, {$pim}),";
                }
                else if ($_POST["{$playerID}-P"] == "Goaltender") {
                    $gp = $_POST["{$playerID}-1"];
                    $saves = $_POST["{$playerID}-2"];
                    $ga = $_POST["{$playerID}-3"];
                    $pim = $_POST["{$playerID}-4"];

                    $insert.= "({$this->gameID}, {$playerID}, {$gp}, 0, 0, {$saves}, {$ga}, {$pim}),";
                }
                else {
                    // shouldnt get here
                }

                $update = "UPDATE players "
                        . "SET GamesPlayed = GamesPlayed + ?, Goals = Goals + ?, Assists = Assists + ?, Points = Points + ?, PIM = PIM + ?, Saves = Saves + ?, GA = GA + ? "
                        . "WHERE ID = ? ";
                $types = "dddddddd";
                $params = array($gp, $goals, $assists, $points, $pim, $saves, $ga, $playerID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();
            }
        
            $insert = substr($insert, 0, -1) . ";";
            $types = "";
            $params = array();
            $stmt = DB::getInstance()->makeQuery($insert, $types, $params);
            $stmt->close();
        }
    }

    public function undoGameData() {
        if (!$this->vars["isExhibition"]) {
            $oldData = array();
            $query = "SELECT PlayerID, Played, Goals, Assists, Saves, GoalsAgainst, PIM FROM gamedata WHERE GameID = ? ";
            $types = "d";
            $params = array($this->gameID);
            $stmt = DB::getInstance()->makeQuery($query, $types, $params);
            $stmt->bind_result($playerID, $gp, $goals, $assists, $saves, $ga, $pim);
            while ($stmt->fetch()) {
                $oldData[$playerID] = array(
                    "GamesPlayed" => $gp,
                    "Goals"       => $goals,
                    "Assists"     => $assists,
                    "Points"      => $goals + $assists,
                    "PIM"         => $pim,
                    "Saves"       => $saves,
                    "GA"          => $ga
                );
            }
            $stmt->close();

            $update = "";
            $gp = $goals = $assists = $points = $saves = $ga = $pim = 0;
            $playerIDs = $_POST["playerIDs"];
            foreach ($playerIDs as $playerID) {
                $gp      = $oldData[$playerID]["GamesPlayed"];
                $goals   = $oldData[$playerID]["Goals"];
                $assists = $oldData[$playerID]["Assists"];
                $points  = $oldData[$playerID]["Points"];
                $saves   = $oldData[$playerID]["Saves"];
                $ga      = $oldData[$playerID]["GA"];
                $pim     = $oldData[$playerID]["PIM"];

                $update = "UPDATE players "
                        . "SET GamesPlayed = GamesPlayed - ?, Goals = Goals - ?, Assists = Assists - ?, Points = Points - ?, PIM = PIM - ?, Saves = Saves - ?, GA = GA - ? "
                        . "WHERE ID = ? ";
                $types = "dddddddd";
                $params = array($gp, $goals, $assists, $points, $pim, $saves, $ga, $playerID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();
            }

            $delete = "DELETE FROM gamedata WHERE GameID = ?";
            $types = "d";
            $params = array($this->gameID);
            $stmt = DB::getInstance()->makeQuery($delete, $types, $params);
            $stmt->close();
        }
    }

    public function processGameResults() {
        $overtime       = $this->vars["overtime"];
        $homeTeamID     = $this->vars["homeTeamID"];
        $homeTeamScore  = $this->vars["homeTeamScore"];
        $homeTeamPIM    = $this->vars["homeTeamPIM"];
        $awayTeamID     = $this->vars["awayTeamID"];
        $awayTeamScore  = $this->vars["awayTeamScore"];
        $awayTeamPIM    = $this->vars["awayTeamPIM"];
        
        $overtimeNeeded = ($overtime == 1) ? "Yes" : "No";
        $update = "UPDATE schedule SET Complete = 'Yes', Overtime = ?, HomeTeamScore = ?, HomeTeamPIM = ?, AwayTeamScore = ?, AwayTeamPIM = ? WHERE ID = ? ";
        $types = "sddddd";
        $params = array($overtimeNeeded, $homeTeamScore, $homeTeamPIM, $awayTeamScore, $awayTeamPIM, $this->gameID);
        $stmt = DB::getInstance()->makeQuery($update, $types, $params);
        $stmt->close();

        if (!$this->vars["isExhibition"]) {
            $homeTeamGDiff = $homeTeamScore - $awayTeamScore;
            $awayTeamGDiff = $awayTeamScore - $homeTeamScore;

            if ($homeTeamScore == $awayTeamScore) {
                $update = "UPDATE standings SET Ties = Ties + 1, Points = Points + 1 WHERE TeamID IN (?, ?) ";
                $types = "dd";
                $params = array($homeTeamID, $awayTeamID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();
            }
            else {
                $winnerID = ($homeTeamScore > $awayTeamScore) ? $homeTeamID : $awayTeamID;
                $loserID  = ($homeTeamScore < $awayTeamScore) ? $homeTeamID : $awayTeamID;

                $update = "UPDATE standings SET Wins = Wins + 1, Points = Points + 2 WHERE TeamID = ? ";
                $types = "d";
                $params = array($winnerID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();

                $overtimeLoss = ($overtimeNeeded == "Yes") ? ", OTLosses = OTLosses + 1" : "";
                $update = "UPDATE standings SET Losses = Losses + 1" . $overtimeLoss . " WHERE TeamID = ? ";
                $types = "d";
                $params = array($loserID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();
            }
            
            $update = "UPDATE standings SET GF = GF + ?, GA = GA + ?, GDiff = GDiff + ?, PIM = PIM + ? WHERE TeamID = ? ";
            $types = "ddddd";
            $params = array($homeTeamScore, $awayTeamScore, $homeTeamGDiff, $homeTeamPIM, $homeTeamID);
            $stmt = DB::getInstance()->makeQuery($update, $types, $params);
            $stmt->close();
            
            $update = "UPDATE standings SET GF = GF + ?, GA = GA + ?, GDiff = GDiff + ?, PIM = PIM + ? WHERE TeamID = ? ";
            $types = "ddddd";
            $params = array($awayTeamScore, $homeTeamScore, $awayTeamGDiff, $awayTeamPIM, $awayTeamID);
            $stmt = DB::getInstance()->makeQuery($update, $types, $params);
            $stmt->close();
        }
    }
    
    public function undoGameResults() {
        $overtimeNeeded = "No";
        $homeTeamID     = 0;
        $homeTeamScore  = 0;
        $homeTeamPIM    = 0;
        $awayTeamID     = 0;
        $awayTeamScore  = 0;
        $awayTeamPIM    = 0;
        
        $query = "SELECT S.Overtime, H.ID, S.HomeTeamScore, S.HomeTeamPIM, A.ID, S.AwayTeamScore, S.AwayTeamPIM "
               . "FROM schedule S "
               . "INNER JOIN teams H on H.ID = S.HomeTeamID "
               . "INNER JOIN teams A on A.ID = S.AwayTeamID "
               . "WHERE S.ID = ? ";
        $types = "d";
        $params = array($this->gameID);
        $stmt = DB::getInstance()->makeQuery($query, $types, $params);
        $stmt->bind_result($ot, $hID, $hScore, $hPIM, $aID, $aScore, $aPIM);
        while ($stmt->fetch()) {
            $overtimeNeeded = $ot;
            $homeTeamID     = $hID;
            $homeTeamScore  = $hScore;
            $homeTeamPIM    = $hPIM;
            $awayTeamID     = $aID;
            $awayTeamScore  = $aScore;
            $awayTeamPIM    = $aPIM;
        }
        $stmt->close();

        if (!$this->vars["isExhibition"]) {
            $homeTeamGDiff = $homeTeamScore - $awayTeamScore;
            $awayTeamGDiff = $awayTeamScore - $homeTeamScore;

            if ($homeTeamScore == $awayTeamScore) {
                $update = "UPDATE standings SET Ties = Ties - 1, Points = Points - 1 WHERE TeamID IN (?, ?) ";
                $types = "dd";
                $params = array($homeTeamID, $awayTeamID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();
            }
            else {
                $winnerID = ($homeTeamScore > $awayTeamScore) ? $homeTeamID : $awayTeamID;
                $loserID  = ($homeTeamScore < $awayTeamScore) ? $homeTeamID : $awayTeamID;

                $update = "UPDATE standings SET Wins = Wins - 1, Points = Points - 2 WHERE TeamID = ? ";
                $types = "d";
                $params = array($winnerID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();

                $overtimeLoss = ($overtimeNeeded == "Yes") ? ", OTLosses = OTLosses - 1" : "";
                $update = "UPDATE standings SET Losses = Losses - 1" . $overtimeLoss . " WHERE TeamID = ? ";
                $types = "d";
                $params = array($loserID);
                $stmt = DB::getInstance()->makeQuery($update, $types, $params);
                $stmt->close();
            }
            
            $update = "UPDATE standings SET GF = GF - ?, GA = GA - ?, GDiff = GDiff - ?, PIM = PIM - ? WHERE TeamID = ? ";
            $types = "ddddd";
            $params = array($homeTeamScore, $awayTeamScore, $homeGDiff, $homeTeamPIM, $homeTeamID);
            $stmt = DB::getInstance()->makeQuery($update, $types, $params);
            $stmt->close();

            $update = "UPDATE standings SET GF = GF - ?, GA = GA - ?, GDiff = GDiff - ?, PIM = PIM - ? WHERE TeamID = ? ";
            $types = "ddddd";
            $params = array($awayTeamScore, $homeTeamScore, $awayTeamGDiff, $awayTeamPIM, $awayTeamID);
            $stmt = DB::getInstance()->makeQuery($update, $types, $params);
            $stmt->close();
        }
    }
}

$content = new Results();
$html = $content->getHTML($_GET["action"]);
include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>