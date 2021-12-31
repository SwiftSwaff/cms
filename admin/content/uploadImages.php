<?php
include getenv("DOCUMENT_ROOT") . "/admin/components/logged-in.php";
include getenv("DOCUMENT_ROOT") . "/admin/components/content-helper.php";

$GLOBALS["BaseDirectory"] = getenv("DOCUMENT_ROOT") . "/resources/uploads/images";

function compressImage($source, $destination, $quality=80) {
    $info = getimagesize($source);
    $image = resizeImage($source);

	if ($info['mime'] == 'image/jpeg') {
        imagejpeg($image, $destination . ".jpg", $quality);
    }
	elseif ($info['mime'] == 'image/png') {
        imagepng($image, $destination . ".png", $quality);
    }
	imagewebp($image, $destination . ".webp", $quality);
}
function resizeImage($sourceFile) {
    $maxDim = 2040;
    $info = getimagesize($sourceFile);

    if ($info['mime'] == 'image/jpeg') {
        $sourceImage = imagecreatefromjpeg($sourceFile);
    }
    elseif ($info['mime'] == 'image/png') {
        $sourceImage = imagecreatefrompng($sourceFile);
    }
    $sourceImageX = imagesx($sourceImage);
    $sourceImageY = imagesy($sourceImage);

    $resizeX = 0;
    $resizeY = 0;
    if ($sourceImageX >= $sourceImageY) {
        if ($sourceImageX > $maxDim) {
            $resizeX = $maxDim;
            $resizeY = ($maxDim / $sourceImageX) * $sourceImageY;
        }
        else {
            return $sourceImage;
        }
    }
    else {
        if ($sourceImageY > $maxDim) {
            $resizeX = ($maxDim / $sourceImageY) * $sourceImageX;
            $resizeY = $maxDim;
        }
        else {
            return $sourceImage;
        }
    }

    $resizedImage = imagecreatetruecolor($resizeX, $resizeY);
    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0,
                       $resizeX, $resizeY, $sourceImageX, $sourceImageY);

    return $resizedImage;
}

function getUploadTargetDirectory($targetFolder) {
    global $BaseDirectory;

    if ($targetFolder == "/") {
        return $BaseDirectory . "/";
    }

    if (!file_exists("{$BaseDirectory}{$targetFolder}")) {
        mkdir("{$BaseDirectory}{$targetFolder}", 0777, true);
    }
    
    return $BaseDirectory . $targetFolder . "/";
}

function getImageSubdirectories() {
    global $BaseDirectory;
    
    $datalistHTML = "<label for='subdirectory-choice'>Save Directory: </label>"
                  . "<input list='subdirectories' id='subdirectory-choice' name='subdirectory-choice' autocomplete='off' placeholder='Leave Blank for Root'>"
                  . "<datalist id='subdirectories'>";
    $subDirs = glob($BaseDirectory . '/*', GLOB_ONLYDIR);
    
    foreach ($subDirs as $subDir) {
        $subDirName = basename($subDir);
        $datalistHTML.= "<option value='{$subDirName}'>";
    }
    $datalistHTML.= "</datalist>";

    return $datalistHTML;
}

$html = "";
$alertMsg = "";
$errorMsg = "";
if (!empty($_POST["contentSubmit"])) {
    if (empty($_FILES["image"]["name"])) {
        $errorMsg.= "No image was selected to upload.";
    }
    else {
        $uploadOk = 1;

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $errorMsg.= "File is not an image. ";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        $validFormats = array("image/jpeg", "image/png");
        if (!in_array($_FILES["image"]["type"], $validFormats)) {
            $errorMsg.= "Only JPG, JPEG, & PNG files are allowed. ";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 1) { // if everything is ok, try to upload file
            $target_dir = getUploadTargetDirectory("/" . $_POST["subdirectory-choice"]);
            $target_name = isset($_POST["image-name"]) ? $_POST["image-name"] : explode(".", basename($_FILES["image"]["name"]))[0];

            compressImage($_FILES["image"]['tmp_name'], $target_dir . $target_name);
            $alertMsg.= "The file '". htmlspecialchars($target_name) . "' has been uploaded. ";
        }
    }
}
if (!empty($errorMsg)) {
    $html.= "<div id='errorMsg'>{$errorMsg}</div><br/>";
}
if (!empty($alertMsg)) {
    $html.= "<div id='alertMsg'>{$alertMsg}</div><br/>";
}

$html.= "<form id='contentForm' action='uploadImages' method='post' enctype='multipart/form-data'>"
      .     "<h1>Upload Image</h1>"
      .     "<div class='contentRow twoCol'>"
      .         "<label for='upload-btn'>Find Image: </label>"
      .         "<label class='upload-btn' name='upload-btn' id='upload-btn' for='file' style='cursor: pointer;'>Browse...</label>"
      .     "</div>"
      .     "<div class='contentRow twoCol'>"
      .         "<label for=''>Name</label>"
      .         "<input type='text' name='image-name' id='image-name' value='' placeholder='Enter Image Name...'>"
      .     "</div>"
      .     "<div class='contentRow twoCol'>" . getImageSubdirectories() . "</div>"
      .     "<div class='contentRow'>"
      .         "<input type='file' accept='image/jpeg, image/png' name='image' id='file' onchange='loadFile(event)' style='display: none;'>"
      .         "<img id='upload-preview'>"
      .     "</div>"
      .     "<div class='contentRow'>" . renderSubmitBtn("contentSubmit", "Submit") . "</div>"
      . "</form>"
      . "<script type='text/javascript'>"
      . "var loadFile = function(event) {"
      .     "var image = document.getElementById('upload-preview');"
      .     "image.src = URL.createObjectURL(event.target.files[0]);"
      .     "var name = document.getElementById('image-name');"
      .     "name.value = event.target.files[0]['name'].split('.')[0];"
      . "};"
      . "</script>";

include getenv("DOCUMENT_ROOT") . "/admin/template.php";
?>