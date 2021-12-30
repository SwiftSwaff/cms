<?php
include getenv("DOCUMENT_ROOT") . "/db/db.php";
$MetaDescription = "The official source for standings on the XLL. Get the current XLL standings now!";

function getStandings($divID, $headerText) {
    $sql = "SELECT T.name, S.wins, S.losses, S.points, S.goals_for, S.goals_against 
            FROM standings S 
            INNER JOIN teams T on T.id = S.team_id 
            WHERE T.division_id = :divID 
            ORDER BY S.points DESC, S.wins DESC, S.goal_differential DESC, S.losses ASC, T.name ASC ";
    $params = array(":divID" => array("type" => PDO::PARAM_INT, "value" => $divID));
    $result = DB::getInstance()->preparedQuery($sql, $params);
    if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        return "<h1 class='pageHeaderText'>{$headerText}</h1>"
             . "<div class='table-wrapper'>"
             .    "<table class='small'>"
             .        "<thead>"
             .            "<tr><th>Team</th><th>W</th><th>L</th><th>Pts</th><th>GF</th><th>GA</th></tr>"
             .        "</thead>"
             .        "<tbody>"
             .            "<tr>"
             .                "<td>{$name}</td>"
             .                "<td>{$wins}</td>"
             .                "<td>{$losses}</td>"
             .                "<td>{$points}</td>"
             .                "<td>{$goals_for}</td>"
             .                "<td>{$goals_against}</td>"
             .            "</tr>"
             .        "</tbody>"
             .    "</table>"
             . "</div>";
    }
    else {
        return "<h1 class='pageHeaderText'>Standings Coming Soon!</h1>";
    }
}

$path = ltrim($_SERVER['REQUEST_URI'], '/');
$elements = explode('/', $path);

$page = strtolower($elements[0]);
$param = isset($elements[1]) ? strtolower($elements[1]) : null;
if ($page == "standings") {
    switch ($param) {
        case "summer2021":
            $main.= getStandings(1, "Summer 2021 Standings");
            break;
        case "winter2021":
            $main.= getStandings(2, "Winter 2021 Senior Standings");
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