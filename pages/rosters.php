<?php
include getenv("DOCUMENT_ROOT") . "/db/db.php";
$MetaDescription = "View the rosters for all the XLL teams.";

function getRoster($teamID, $headerText) {
    $sql = "SELECT first_name, last_name, notes, jersey_number, shot_hand, position, games_played, goals, assists, points, penalty_minutes 
            FROM players 
            WHERE team_id = :teamID 
            ORDER BY jersey_number ASC ";
    $params = array(":teamID" => array("type" => PDO::PARAM_INT, "value" => $teamID));
    $result = DB::getInstance()->preparedQuery($sql, $params);
    if ($result->rowCount() > 0) {
        $runnerData = "";
        $goalieData = "";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
    
            $notesAddon = ($notes != null) ? "({$notes})" : "";
            if ($position == 'Runner') {
                $runnerData.= "<tr>"
                            .    "<td>{$first_name} {$last_name} {$notesAddon}</td><td>{$jersey_number}</td><td>{$shot_hand}</td>"
                            .    "<td>{$games_played}</td><td>{$goals}</td><td>{$assists}</td><td>{$points}</td>"
                            .    "<td>{$penalty_minutes}</td>"
                            . "</tr>";
            }
            else {
                $goalieData.= "<tr>"
                            .    "<td>{$first_name} {$last_name} {$notesAddon}</td><td>{$jersey_number}</td><td>{$games_played}</td>"
                            . "</tr>";
            }
        }

        return "<h1 class='pageHeaderText'>{$headerText}</h1>"
             . "<h1 class='pageHeaderText'>Runners</h1>"
             . "<div class='table-wrapper'>"
             .    "<table class='roster-table'>"
             .        "<thead>"
             .            "<tr><th>Name</th><th>#</th><th>Shot</th><th>GP</th><th>G</th><th>A</th><th>P</th><th>PIM</th></tr>"
             .        "</thead>"
             .        "<tbody>{$runnerData}</tbody>"
             .    "</table>"
             . "</div>"
             . "<h1 class='pageHeaderText'>Goaltenders</h1>"
             . "<div class='table-wrapper'>"
             .    "<table class='roster-table small'>"
             .        "<thead>"
             .            "<tr><th>Name</th><th>#</th><th>GP</th></tr>"
             .        "</thead>"
             .        "<tbody>{$goalieData}</tbody>"
             .    "</table>"
             . "</div>";
    }
    else {
        return "<h1 class='pageHeaderText'>Roster Coming Soon!</h1>";
    }
}

$path = ltrim($_SERVER['REQUEST_URI'], '/');
$elements = explode('/', $path);

$page = strtolower($elements[0]);
$param = isset($elements[1]) ? strtolower($elements[1]) : null;
if ($page == "rosters") {
    switch ($param) {
        case "wolves-summer2021":
            $main.= getRoster(1, "Grey Wolves");
            break;
        case "skyhawks-summer2021":
            $main.= getRoster(2, "Red Skyhawks");
            break;
        case "bears-summer2021":
            $main.= getRoster(3, "Black Bears");
            break;
        case "wolves-winter2021":
            $main.= getRoster(4, "Grey Wolves");
            break;
        case "hawks-winter2021":
            $main.= getRoster(5, "Red Hawks");
            break;
        case "bears-winter2021":
            $main.= getRoster(6, "Black Bears");
            break;
        case "eagles-winter2021":
            $main.= getRoster(7, "Golden Eagles");
            break;
        default:
            $showBanner = false;
            $showLeftSidebar = false;
            $showRightSidebar = false;
            header('HTTP/1.1 404 Not Found');
            $main.= file_get_contents("404.html");
            break;
    }
}
else {
    header('HTTP/1.1 404 Not Found');
    $main.= file_get_contents("404.html");
}
?>