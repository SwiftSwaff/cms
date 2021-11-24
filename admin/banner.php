<?php
    $bannerDir = "../resources/banner-image.png";

    if (isset($_FILES["image"]["name"])) {
        move_uploaded_file($_FILES["image"]["tmp_name"], $bannerDir);
        $src = imagecreatefrompng($bannerDir);
        
        list($w, $h) = getimagesize($bannerDir);
        $maxw = 800;
        $maxh = 400;
        $tw = $w;
        $th = $h;
        if ($w > $h && $maxw < $w) {
            $th = $maxh / $w * $h;
            $tw = $maxw;
        }
        elseif ($h > $w && $maxh < $h) {
            $tw = $maxw / $h * $w;
            $th = $maxh;
        }
        elseif ($maxw < $w) {
            $tw = $maxw;
        }
        elseif ($maxh < $h) {
            $th = $maxh;
        }
    }
    
    $html = '<form action="banner.php" method="post" enctype="multipart/form-data">'
          .     '<label for="banner-image">Select image:</label>'
          .     '<input type="file" id="image" name="image" accept="png/*">'
          .     '<input type="submit">'
          . '</form>';

    include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>