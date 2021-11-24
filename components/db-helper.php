<?php
if (!defined('IndexAccessed')) {
    die('Direct access not permitted');
}
include getenv("DOCUMENT_ROOT") . "/db.php";

function getUpcomingSchedule() {
    $tableHTML = "<h1>Upcoming Schedule</h1>"
               . "<div class='table-wrapper'>"
               .    "<table>"
               .        "<thead>"
               .            "<tr><th>Time</th><th colspan='2'>Home</th><th colspan='2'>Away</th></tr>"
               .        "</thead>"
               .        "<tbody>";
    $query = "SELECT S.Time, H.Name, S.HomeTeamScore, A.Name, S.AwayTeamScore "
           . "FROM schedule S "
           . "INNER JOIN teams H on H.ID = S.HomeTeamID "
           . "INNER JOIN teams A on A.ID = S.AwayTeamID "
           . "WHERE S.Complete = 'No' "
           . "ORDER BY Time ASC ";
    $stmt = DB::getInstance()->makeQuery($query);
    $stmt->bind_result($time, $homeTeam, $homeScore, $awayTeam, $awayScore);
    while ($stmt->fetch()) {
        $dateStr = date("M jS g:i A", strtotime($time));
        $tableHTML.= "<tr>"
                   .    "<td>" . $dateStr . "</td>"
                   .    "<td>" . $homeTeam . "</td>"
                   .    "<td>" . $homeScore . "</td>"
                   .    "<td>" . $awayTeam . "</td>"
                   .    "<td>" . $awayScore . "</td>"
                   . "</tr>";
    }
    $stmt->close();

    $tableHTML.= "</tbody></table></div>";

    return $tableHTML;
}

function getCompletedGames() {
    $tableHTML = "<h1>Completed Games</h1>"
               . "<div class='table-wrapper'>"
               .    "<table>"
               .        "<thead>"
               .            "<tr><th>Time</th><th colspan='2'>Home</th><th colspan='2'>Away</th></tr>"
               .        "</thead>"
               .        "<tbody>";
    $query = "SELECT S.Time, H.Name, S.HomeTeamScore, A.Name, S.AwayTeamScore "
           . "FROM schedule S "
           . "INNER JOIN teams H on H.ID = S.HomeTeamID "
           . "INNER JOIN teams A on A.ID = S.AwayTeamID "
           . "WHERE S.Complete = 'Yes' "
           . "ORDER BY Time ASC ";
    $stmt = DB::getInstance()->makeQuery($query);
    $stmt->bind_result($time, $homeTeam, $homeScore, $awayTeam, $awayScore);
    while ($stmt->fetch()) {
        $dateStr = date("M jS g:i A", strtotime($time));
        $tableHTML.= "<tr>"
                   .    "<td>" . $dateStr . "</td>"
                   .    "<td>" . $homeTeam . "</td>"
                   .    "<td>" . $homeScore . "</td>"
                   .    "<td>" . $awayTeam . "</td>"
                   .    "<td>" . $awayScore . "</td>"
                   . "</tr>";
    }
    $stmt->close();

    $tableHTML.= "</tbody></table></div>";

    return $tableHTML;
}

/*
function getEventHTML($id) {
    $html = "";

    $query = "SELECT Title, Content, Date FROM events WHERE ID = ?";
    $types = "d";
    $params = array($id);

    $stmt = DB::getInstance()->makeQuery($query, $types, $params);
    $stmt->bind_result($title, $content, $date);
    while ($stmt->fetch()) {
        $html.= "<h1>" . $title . "</h1>"
              . "<h3>" . date("jS M Y h:i:A", strtotime($date)) . "</h3>"
              . "<hr style='margin-bottom: 20px;'>"
              . html_entity_decode($content, ENT_COMPAT, "UTF-8");
    }

    return $html;
}
*/
?>