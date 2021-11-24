<?php
if (!defined('IndexAccessed')) {
    die('Direct access not permitted');
}
function createSlider($xmlConfigFilename, $sliderID, $sliderJSFile) {
    $xml = simplexml_load_file(getenv("DOCUMENT_ROOT") . "/config/" . $xmlConfigFilename);
    if ($xml->count() == 0) {
        echo "";
    }
    else {
        $html = "<div id='" . $sliderID . "' class='keen-slider'>";
        foreach ($xml->images as $image) {
            $filename = $image->filename;
            $fallback = $image->{'fallback-ext'};
            //$alt = $image->alt;
    
            $filepath = getenv("DOCUMENT_ROOT") . "/resources/uploads/images/{$filename}";
            $relpath = "/resources/uploads/images/{$filename}";
            if (file_exists($filepath . "." . $fallback)) {
                $html.= "<div class='keen-slider__slide'>"
                      .     "<picture>"
                      .         "<source srcset='{$relpath}.webp' type='image/webp'>"
                      .         "<img src='{$relpath}.{$fallback}'>"
                      .     "</picture>"
                      . "</div>";
            }
        }
    
        $html.= "</div>"
              . "<script src='/plugins/node_modules/keen-slider/keen-slider.js'></script>"
              . "<script src='/js/{$sliderJSFile}'></script>";
        echo $html;
    }
}
?>