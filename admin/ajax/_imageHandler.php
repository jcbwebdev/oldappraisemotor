<?php
    /*
        This page should only ever be opened as part of an ajax request

        If its opened any other way - it should fail quietly
    */

    use PeterBourneComms\CMS\Member;
    use PeterBourneComms\CMS\Carousel;
    use PeterBourneComms\CMS\HomePanel;
    use PeterBourneComms\CMS\Content;
    use PeterBourneComms\CMS\News;
    use PeterBourneComms\Ecommerce\ProductCategory;
    use PeterBourneComms\Ecommerce\Product;
    use PeterBourneComms\CMS\SupportingPartner;
    use PeterBourneComms\CCA\Customer;
    use PeterBourneComms\CCA\AuctionRoom;


    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");
        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);

        //Script works with thr ImageHandler class to delete profile images and top-of-page images on the fly


        //Get variables from POST
        $ContentID = clean_int($_POST['ContentID']);
        $ContentType = $_POST['ContentType'];
        $ImagePath = $_POST['ImagePath'];
        $Thumbnails = $_POST['Thumbnails'];

        if ($ContentID <= 0 || $ContentType == '' || $ImagePath == '')
        {
            exit;
        }


        //ContentType determines the action we take
        switch($ContentType)
        {
            case 'member-avatars':
                $Obj = new Member($ContentID,0,0,$ImagePath);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;

            case 'carousel':
                $Obj = new Carousel($ContentID);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;

            case 'home-panels':
                $Obj = new HomePanel($ContentID,0,0,$ImagePath);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;

            case 'content':
                $Obj = new Content($ContentID,false,0, 0, $ImagePath);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;

            case 'news':
                $Obj = new News($ContentID,0, 0, $ImagePath);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;

            case 'product-category':
                $Obj = new ProductCategory($ContentID,0, 0, $ImagePath);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;

            case 'product':
                $Obj = new Product($ContentID,0, 0, $ImagePath);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;
    
            case 'partner':
                $Obj = new SupportingPartner($ContentID,0, 0, $ImagePath);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;
                
            case 'customer':
                $Obj = new Customer($ContentID,0, 0, $ImagePath);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;
                
            case 'auction-room':
                $Obj = new AuctionRoom($ContentID,0, 0, $ImagePath);
                $Obj->deleteImage();
                $Obj->saveItem();
                break;

            default:
                die();
        }

    }