<?php
include getenv("DOCUMENT_ROOT") . "/admin/components/image-helper.php";

$html = "";
$errorMsg = "";
$alertMsg = "";
if (!empty($_POST["contentSubmit"])) {
    $xmlData = array();
    
    foreach ($_POST["images"] as $index => $filename) {
        $filenameArr = explode(".", $filename);
        $name = $filenameArr[0];
        $extension = $filenameArr[1];
        $xmlData[] = array(
            "filename"     => $name,
            "fallback-ext" => $extension,
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
    
    $xml = new SimpleXMLElement('<galleryimages/>');
    foreach ($xmlData as $data) {
        if (!file_exists(getenv("DOCUMENT_ROOT") . "/resources/uploads/images/{$data['filename']}.{$data['fallback-ext']}")) {
            $errorMsg = "File {$data["filename"]}.{$data['fallback-ext']} does not exist. ";
        }
        else {
            $xmlImg = $xml->addChild("images");
            $xmlImg->addChild("filename", $data["filename"]);
            $xmlImg->addChild("fallback-ext", $data["fallback-ext"]);
            $xmlImg->addChild("order", $data["order"]);
            //$xmlImg->addChild("alt", "");
        }
    }
    
    $xml->saveXML(getenv("DOCUMENT_ROOT") . "/config/galleryConfig.xml");
    $alertMsg = "Gallery images were successfully updated. ";
}
if (!empty($errorMsg)) {
    $html.= "<div id='errorMsg'>{$errorMsg}</div><br/>";
}
if (!empty($alertMsg)) {
    $html.= "<div id='alertMsg'>{$alertMsg}</div><br/>";
}


$html.= "<form action='gallery' method='post' enctype='multipart/form-data'>"
      .     "<h1>Gallery</h1>"
      .     getGalleryOptions()
      .     "<input name='filename' type='hidden' value='galleryConfig.xml'>"
      .     renderSubmitBtn("contentSubmit", "Submit")
      . "</form>";

include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>