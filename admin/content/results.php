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
        $sql = "SELECT id AS player_id, CONCAT(first_Name, ' ', last_name) as full_name, jersey_number 
                FROM players 
                WHERE team_id = :team_id AND position = :position ";
        $params = array(
            ":team_id"  => array("type" => PDO::PARAM_INT, "value" => $teamID), 
            ":position" => array("type" => PDO::PARAM_STR, "value" => $position)
        );
        $result = DB::getInstance()->preparedQuery($sql, $params);
        if ($result->rowCount() > 0) {
            $html = "<table><thead>";
            if ($position == "Runner") {
                $html.= "<tr><th>#</th><th>Name</th><th>Played?</th><th>G</th><th>A</th><th>PIM</th></tr>";
            }
            else {
                $html.= "<tr><th>#</th><th>Name</th><th>Played?</th><th>SV</th><th>GA</th><th>PIM</th></tr>";
            }
            $html.= "</thead><tbody>";

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $html.= "<tr>"
                      .     "<td>{$jersey_number}" . renderInputField("playerIDs[]", "hidden", $player_id) . "</td>"
                      .     "<td>{$full_name}" . renderInputField("{$player_id}-P", "hidden", $position) . "</td>"
                      .     "<td>" . renderCheckbox("{$player_id}-1", "checked") . "</td>"
                      .     "<td>" . renderInputField("{$player_id}-2", "number", 0) . "</td>"
                      .     "<td>" . renderInputField("{$player_id}-3", "number", 0) . "</td>"
                      .     "<td>" . renderInputField("{$player_id}-4", "number", 0) . "</td>"
                      . "</tr>";
            }
            $html.= "</tbody></table>";
            return $html;
        }
        else {
            return "<h2>No players found</h2>";
        }
    }
    private function edit_getPlayers($teamID, $position) {
        $select = "";
        if ($position == "Runner") {
            $select = "GD.played, GD.goals AS stat_1, GD.assists AS stat_2, GD.penalty_minutes ";
        }
        else {
            $select = "GD.played, GD.saves AS stat_1, GD.goals_against AS stat_2, GD.penalty_minutes ";
        }

        $sql = "SELECT P.id AS player_id, CONCAT(P.first_name, ' ', P.last_name) as full_name, P.jersey_number, {$select} 
                FROM players P 
                INNER JOIN gamedata GD ON GD.player_id = P.id 
                WHERE GD.game_id = :game_id AND P.team_id = :team_id AND P.position = :position";
        $params = array(
            ":game_id"  => array("type" => PDO::PARAM_INT, "value" => $this->gameID),
            ":team_id"  => array("type" => PDO::PARAM_INT, "value" => $teamID),
            ":position" => array("type" => PDO::PARAM_STR, "value" => $position)
        );
        $result = DB::getInstance()->preparedQuery($sql, $params);
        if ($result->rowCount() > 0) {
            $html = "<table><thead>";
            if ($position == "Runner") {
                $html.= "<tr><th>#</th><th>Name</th><th>Played?</th><th>G</th><th>A</th><th>PIM</th></tr>";
            }
            else {
                $html.= "<tr><th>#</th><th>Name</th><th>Played?</th><th>SV</th><th>GA</th><th>PIM</th></tr>";
            }
            $html.= "</thead><tbody>";

            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $did_play = $played ? "checked" : "";
                $html.= "<tr>"
                      .     "<td>{$jersey_number}" . renderInputField("playerIDs[]", "hidden", $player_id) . "</td>"
                      .     "<td>{$full_name}" . renderInputField("{$player_id}-P", "hidden", $position) . "</td>"
                      .     "<td>" . renderCheckbox("{$player_id}-1", $did_play) . "</td>"
                      .     "<td>" . renderInputField("{$player_id}-2", "number", $stat_1) . "</td>"
                      .     "<td>" . renderInputField("{$player_id}-3", "number", $stat_2) . "</td>"
                      .     "<td>" . renderInputField("{$player_id}-4", "number", $penalty_minutes) . "</td>"
                      . "</tr>";
            }
            $html.= "</tbody></table>";
            return $html;
        }
        else {
            return "<h2>No players found</h2>";
        }
    }

    protected function archive() {
        $userDivID = $_SESSION["DivisionID"];
        $userTeamID = $_SESSION["TeamID"];
        $divisions = parseLeagueDivisions("IncompleteHTML", "CompleteHTML");
        
        $sql = "SELECT S.id, S.division_id, S.game_time, S.is_complete, 
                    H.name AS home_team_name, S.home_team_score, 
                    A.Name AS away_team_name, S.away_team_score 
                FROM schedule S 
                INNER JOIN teams H on H.id = S.home_team_id 
                INNER JOIN teams A on A.id = S.away_team_id 
                INNER JOIN divisions D on D.id = S.division_id 
                WHERE D.is_active = '1' 
                ORDER BY S.game_time ASC, S.id ASC ";
         $result = DB::getInstance()->preparedQuery($sql);
         if ($result->rowCount() > 0) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $dateHTML = date("F jS Y @ h:i A", strtotime($game_time));
                
                if ($is_complete == "No") {
                    $anchor = "<a class='list-elem' href='{$this->addURL}{$id}'>[{$dateHTML}] {$home_team_name} ({$home_team_score}) vs {$away_team_name} ({$away_team_score})</a>";
                    $divisions[$division_id]["IncompleteHTML"].= "<li>{$anchor}</li>";
                }
                else {
                    $anchor = "<a class='list-elem' href='{$this->editURL}{$id}'>[{$dateHTML}] {$home_team_name} ({$home_team_score}) vs {$away_team_name} ({$away_team_score})</a>";
                    $divisions[$division_id]["CompleteHTML"].= "<li>{$anchor}</li>";
                }
            }
        }
        else {
            return "<h2>No games found</h2>";
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
        
        $sql = "SELECT S.game_time, S.is_exhibition, S.division_id, 
                    H.Name AS home_team_name, S.home_team_id, 
                    A.Name AS away_team_name, S.away_team_id 
                FROM schedule S 
                INNER JOIN teams H on H.id = S.home_team_id 
                INNER JOIN teams A on A.id = S.away_team_id  
                WHERE S.id = :game_id ";
        $params = array(":game_id" => array("type" => PDO::PARAM_INT, "value" => $this->gameID));
        $result = DB::getInstance()->preparedQuery($sql, $params);
        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $homeTeamData = "<div class='contentRow v-right'>"
                            .     "<h1>{$home_team_name}</h1>"
                            .     renderInputField("homeTeamScore", "number", "0") 
                            .     renderInputField("homeTeamID", "hidden", $home_team_id)
                            . "</div>";
                    
            $awayTeamData = "<div class='contentRow v-left'>"
                            .     renderInputField("awayTeamScore", "number", "0") 
                            .     "<h1>{$away_team_name}</h1>"
                            .     renderInputField("awayTeamID", "hidden", $away_team_id)
                            . "</div>";

            return returnToPrev($this->archiveURL, "Return to Results")
               . "<form id='contentForm' action='{$this->addURL}{$this->gameID}' method='post'>"
               .     "<div class='contentRow spacer'>"
               .         "<div class='contentColumn twoCol'>"
               .             $homeTeamData
               .             $this->add_getPlayers($home_team_id, 'Runner')
               .             $this->add_getPlayers($home_team_id, 'Goaltender')
               .         "</div>"
               .         "<div class='contentColumn twoCol'>"
               .             $awayTeamData
               .             $this->add_getPlayers($away_team_id, 'Runner')
               .             $this->add_getPlayers($away_team_id, 'Goaltender')
               .         "</div>"
               .     "</div>"
               .     "<div class='contentRow v-center'>" . renderCheckbox("overtime", "", "Was Overtime Needed?") . "</div>"
               .     renderInputField("divisionID", "hidden", $division_id)
               .     renderInputField("homeTeamPIM", "hidden", 0)
               .     renderInputField("awayTeamPIM", "hidden", 0)
               .     renderInputField("isExhibition", "hidden", $is_exhibition)
               .     renderSubmitBtn("contentSubmit", "Submit")
               . "</form>";
        }
        else {
            return "<h2>Game not found</h2>";
        }
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
        $sql = "SELECT S.game_time, S.is_exhibition, S.division_id, S.had_overtime, 
                    S.home_team_id, H.Name AS home_team_name, S.home_team_score, S.home_team_pim, 
                    S.away_team_id, A.Name AS away_team_name, S.away_team_score, S.away_team_pim 
                FROM schedule S 
                INNER JOIN teams H on H.id = S.home_team_id 
                INNER JOIN teams A on A.id = S.away_team_id 
                WHERE S.id = :game_id ";
        $params = array(":game_id" => array("type" => PDO::PARAM_INT, "value" => $this->gameID));
        $result = DB::getInstance()->preparedQuery($sql, $params);
        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

            $homeTeamData = "<div class='contentRow v-right'>"
                          .     "<h1>{$home_team_name}</h1>"
                          .     renderInputField("homeTeamScore", "number", $home_team_score) 
                          .     renderInputField("homeTeamID", "hidden", $home_team_id)
                          . "</div>";
                        
            $awayTeamData = "<div class='contentRow v-left'>"
                          .     renderInputField("awayTeamScore", "number", $away_team_score) 
                          .     "<h1>{$away_team_name}</h1>"
                          .     renderInputField("awayTeamID", "hidden", $away_team_id)
                          . "</div>";

            $checked = ($had_overtime == "Yes") ? "checked" : "";

            return returnToPrev($this->archiveURL, "Return to Results")
               . "<form id='contentForm' action='{$this->editURL}{$this->gameID}' method='post'>"
               .     "<div class='contentRow spacer'>"
               .         "<div class='contentColumn twoCol'>"
               .             $homeTeamData
               .             $this->edit_getPlayers($home_team_id, 'Runner')
               .             $this->edit_getPlayers($home_team_id, 'Goaltender')
               .         "</div>"
               .         "<div class='contentColumn twoCol'>"
               .             $awayTeamData
               .             $this->edit_getPlayers($away_team_id, 'Runner')
               .             $this->edit_getPlayers($away_team_id, 'Goaltender')
               .         "</div>"
               .     "</div>"
               .     "<div class='contentRow v-center'>" . renderCheckbox("overtime", $checked, "Was Overtime Needed?") . "</div>"
               .     renderInputField("divisionID", "hidden", $division_id)
               .     renderInputField("homeTeamPIM", "hidden", $home_team_pim)
               .     renderInputField("awayTeamPIM", "hidden", $away_team_pim)
               .     renderInputField("isExhibition", "hidden", $is_exhibition)
               .     renderSubmitBtn("contentSubmit", "Submit")
               . "</form>";
        }
        else {
            return "<h2>Game not found</h2>";
        }
    }
    
    protected function delete() {
        //do nothing lmao
    }
    
    // TODO: Optimize this function or replace it entirely
    public function processGameData() {
        if (!$this->vars["isExhibition"]) {
            $homeTeamID = $this->vars["homeTeamID"];
            $awayTeamID = $this->vars["awayTeamID"];

            $gp = $goals = $assists = $points = $saves = $ga = $pim = 0;

            $sql = "INSERT INTO gamedata (game_id, player_id, played, goals, assists, saves, goals_against, penalty_minutes) VALUES ";

            $playerIDs = $_POST["playerIDs"];
            foreach ($playerIDs as $playerID) {
                if ($_POST["{$playerID}-P"] == "Runner") {
                    $gp      = $_POST["{$playerID}-1"];
                    $goals   = $_POST["{$playerID}-2"];
                    $assists = $_POST["{$playerID}-3"];
                    $points  = $goals + $assists;
                    $pim     = $_POST["{$playerID}-4"];

                    $sql.= "({$this->gameID}, {$playerID}, {$gp}, {$goals}, {$assists}, 0, 0, {$pim}),";
                }
                else if ($_POST["{$playerID}-P"] == "Goaltender") {
                    $gp     = $_POST["{$playerID}-1"];
                    $saves  = $_POST["{$playerID}-2"];
                    $ga     = $_POST["{$playerID}-3"];
                    $pim    = $_POST["{$playerID}-4"];

                    $sql.= "({$this->gameID}, {$playerID}, {$gp}, 0, 0, {$saves}, {$ga}, {$pim}),";
                }

                $update = "UPDATE players 
                           SET games_played = games_played + :gp, goals = goals + :g, assists = assists + :a, points = points + :p, 
                            penalty_minutes = penalty_minutes + :pim, saves = saves + :sv, goals_against = goals_against + :ga 
                           WHERE id = :player_id ";
                $params = array(
                    ":gp"        => array("type" => PDO::PARAM_INT, "value" => $gp),
                    ":g"         => array("type" => PDO::PARAM_INT, "value" => $goals),
                    ":a"         => array("type" => PDO::PARAM_INT, "value" => $assists),
                    ":p"         => array("type" => PDO::PARAM_INT, "value" => $points),
                    ":pim"       => array("type" => PDO::PARAM_INT, "value" => $pim),
                    ":sv"        => array("type" => PDO::PARAM_INT, "value" => $saves),
                    ":ga"        => array("type" => PDO::PARAM_INT, "value" => $ga),
                    ":player_id" => array("type" => PDO::PARAM_INT, "value" => $playerID)
                );
                DB::getInstance()->preparedQuery($update, $params);
            }
        
            $sql = substr($sql, 0, -1) . ";";
            DB::getInstance()->preparedQuery($sql);
        }
    }

    // TODO: Optimize this function or replace it entirely
    public function undoGameData() {
        if (!$this->vars["isExhibition"]) {
            $oldData = array();
            $sql = "SELECT player_id, played, goals, assists, saves, goals_against, penalty_minutes 
                    FROM gamedata 
                    WHERE game_id = :game_id ";
            $params = array(":game_id" => array("type" => PDO::PARAM_INT, "value" => $this->gameID));
            $result = DB::getInstance()->preparedQuery($sql, $params);
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $oldData[$player_id] = array(
                    "GamesPlayed" => $played,
                    "Goals"       => $goals,
                    "Assists"     => $assists,
                    "Points"      => $goals + $assists,
                    "PIM"         => $penalty_minutes,
                    "Saves"       => $saves,
                    "GA"          => $goals_against
                );
            }

            $gp = $goals = $assists = $points = $saves = $ga = $pim = 0;

            $playerIDs = $_POST["playerIDs"];
            foreach ($playerIDs as $playerID) {
                $gp      = $oldData[$playerID]["GamesPlayed"];
                $goals   = $oldData[$playerID]["Goals"];
                $assists = $oldData[$playerID]["Assists"];
                $points  = $oldData[$playerID]["Points"];
                $pim     = $oldData[$playerID]["PIM"];
                $saves   = $oldData[$playerID]["Saves"];
                $ga      = $oldData[$playerID]["GA"];

                $update = "UPDATE players 
                           SET games_played = games_played - :gp, goals = goals - :g, assists = assists - :a, points = points - :p, 
                            penalty_minutes = penalty_minutes - :pim, saves = saves - :sv, goals_against = goals_against - :ga 
                           WHERE id = :player_id ";
                $params = array(
                    ":gp"        => array("type" => PDO::PARAM_INT, "value" => $gp),
                    ":g"         => array("type" => PDO::PARAM_INT, "value" => $goals),
                    ":a"         => array("type" => PDO::PARAM_INT, "value" => $assists),
                    ":p"         => array("type" => PDO::PARAM_INT, "value" => $points),
                    ":pim"       => array("type" => PDO::PARAM_INT, "value" => $pim),
                    ":sv"        => array("type" => PDO::PARAM_INT, "value" => $saves),
                    ":ga"        => array("type" => PDO::PARAM_INT, "value" => $ga),
                    ":player_id" => array("type" => PDO::PARAM_INT, "value" => $playerID)
                );
                DB::getInstance()->preparedQuery($update, $params);
            }

            $delete = "DELETE FROM gamedata WHERE game_id = :game_id";
            $params = array(":game_id" => array("type" => PDO::PARAM_INT, "value" => $this->gameID));
            DB::getInstance()->preparedQuery($delete, $params);
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
        $update = "UPDATE schedule 
                   SET is_complete = 'Yes', had_overtime = :had_overtime, 
                    home_team_score = :home_team_score, home_team_pim = :home_team_pim, 
                    away_team_score = :away_team_score, away_team_pim = :away_team_pim 
                   WHERE id = :game_id ";
        $params = array(
            ":had_overtime"    => array("type" => PDO::PARAM_STR, "value" => $overtimeNeeded),
            ":home_team_score" => array("type" => PDO::PARAM_INT, "value" => $homeTeamScore),
            ":home_team_pim"   => array("type" => PDO::PARAM_INT, "value" => $homeTeamPIM),
            ":away_team_score" => array("type" => PDO::PARAM_INT, "value" => $awayTeamScore),
            ":away_team_pim"   => array("type" => PDO::PARAM_INT, "value" => $awayTeamPIM),
            ":game_id"         => array("type" => PDO::PARAM_INT, "value" => $this->gameID)
        );
        DB::getInstance()->preparedQuery($update, $params);

        if (!$this->vars["isExhibition"]) {
            $homeTeamGDiff = $homeTeamScore - $awayTeamScore;
            $awayTeamGDiff = $awayTeamScore - $homeTeamScore;

            if ($homeTeamScore == $awayTeamScore) {
                $update = "UPDATE standings 
                           SET ties = ties + 1, points = points + 1 
                           WHERE team_id IN (:home_team_id, :away_team_id) ";
                $params = array(
                    ":home_team_id" => array("type" => PDO::PARAM_INT, "value" => $homeTeamID),
                    ":away_team_id" => array("type" => PDO::PARAM_INT, "value" => $awayTeamID)
                );
                DB::getInstance()->preparedQuery($update, $params);
            }
            else {
                $winnerID = ($homeTeamScore > $awayTeamScore) ? $homeTeamID : $awayTeamID;
                $loserID  = ($homeTeamScore < $awayTeamScore) ? $homeTeamID : $awayTeamID;

                $update = "UPDATE standings SET wins = wins + 1, points = points + 2 WHERE team_id = :winner_id ";
                $params = array(":winner_id" => array("type" => PDO::PARAM_INT, "value" => $winnerID));
                DB::getInstance()->preparedQuery($update, $params);

                $overtimeLoss = ($overtimeNeeded == "Yes") ? ", overtime_losses = overtime_losses + 1" : "";
                $update = "UPDATE standings SET losses = losses + 1" . $overtimeLoss . " WHERE team_id = :loser_id ";
                $params = array(":loser_id" => array("type" => PDO::PARAM_INT, "value" => $loserID));
                DB::getInstance()->preparedQuery($update, $params);
            }
            
            $update = "UPDATE standings 
                       SET goals_for = goals_for + :gf, goals_against = goals_against + :ga, 
                        goal_differential = goal_differential + :gd, penalty_minutes = penalty_minutes + :pim 
                       WHERE team_id = :team_id ";

            $params = array(
                ":gf"      => array("type" => PDO::PARAM_INT, "value" => $homeTeamScore),
                ":ga"      => array("type" => PDO::PARAM_INT, "value" => $awayTeamScore),
                ":gd"      => array("type" => PDO::PARAM_INT, "value" => $homeTeamGDiff),
                ":pim"     => array("type" => PDO::PARAM_INT, "value" => $homeTeamPIM),
                ":team_id" => array("type" => PDO::PARAM_INT, "value" => $homeTeamID)
            );
            DB::getInstance()->preparedQuery($update, $params);
            
            $params = array(
                ":gf"      => array("type" => PDO::PARAM_INT, "value" => $awayTeamScore),
                ":ga"      => array("type" => PDO::PARAM_INT, "value" => $homeTeamScore),
                ":gd"      => array("type" => PDO::PARAM_INT, "value" => $awayTeamGDiff),
                ":pim"     => array("type" => PDO::PARAM_INT, "value" => $awayTeamPIM),
                ":team_id" => array("type" => PDO::PARAM_INT, "value" => $awayTeamID)
            );
            DB::getInstance()->preparedQuery($update, $params);
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
        
        $sql = "SELECT S.had_overtime, 
                    S.home_team_id, S.home_team_score, S.home_team_pim, 
                    S.away_team_id, S.away_team_score, S.away_team_pim 
                FROM schedule S 
                INNER JOIN teams H on H.id = S.home_team_id 
                INNER JOIN teams A on A.id = S.away_team_id 
                WHERE S.id = :game_id ";
        $params = array(":game_id" => array("type" => PDO::PARAM_INT, "value" => $this->gameID));
        $result = DB::getInstance()->preparedQuery($sql, $params);
        if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            
            $overtimeNeeded = $had_overtime;
            $homeTeamID     = $home_team_id;
            $homeTeamScore  = $home_team_score;
            $homeTeamPIM    = $home_team_pim;
            $awayTeamID     = $away_team_id;
            $awayTeamScore  = $away_team_score;
            $awayTeamPIM    = $away_team_pim;
        }

        if (!$this->vars["isExhibition"]) {
            $homeTeamGDiff = $homeTeamScore - $awayTeamScore;
            $awayTeamGDiff = $awayTeamScore - $homeTeamScore;

            if ($homeTeamScore == $awayTeamScore) {
                $update = "UPDATE standings 
                           SET ties = ties - 1, points = points - 1 
                           WHERE team_id IN (:home_team_id, :away_team_id) ";
                $params = array(
                    ":home_team_id" => array("type" => PDO::PARAM_INT, "value" => $homeTeamID),
                    ":away_team_id" => array("type" => PDO::PARAM_INT, "value" => $awayTeamID)
                );
                DB::getInstance()->preparedQuery($update, $params);
            }
            else {
                $winnerID = ($homeTeamScore > $awayTeamScore) ? $homeTeamID : $awayTeamID;
                $loserID  = ($homeTeamScore < $awayTeamScore) ? $homeTeamID : $awayTeamID;

                $update = "UPDATE standings SET wins = wins - 1, points = points - 2 WHERE team_id = :winner_id ";
                $params = array(":winner_id" => array("type" => PDO::PARAM_INT, "value" => $winnerID));
                DB::getInstance()->preparedQuery($update, $params);

                $overtimeLoss = ($overtimeNeeded == "Yes") ? ", overtime_losses = overtime_losses - 1" : "";
                $update = "UPDATE standings SET losses = losses - 1" . $overtimeLoss . " WHERE team_id = :loser_id ";
                $params = array(":loser_id" => array("type" => PDO::PARAM_INT, "value" => $loserID));
                DB::getInstance()->preparedQuery($update, $params);
            }
            
            $update = "UPDATE standings 
                       SET goals_for = goals_for - :gf, goals_against = goals_against - :ga, 
                        goal_differential = goal_differential - :gd, penalty_minutes = penalty_minutes - :pim 
                       WHERE team_id = :team_id ";

            $params = array(
                ":gf"      => array("type" => PDO::PARAM_INT, "value" => $homeTeamScore),
                ":ga"      => array("type" => PDO::PARAM_INT, "value" => $awayTeamScore),
                ":gd"      => array("type" => PDO::PARAM_INT, "value" => $homeTeamGDiff),
                ":pim"     => array("type" => PDO::PARAM_INT, "value" => $homeTeamPIM),
                ":team_id" => array("type" => PDO::PARAM_INT, "value" => $homeTeamID)
            );
            DB::getInstance()->preparedQuery($update, $params);
            
            $params = array(
                ":gf"      => array("type" => PDO::PARAM_INT, "value" => $awayTeamScore),
                ":ga"      => array("type" => PDO::PARAM_INT, "value" => $homeTeamScore),
                ":gd"      => array("type" => PDO::PARAM_INT, "value" => $awayTeamGDiff),
                ":pim"     => array("type" => PDO::PARAM_INT, "value" => $awayTeamPIM),
                ":team_id" => array("type" => PDO::PARAM_INT, "value" => $awayTeamID)
            );
            DB::getInstance()->preparedQuery($update, $params);
        }
    }
}

$content = new Results();
$html = $content->getHTML($_GET["action"]);
include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>