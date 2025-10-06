<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\CMS\ImageLibrary;

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkAdmin($_SERVER['PHP_SELF'], 'E');


        $ProductID = clean_int($_POST['ProductID']);


        //Check some variables are present before proceeding
        if ($ProductID <= 0 || !isset($_POST['ImgFile']))
        {
            die();
        }

        //Artificially sort DisplayOrder
        if ($DisplayOrder <= 0)
        {
            $DisplayOrder = 1;
        }

        $Path = USER_UPLOADS.'/images/products/';

        //Create object
        $Img = new ImageLibrary('Products', $ProductID, null, 1200, 1200, $Path);

        $Img->createImageItem();
        //$Img->setCaption($Caption);
        $Img->setDisplayOrder($DisplayOrder);
        $Img->processImage($_POST['ImgFile']);

        $Img->saveImageItem();

        $newID = $Img->getID();
        $newFilename = $Img->getImgFilename();

        $output = "<li class='prod-thumb' id='Image_".$newID."' data-imageid='".$newID."' data-contentid='".$ProductID."'><img src='".FixOutput($Path."small/".$newFilename)."' alt='".FixOutput($newFilename)."' title='".FixOutput($newFilename)."' data-imageid='".$newID."' data-contentid='".$ProductID."' /><div class='prod-thumb-del' data-imageid='".$newID."'><img src='/assets/img/icons/icon_close.png' alt='Delete' width='20' class='prod-thumb-del-img' /></div></li>";

        $ret_arr = array('Success'=>'Product image uploaded', 'full'=>$output);

        echo json_encode($ret_arr);

    }