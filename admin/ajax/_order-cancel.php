<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\Ecommerce\Order;

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

        include("../../assets/dbfuncs.php");


        /*
         * Cancel an order
         *
         */

        $OrderID = clean_int($_POST['OrderID']);

        $OO = new Order();

        if (!is_object($OO) || clean_int($OrderID) <= 0) {
            echo "1";
            die();
        }

        $Order = $OO->getItemById($OrderID);

        //Some checks
        if (!is_array($Order) || count($Order) < 1) {
            echo "2";
            die();
        }

        if ($Order['Status'] == 'Despatched' || $Order['Status'] == 'Cancelled') {
            echo "4";
            die();
        }

        //Carry out the update
        $OO->setStatus('Cancelled');
        $OO->setStatusDetail('Cancelled by '.$_SESSION['UserDetails']['FullName']." on ".date('Y-m-d H:i:s', time()));
        $success = $OO->saveItem();

        //Reload
        $Order = $OO->getItemById($OrderID);

        if ($success == true) {

            //Now delete the stored image
            //$OO->deleteAllOrderLineImages();
        }


        if ($success != true) {
            $_SESSION['Message'] = array('Type' => 'warning', 'Message' => "There was a problem updating the order. Please try again.");
            $retdata = array('status' => 'error', 'detail' => "Sorry - there was a problem updating the order on the database");
        } else {
            $_SESSION['Message'] = array('Type' => 'success', 'Message' => "The order has been cancelled.");
            $retdata = array('status' => 'success', 'detail' => 'Cancelled');
        }

        echo json_encode($retdata);
    }