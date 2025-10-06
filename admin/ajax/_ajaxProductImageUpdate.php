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
        $Path = USER_UPLOADS.'/images/products/';
        $i = 1;

        if ($ProductID <= 0) { die(); }

        //Create object
        $Img = new ImageLibrary('Products', $ProductID, null, 1200, 360, $Path);

        foreach ($_POST['Image'] as $value) {
            $Img->getImageById($value);
            $Img->setDisplayOrder($i);
            $Img->saveImageItem();
            $i++;
        }

        $ret_arr = array('Success'=>'Product image order updated', 'full'=>'');

        echo json_encode($ret_arr);

    }