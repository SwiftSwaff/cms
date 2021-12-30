<?php
include_once getenv("DOCUMENT_ROOT") . "/admin/components/logged-in.php";
include_once getenv("DOCUMENT_ROOT") . "/admin/components/content-helper.php";

function parseXML($filename) {
    $prevFilenames = array();
    $prevImagesXML = array();
    $xml = json_decode(json_encode(simplexml_load_file(getenv("DOCUMENT_ROOT") . "/config/" . $filename)), true);
    if (!empty($xml["images"])) {
        if (isset($xml["images"]["filename"])) {
            array_push($prevFilenames, $xml["images"]["filename"]);
            $prevImagesXML[$xml["images"]["filename"]] = $xml["images"];
        }
        else {
            foreach ($xml["images"] as $image) {
                array_push($prevFilenames, $image["filename"]);
                $prevImagesXML[$image["filename"]] = $image;
            }
        }
    }
    return array("XML" => $prevImagesXML, "Filenames" => $prevFilenames);
}

function getImages() {
    $includeTypes = array('jpg', 'jpeg', 'png');
    $rdi = new RecursiveDirectoryIterator(getenv("DOCUMENT_ROOT") . "/resources/uploads/images/", RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveCallbackFilterIterator($rdi, function($file, $key, $iterator) use ($includeTypes) {
        if ($iterator->hasChildren()) {
            return true;
        }
        
        $temp = explode(".", $file->getFilename());
        $type = end($temp);
        if (in_array($type, $includeTypes)) {
            return true;
        }
        return false;
    });

    return $files;
}

function getGalleryOptions() {
    $xmlArr = parseXML("galleryConfig.xml");

    $counter = 0;
    $imageOptions = "<div class='imageSelect'><table class='imageSelect-tbl'>"
                  . "<thead><tr><th>Preview</th><th>Filename</th><th>Use?</th><th>Order</th></tr></thead><tbody>";

    $it = new RecursiveIteratorIterator(getImages());
    foreach ($it as $filepath) {
        $strippedFilepath = str_replace("\\", "/", $filepath);
        $strippedFilepath = str_replace(getenv("DOCUMENT_ROOT") . "/resources/uploads/images/", "", $strippedFilepath);

        $baseFilenameArr = explode(".", $strippedFilepath);
        if (end($baseFilenameArr) == "webp") {
            continue;
        }

        $checked = "";
        $order = "1";
        
        if (in_array($baseFilenameArr[0], $xmlArr["Filenames"])) {
            $checked = "checked";
            $order = $xmlArr["XML"][$baseFilenameArr[0]]["order"];
        }

        $filepath = str_replace("\\", "/", $filepath);
        $imgSrc = str_replace(getenv("DOCUMENT_ROOT"), "", $filepath);
        
        $imageOptions.= "<tr>"
                      .     "<td><img src='{$imgSrc}' height=100></td>"
                      .     "<td><label for='image{$counter}'>{$strippedFilepath}</label></td>"
                      .     "<td><input type='checkbox' id='image{$counter}' name='images[{$counter}]' value='{$strippedFilepath}' {$checked}></td>"
                      .     "<td><input type='number' id='order{$counter}' name='order[{$counter}]' value='{$order}'></td>"
                      . "</tr>";
        
        $counter++;
    }
    $imageOptions.= "</tbody></table></div>";

    return $imageOptions;
}

function getSliderOptions() {
    $xmlArr = parseXML("sliderConfig.xml");
    $counter = 0;
    $imageOptions = "<div class='imageSelect'><table class='imageSelect-tbl'>"
                  . "<thead><tr><th>Preview</th><th>Filename</th><th>Use?</th><th>Order</th></tr></thead><tbody>";

    $it = new RecursiveIteratorIterator(getImages());
    foreach ($it as $filepath) {
        $strippedFilepath = str_replace("\\", "/", $filepath);
        $strippedFilepath = str_replace(getenv("DOCUMENT_ROOT") . "/resources/uploads/images/", "", $strippedFilepath);

        $baseFilenameArr = explode(".", $strippedFilepath);
        if (end($baseFilenameArr) == "webp") {
            continue;
        }
        
        $checked = "";
        $order = "1";
        
        if (in_array($baseFilenameArr[0], $xmlArr["Filenames"])) {
            $checked = "checked";
            $order = $xmlArr["XML"][$baseFilenameArr[0]]["order"];
        }

        $filepath = str_replace("\\", "/", $filepath);
        $imgSrc = str_replace(getenv("DOCUMENT_ROOT"), "", $filepath);
        
        $imageOptions.= "<tr>"
                      .     "<td><img src='{$imgSrc}' height=100></td>"
                      .     "<td><label for='image{$counter}'>{$strippedFilepath}</label></td>"
                      .     "<td><input type='checkbox' id='image{$counter}' name='images[{$counter}]' value='{$strippedFilepath}' {$checked}></td>"
                      .     "<td><input type='number' id='order{$counter}' name='order[{$counter}]' value='{$order}'></td>"
                      . "</tr>";
        
        $counter++;
    }
    $imageOptions.= "</tbody></table></div>";

    return $imageOptions;
}

function getPartnerOptions() {
    $xmlArr = parseXML("partnersConfig.xml");

    $counter = 0;
    $imageOptions = "<div class='imageSelect'><table class='imageSelect-tbl'>"
                  . "<thead><tr><th>Preview</th><th>Filename</th><th>Use?</th><th>Order</th><th>Partner Name</th><th>Partner Link</th></tr></thead><tbody>";

    $it = new RecursiveIteratorIterator(getImages());
    foreach ($it as $filepath) {
        $strippedFilepath = str_replace("\\", "/", $filepath);
        $strippedFilepath = str_replace(getenv("DOCUMENT_ROOT") . "/resources/uploads/images/", "", $strippedFilepath);

        $baseFilenameArr = explode(".", $strippedFilepath);
        if (end($baseFilenameArr) == "webp") {
            continue;
        }

        $checked = "";
        $order = "1";
        $partnerName = "";
        $partnerLink = "";

        if (in_array($baseFilenameArr[0], $xmlArr["Filenames"])) {
            $checked = "checked";
            $partnerName = $xmlArr["XML"][$baseFilenameArr[0]]["partner-name"];
            $partnerLink = $xmlArr["XML"][$baseFilenameArr[0]]["partner-link"];
            $order = $xmlArr["XML"][$baseFilenameArr[0]]["order"];
        }

        $filepath = str_replace("\\", "/", $filepath);
        $imgSrc = str_replace(getenv("DOCUMENT_ROOT"), "", $filepath);
        
        $imageOptions.= "<tr>"
                      .     "<td><img src='{$imgSrc}' height=100></td>"
                      .     "<td><label for='image{$counter}'>{$strippedFilepath}</label></td>"
                      .     "<td><input type='checkbox' id='image{$counter}' name='images[{$counter}]' value='{$strippedFilepath}' {$checked}></td>"
                      .     "<td><input type='number' id='order{$counter}' name='order[{$counter}]' value='{$order}'></td>"
                      .     "<td><input type='text' id='partner{$counter}' name='partner[{$counter}]' value='{$partnerName}' placeholder='Enter Partner Name...'></td>"
                      .     "<td><input type='text' id='link{$counter}' name='link[{$counter}]' value='{$partnerLink}' placeholder='Enter Link Name...'></td>"
                      . "</tr>";
        
        $counter++;
    }
    $imageOptions.= "</tbody></table></div>";

    return $imageOptions;
}
?>