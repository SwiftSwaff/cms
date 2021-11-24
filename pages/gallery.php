<?php
$MetaDescription = "The official source for professional photography on the XLL.";

function getGalleryHTML() {
    $html = "";
    $xml = simplexml_load_file(getenv("DOCUMENT_ROOT") . "/config/galleryConfig.xml");
    if ($xml->count() > 0) {
        $html.= "<h1 class='pageHeaderText'>Gallery</h1>"
              . "<div id='galleryWrapper'>";
        foreach ($xml->images as $image) {
            $filename = $image->filename;
            $fallback = $image->{'fallback-ext'};
            //$alt = $image->alt;

            $filepath = getenv("DOCUMENT_ROOT") . "/resources/uploads/images/{$filename}";
            $relpath = "/resources/uploads/images/{$filename}";
            if (file_exists($filepath . "." . $fallback)) {
                $html.= "<div class='gallery-elem'>"
                      .     "<picture>"
                      .         "<source srcset='{$relpath}.webp' type='image/webp'>"
                      .         "<img class='gallery-img' alt='{$filename}' src='{$relpath}.{$fallback}'>"
                      .     "</picture>"
                      . "</div>";
            }
        }
        $html.= "</div>";
    }

    return $html;
}

function getOverlayHTML() {
    $closeBtnIcon = "/resources/closeicon.png";

    $html = "<div id='pageOverlay'>"
          . "<a href='javascript: void(0)' class='closeBtn'></a>"
          .     "<div id='overlayWrapper'>"
          .     "</div>"
          . "</div>";

    return $html;
}

$main = getGalleryHTML() . getOverlayHTML();
?>