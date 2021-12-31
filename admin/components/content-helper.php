<?php
require_once getenv("DOCUMENT_ROOT") . "/db/db.php";

function renderInputField($name, $type, $value, $labelText = "") {
    $label = ($labelText != "") ? "<label for='{$name}'>{$labelText}</label>" : "";
    $input = "<input type='{$type}' id='{$name}' name='{$name}' value='{$value}'>";
    return $label . $input;
}

function renderCheckbox($name, $value, $labelText = "") {
    $label = ($labelText != "") ? "<label for='{$name}'>{$labelText}</label>" : "";
    $input = "<input type='hidden' name={$name} value='0'>"
            . "<input type='checkbox' id='{$name}' name='{$name}' value='1' {$value}>";
    return $label . $input;
}

function renderSubmitBtn($name, $btnText) {
    $input = "<input type='submit' id='{$name}' name='{$name}' class='submitBtn' value='{$btnText}'>";
    return $input;
}

function returnToPrev($url, $text) {
    return "<div><a href='" . $url . "'>" . $text . "</a></div>";
}

// TODO: Consider re-envisioning this approach, it's too hard to follow
function parseLeagueDivisions(...$vars) {
    $divisions = array();
    $sql = "SELECT id, name 
            FROM divisions 
            WHERE is_active = '1'";
    $result = DB::getInstance()->preparedQuery($sql);
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $content = array("Name" => $name);
        foreach ($vars as $var) { // for each category that is passed into the function
            $content[$var] = "";
        }
        
        $divisions[$id] = $content;
    }

    return $divisions;
}

function parseDivisionSelector() {
    if ($_SESSION["DivisionID"] != 0) {
        return ""; // don't render the selector when user has limited permission
    }
    
    $viewSelectButtons = "";
    $first = true;
    $sql = "SELECT id, name 
            FROM divisions 
            WHERE is_active = '1'";
    $result = DB::getInstance()->preparedQuery($sql);
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $active = "";
        if ($first) {
            $active = "active";
            $first = false;
        }
        $viewSelectButtons.= "<button class='viewSelect-btn {$active}' onclick='changeDivisionView(this, {$id});'>{$name}</button>";
    }
    
    return "<div class='viewSelect'><span>Select a Division: </span>{$viewSelectButtons}</div>";
}
?>