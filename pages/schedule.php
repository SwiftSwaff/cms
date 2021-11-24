<?php
include getenv("DOCUMENT_ROOT") . "/db.php";
$MetaDescription = "The official source for the XLL schedule. Find schedule times and history.";

function getSchedule($divID, $headerText) {
    $tableHTML = "<h1 class='pageHeaderText'>{$headerText}</h1>"
               . "<div class='table-wrapper'>"
               .    "<table class='large'>"
               .        "<thead>"
               .            "<tr><th>#</th><th>Time</th><th colspan='2'>Home</th><th colspan='2'>Away</th></tr>"
               .        "</thead>"
               .        "<tbody>";

    $gameNum = 1;
    $query = "SELECT S.Time, S.IsExhibition, S.Complete, H.Name, S.HomeTeamScore, A.Name, S.AwayTeamScore, H.Home "
           . "FROM schedule S "
           . "INNER JOIN teams H on H.ID = S.HomeTeamID "
           . "INNER JOIN teams A on A.ID = S.AwayTeamID "
           . "WHERE S.DivisionID = ? "
           . "ORDER BY Time ASC ";
    $types = "d";
    $params = array($divID);
    $stmt = DB::getInstance()->makeQuery($query, $types, $params);
    $stmt->bind_result($time, $exhibition, $complete, $homeTeamName, $homeTeamScore, $awayTeamName, $awayTeamScore, $location);
    while ($stmt->fetch()) {
        $dateStr = date("M jS g:i A", strtotime($time));

        $rowStyle = "";
        if ($exhibition && ($complete == "Yes")) {
            $rowStyle = " style='background-color: rgba(255,214,99,0.5);'";
        }
        else if ($exhibition) {
            $rowStyle = " style='background-color: rgba(255,214,99,0.25);'";
        }
        else if ($complete == "Yes") {
            $rowStyle = " style='background-color: rgba(70,140,70,0.5);'";
        }
        else {}

        $tableHTML.= "<tr>"
                   .    "<td" . $rowStyle . ">{$gameNum}</td>"
                   .    "<td" . $rowStyle . ">{$dateStr}</td>"
                   .    "<td" . $rowStyle . ">{$homeTeamName}</td><td" . $rowStyle . "> {$homeTeamScore}</td>"
                   .    "<td" . $rowStyle . ">{$awayTeamName}</td><td" . $rowStyle . "> {$awayTeamScore}</td>"
                   . "</tr>";
        $gameNum++;
    }
    $stmt->close();

    $tableHTML.= "</tbody></table></div>";

    return $tableHTML;
}

$path = ltrim($_SERVER['REQUEST_URI'], '/');
$elements = explode('/', $path);

$page = strtolower($elements[0]);
$param = isset($elements[1]) ? strtolower($elements[1]) : null;
if ($page == "schedule") {
    switch ($param) {
        case "summer2021":
            $main.= getSchedule(1, "Summer 2021 Schedule");
            $main.= "<h6 style='text-align: center; margin-top: 10px;'><i>*All games played at <a target='_blank' href='https://goo.gl/maps/71gBVtTmb1LFP9L17'>Poirier Sport & Leisure Complex</a></i></h6>";
            //$main.= "<h1 class='headerText'>Schedule coming soon!</h1>";
            break;
        case "winter2021":
            $main.= getSchedule(2, "Winter 2021 Senior Schedule");
            $main.= "<h6 style='text-align: center; margin-top: 10px;'><i>*Games in orange are exhibition games. All games played at <a target='_blank' href='https://goo.gl/maps/71gBVtTmb1LFP9L17'>Poirier Sport & Leisure Complex</a></i></h6>";
            break;
        /*case "league3":
            //$main.= getSchedule(3, "League #3 Schedule");
            $main.= "<h1 class='headerText'>Schedule coming soon!</h1>";
            break;*/
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