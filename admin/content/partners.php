<?php
include getenv("DOCUMENT_ROOT") . "/admin/components/image-helper.php";

$html = "";
$errorMsg = "";
$alertMsg = "";
if (!empty($_POST["contentSubmit"])) {
    if (!empty($_POST["images"])) {
        $xmlData = array();
        
        foreach ($_POST["images"] as $index => $filename) {
            $filenameArr = explode(".", $filename);
            $name = $filenameArr[0];
            $extension = $filenameArr[1];
            $xmlData[] = array(
                "filename"     => $name,
                "fallback-ext" => $extension,
                "partner-name" => (!empty($_POST["partner"][$index]) ? $_POST["partner"][$index] : $name),
                "partner-link" => (!empty($_POST["link"][$index]) ? $_POST["link"][$index] : "#"),
                "order"        => (!empty($_POST["order"][$index]) ? $_POST["order"][$index] : "1")
            );
        }
        function sort_images($i1, $i2) { 
            if (intval($i1["order"]) == intval($i2["order"])) {
                return 0;
            }
            elseif (intval($i1["order"]) < intval($i2["order"])) {
                return -1;
            }
            else {
                return 1;
            }
        }
        usort($xmlData, 'sort_images');
        
        $xml = new SimpleXMLElement('<partnerimages/>');
        foreach ($xmlData as $data) {
            if (!file_exists(getenv("DOCUMENT_ROOT") . "/resources/uploads/images/{$data['filename']}.{$data['fallback-ext']}")) {
                $errorMsg = "File {$data["filename"]}.{$data['fallback-ext']} does not exist. ";
            }
            else {
                $xmlImg = $xml->addChild("images");
                $xmlImg->addChild("filename", $data["filename"]);
                $xmlImg->addChild("fallback-ext", $data["fallback-ext"]);
                $xmlImg->addChild("partner-name", $data["partner-name"]);
                $xmlImg->addChild("partner-link", $data["partner-link"]);
                $xmlImg->addChild("order", $data["order"]);
            }
        }
        
        $xml->saveXML(getenv("DOCUMENT_ROOT") . "/config/partnersConfig.xml");
        $alertMsg = "Partner images were successfully updated. ";
    }
    else {
        $errorMsg = "No images were selected.";
    }
}
if (!empty($errorMsg)) {
    $html.= "<div id='errorMsg'>{$errorMsg}</div><br/>";
}
if (!empty($alertMsg)) {
    $html.= "<div id='alertMsg'>{$alertMsg}</div><br/>";
}


$html.= "<form action='partners' method='post' enctype='multipart/form-data'>"
      .     "<h1>Partners</h1>"
      .     getPartnerOptions("partnersConfig.xml")
      .     "<input name='filename' type='hidden' value='partnersConfig.xml'>"
      .     renderSubmitBtn("contentSubmit", "Submit")
      . "</form>";

include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>