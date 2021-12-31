<?php
include getenv("DOCUMENT_ROOT") . "/admin/components/logged-in.php";
include getenv("DOCUMENT_ROOT") . "/admin/components/content-helper.php";

$html = "";
$errorMsg = "";
$alertMsg = "";
if (!empty($_POST["contentSubmit"])) {
    $instagramFeatured = isset($_POST["instagramFeatured"]) ? $_POST["instagramFeatured"] : null;
    
    if ($instagramFeatured) {
        $xml = simplexml_load_file(getenv("DOCUMENT_ROOT") . "/config/socialConfig.xml");
        
        $edit = $xml->xpath("/socialmedia/instagram");
        $edit[0]->featured = $instagramFeatured;
        
        $xml->saveXML(getenv("DOCUMENT_ROOT") . "/config/socialConfig.xml");
        
        $alertMsg = "Social Media hooks were successfully updated. ";
    }
    else {
        $errorMsg = "Missing information. Please fill out form fully before submitting. ";
    }
}
if (!empty($errorMsg)) {
    $html.= "<div id='errorMsg'>{$errorMsg}</div><br/>";
}
if (!empty($alertMsg)) {
    $html.= "<div id='alertMsg'>{$alertMsg}</div><br/>";
}

$xml = json_decode(json_encode(simplexml_load_file(getenv("DOCUMENT_ROOT") . "/config/socialConfig.xml")), true);
$instagramFeatured = !empty($xml["instagram"]["featured"]) ? $xml["instagram"]["featured"] : "";

$html.= "<form id='contentForm' action='social-featured' method='post' enctype='multipart/form-data'>"
      .     "<h1>Social Media Links</h1>"
      .     "<div class='contentColumn'>"
      .         "<div class='contentRow'>" . renderInputField("instagramFeatured", "text", $instagramFeatured, "Instagram Featured") . "</div>"
      .     "</div>"
      .     renderSubmitBtn("contentSubmit", "Submit")
      . "</form>";


include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>