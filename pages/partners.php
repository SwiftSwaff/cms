<?php
$MetaDescription = "Gathering of all the official partners/sponsors of the XLL.";

function getPartnersHTML() {
    $html = "";
    $xml = simplexml_load_file(getenv("DOCUMENT_ROOT") . "/config/partnersConfig.xml");
    if ($xml->count() > 0) {
        $html.= "<h1 class='pageHeaderText'>Our Partners</h1>"
              . "<div id='partnersWrapper'>";
        foreach ($xml->images as $image) {
            $filename    = $image->filename;
            $fallback    = $image->{'fallback-ext'};
            $partnerName = $image->{'partner-name'};
            $partnerLink = $image->{'partner-link'};
            //$alt = $image->alt;

            $filepath = getenv("DOCUMENT_ROOT") . "/resources/uploads/images/{$filename}";
            $relpath = "/resources/uploads/images/{$filename}";
            if (file_exists($filepath . "." . $fallback)) {
                $html.= "<div class='partner-elem'>"
                      .     "<a href='{$partnerLink}'>"
                      .         "<picture>"
                      .             "<source srcset='{$relpath}.webp' type='image/webp'>"
                      .             "<img src='{$relpath}.png' alt='{$partnerName}' width=200 height=200>"
                      .         "</picture>"
                      .         "<span>{$partnerName}</span>"
                      .     "</a>"
                      . "</div>";
            }
        }
        $html.= "</div>";
    }
   
    return $html;
}

$main = getPartnersHTML();
?>
