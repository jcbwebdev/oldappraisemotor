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
        $id = clean_int($_POST['imageid']);
        $Path = USER_UPLOADS.'/images/products/';

        if ($id <= 0 || $ProductID <= 0) { die(); }

        //Create object
        $Img = new ImageLibrary('Products', $ProductID, $id, 1200, 360, $Path);
        $Img->deleteItem();

        $ret_arr = array('Success'=>'Product image deleted', 'full'=>'');

        echo json_encode($ret_arr);

    }