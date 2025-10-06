<?php
    include("../../assets/dbfuncs.php");
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CMS\ImageLibrary;


    $Caption = $_POST['Caption'];
    $DisplayOrder = clean_int($_POST['DisplayOrder']);
    $ContentID = clean_int($_POST['ContentID']);
    $ContentType = $_POST['ContentType'];

    //Check some variables are present before proceeding
    if ($ContentID <= 0 || $ContentType == '' || !isset($_POST['ImgFile']))
    {
        echo $output;
        exit;
    }

    //Artificially sort DisplayOrder
    if ($DisplayOrder <= 0)
    {
        $DisplayOrder = 1000;
    }


    //Create object
    $Img = new ImageLibrary($ContentType, $ContentID, null, 1000, 200, USER_UPLOADS.'/images/gallery/');

    $Img->createImageItem();
    $Img->setCaption($Caption);
    $Img->setDisplayOrder($DisplayOrder);
    $Img->processImage($_POST['ImgFile']);

    $Img->saveImageItem();

    $newID = $Img->getID();
    $newFilename = $Img->getImgFilename();


    //Prepare Output
    $output = "<div style=\"display:none;\" class=\"imagelibrary-panel latest-insert\" id=\"Image_" . $newID . "\"><div class=\"imagelibrary-img\"><img src=\"".USER_UPLOADS."/images/gallery/small/" . FixOutput($newFilename) . "\" alt=\"" . FixOutput($Caption) . "\" title=\"" . FixOutput($Caption) . "\" /></div>";
    $output .= "<div class=\"imagelibrary-caption\"><p><label for=\"IM_Caption_" . $newID . "\">Caption:</label><textarea class=\"ajxupd\" data-contentid=\"" . check_output($ContentID) . "\" data-contenttype='" . check_output($ContentType) . "' name=\"IM_Caption_" . $newID . "\" id=\"IM_Caption_" . $newID . "\" data-mode=\"update\" data-field=\"caption\" data-imageid=\"" . $newID . "\">" . check_output($Caption) . "</textarea></p>";
    $output .= " <p><label for=\"IM_DisplayOrder_" . $newID . "\">Display Order:</label><input class=\"ajxupd\" data-contentid=\"" . check_output($ContentID) . "\" data-contenttype='" . check_output($ContentType) . "' type=\"number\" name=\"IM_DisplayOrder_" . $newID . "\" id=\"IM_DisplayOrder_" . $newID . "\" data-mode=\"update\" data-field=\"displayorder\" value=\"" . check_output($DisplayOrder) . "\" data-imageid=\"" . $newID . "\"></p>";
    $output .= "<p class=\"img-del-link activedel\" data-imageid=\"" . $newID . "\" data-contenttype='" . check_output($ContentType) . "' data-contentid='" . check_output($ContentID) . "'>Delete this image</p></div></div>";
    echo $output;