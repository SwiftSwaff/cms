<?php
require_once getenv("DOCUMENT_ROOT") . "/db/db.php";
$MetaDescription = "The official source for the XLL schedule. Find schedule times and history.";

function getSchedule($divID, $headerText) {
    $tableHTML = "";
    $gameNum = 1;
    $sql = "SELECT S.game_time, S.is_exhibition, S.is_complete, 
                H.name AS home_team_name, S.home_team_score, 
                A.Name AS away_team_name, S.away_team_score, 
                H.home_location 
            FROM schedule S 
            INNER JOIN teams H on H.id = S.home_team_id 
            INNER JOIN teams A on A.id = S.away_team_id 
            WHERE S.division_id = :divID 
            ORDER BY S.game_time ASC ";
    $params = array(":divID" => array("type" => PDO::PARAM_INT, "value" => $divID));
    $result = DB::getInstance()->preparedQuery($sql, $params);
    if ($result->rowCount() > 0) {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
    
            $dateStr = date("M jS g:i A", strtotime($game_time));
    
            $rowStyle = "";
            if ($is_exhibition && ($is_complete == "Yes")) {
                $rowStyle = " style='background-color: rgba(255,214,99,0.5);'";
            }
            else if ($is_exhibition) {
                $rowStyle = " style='background-color: rgba(255,214,99,0.25);'";
            }
            else if ($is_complete == "Yes") {
                $rowStyle = " style='background-color: rgba(70,140,70,0.5);'";
            }
    
            $tableHTML.= "<tr>"
                       .    "<td{$rowStyle}>{$gameNum}</td>"
                       .    "<td{$rowStyle}>{$dateStr}</td>"
                       .    "<td{$rowStyle}>{$home_team_name}</td><td{$rowStyle}> {$home_team_score}</td>"
                       .    "<td{$rowStyle}>{$away_team_name}</td><td{$rowStyle}> {$away_team_score}</td>"
                       . "</tr>";
            $gameNum++;
        }
    
        return "<h1 class='pageHeaderText'>{$headerText}</h1>"
             . "<div class='table-wrapper'>"
             .      "<table class='large'>"
             .          "<thead>"
             .              "<tr><th>#</th><th>Time</th><th colspan='2'>Home</th><th colspan='2'>Away</th></tr>"
             .          "</thead>"
             .          "<tbody>{$tableHTML}</tbody>"
             .      "</table>"
             . "</div>";
    }
    else {
        return "<h1 class='pageHeaderText'>Schedule Coming Soon!</h1>";
    }
}

$path = ltrim($_SERVER['REQUEST_URI'], '/');
$elements = explode('/', $path);

$page = strtolower($elements[0]);
$param = isset($elements[1]) ? strtolower($elements[1]) : null;
if ($page == "schedule") {
    switch ($param) {
        case "summer2021":
            $main.= getSchedule(1, "Summer 2021 Schedule");
            break;
        case "winter2021":
            $main.= getSchedule(2, "Winter 2021 Senior Schedule");
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