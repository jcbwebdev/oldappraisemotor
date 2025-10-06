<?php
    include("../../assets/dbfuncs.php");
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

    use PeterBourneComms\CMS\ImageLibrary;

    $id = clean_int($_POST['imageid']);
    $contentid = clean_int($_POST['contentid']);
    $contenttype = $_POST['contenttype'];

    if ($id < 0)
    {
        //header("Location: /admin/");
        exit;
    }

    //Delete
    $ImgDel = new ImageLibrary($contenttype, $contentid, $id, null, null, USER_UPLOADS.'/images/gallery/');

    $ImgDel->deleteItem();
?>