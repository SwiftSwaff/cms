<?php
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

    function parseLeagueDivisions(...$vars) {
        $divisions = array();
        $query = "SELECT ID, Name FROM divisions WHERE IsActive = '1'";
        $stmt = DB::getInstance()->makeQuery($query);
        $stmt->bind_result($divID, $divName);
        while ($stmt->fetch()) {
            $content = array("Name" => $divName);
            foreach ($vars as $var) {
                $content[$var] = "";
            }
            
            $divisions[$divID] = $content;
        }
        $stmt->close();

        return $divisions;
    }

    function parseDivisionSelector() {
        if ($_SESSION["DivisionID"] != 0) {
            return ""; // don't render the selector when user has limited permission
        }
        
        $viewSelectButtons = "";
        $first = true;
        $query = "SELECT ID, Name FROM divisions WHERE IsActive = '1'";
        $stmt = DB::getInstance()->makeQuery($query);
        $stmt->bind_result($divID, $divName);
        while ($stmt->fetch()) {
            $active = "";
            if ($first) {
                $active = "active";
                $first = false;
            }
            $viewSelectButtons.= "<button class='viewSelect-btn {$active}' onclick='changeDivisionView(this, {$divID});'>{$divName}</button>";
        }
        $stmt->close();
        
        return "<div class='viewSelect'><span>Select a Division: </span>{$viewSelectButtons}</div>";
    }
?>