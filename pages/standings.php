<?php
include getenv("DOCUMENT_ROOT") . "/db.php";
$MetaDescription = "The official source for standings on the XLL. Get the current XLL standings now!";

function getStandings($divID, $headerText) {
    $tableHTML = "<h1 class='pageHeaderText'>" . $headerText . "</h1>"
               . "<div class='table-wrapper'>"
               .    "<table class='small'>"
               .        "<thead>"
               .            "<tr><th>Team</th><th>W</th><th>L</th><th>Pts</th><th>GF</th><th>GA</th></tr>"
               .        "</thead>"
               .        "<tbody>";

    $query = "SELECT T.Name, S.Wins, S.Losses, S.Points, S.GF, S.GA "
           . "FROM standings S "
           . "INNER JOIN teams T on T.ID = S.TeamID "
           . "WHERE T.DivisionID = ? "
           . "ORDER BY Points DESC, Wins DESC, GDiff DESC, Losses ASC, Name ASC ";
    $types = "d";
    $params = array($divID);
    $stmt = DB::getInstance()->makeQuery($query, $types, $params);
    $stmt->bind_result($name, $wins, $losses, $points, $gf, $ga);
    while ($stmt->fetch()) {
        $tableHTML.= "<tr>"
                   .    "<td>" . $name . "</td>"
                   .    "<td>" . $wins . "</td>"
                   .    "<td>" . $losses . "</td>"
                   .    "<td>" . $points . "</td>"
                   .    "<td>" . $gf . "</td>"
                   .    "<td>" . $ga . "</td>"
                   . "</tr>";
    }
    $stmt->close();

    $tableHTML.= "</tbody></table></div>";

    return $tableHTML;
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