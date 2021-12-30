<?php
include getenv("DOCUMENT_ROOT") . "/admin/components/logged-in.php";
include getenv("DOCUMENT_ROOT") . "/admin/components/content-helper.php";
require_once getenv("DOCUMENT_ROOT") . "/db/db.php";

$html = "";
$errorMsg = "";
$alertMsg = "";
if (!empty($_POST["contentSubmit"])) {
    $facebookURL = isset($_POST["facebookURL"]) ? $_POST["facebookURL"] : null;
    $twitterURL = isset($_POST["twitterURL"]) ? $_POST["twitterURL"] : null;
    $instagramURL = isset($_POST["instagramURL"]) ? $_POST["instagramURL"] : null;
    
    if ($facebookURL && $twitterURL && $instagramURL) {
        $xml = new SimpleXMLElement('<socialmedia/>');
        
        $xmlFacebook = $xml->addChild("facebook");
        $xmlFacebook->addChild("url", $facebookURL);
        $xmlTwitter = $xml->addChild("twitter");
        $xmlTwitter->addChild("url", $twitterURL);
        $xmlInstagram = $xml->addChild("instagram");
        $xmlInstagram->addChild("url", $instagramURL);
        $xmlInstagram->addChild("featured", "");
        
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
$facebookURL = !empty($xml["facebook"]["url"]) ? $xml["facebook"]["url"] : "";
$twitterURL = !empty($xml["twitter"]["url"]) ? $xml["twitter"]["url"] : "";
$instagramURL = !empty($xml["instagram"]["url"]) ? $xml["instagram"]["url"] : "";

$html.= "<form id='contentForm' action='social-media' method='post' enctype='multipart/form-data'>"
      .     "<h1>Social Media Links</h1>"
      .     "<div class='contentColumn'>"
      .         "<div class='contentRow'>" . renderInputField("facebookURL", "text", $facebookURL, "Facebook") . "</div>"
      .         "<div class='contentRow'>" . renderInputField("twitterURL", "text", $twitterURL, "Twitter") . "</div>"
      .         "<div class='contentRow'>" . renderInputField("instagramURL", "text", $instagramURL, "Instagram") . "</div>"
      .     "</div>"
      .     renderSubmitBtn("contentSubmit", "Submit")
      . "</form>";


include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>