<?php
    /*
        This page should only ever be opened as part of an ajax request

        If its opened any other way - it should fail quietly
    */

    use PeterBourneComms\CMS\ImageLibrary;


    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");
        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

        //Script that updates Image Cpation and Display order - depending on what's passed as POST
        $mode = $_POST['mode'];
        $field = $_POST['field'];
        $datavalue = $_POST['datavalue'];
        $imageid = clean_int($_POST['imageid']);
        $contentid = $_POST['contentid'];
        $contenttype = $_POST['contenttype'];


        if ($contentid <= 0 || $imageid <= 0)
        {
            //header("Location: /admin/");
            exit;
        }

        try {
            $UpdImg = new ImageLibrary($contenttype, $contentid, $imageid, 1000, 200, USER_UPLOADS.'/images/gallery/' );
        } catch (Exception $e) {
            error_log("Failed to create ImageLibrary object" . $e);
        }

        //Update existing record
        if ($field == 'caption')
        {
            $UpdImg->setCaption($datavalue);
        }
        elseif ($field == 'displayorder')
        {
            $UpdImg->setDisplayOrder($datavalue);
        }
        $UpdImg->saveImageItem();


        unset($UpdImg);
    }
?>