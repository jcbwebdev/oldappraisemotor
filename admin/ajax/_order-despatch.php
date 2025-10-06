<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\CMS\PBEmail;
    use PeterBourneComms\Ecommerce\Order;

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

        include("../../assets/dbfuncs.php");


        /*
         * Set an order to despatched
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

        //Need to check if the order amount we have stored matches what was sent into PayPal.
        if ($Order['Status'] == 'Despatched' || $Order['Status'] == 'Cancelled') {
            echo "4";
            die();
        }

        //Carry out the update
        $DespatchDate = date('Y-m-d H:i:s', time());
        $OO->setStatus('Despatched');
        $OO->setStatusDetail('Despatched by '.$_SESSION['UserDetails']['FullName']." on ".$DespatchDate);
        $OO->setDespatched('Y');
        $OO->setDespatchDate($DespatchDate);
        $success = $OO->saveItem();

        //Reload
        $Order = $OO->getItemById($OrderID);

        if ($success == true) {
            //Send email
            include(DOCUMENT_ROOT . "/assets/inc-basket.php");

            //Prepare email
            $subject = "Your order has been despatched from " . SITENAME;

            //HTML version
            $html = "<p><strong>Your order with " . SITENAME . "</strong></p>\n";
            $html .= "<p>Dear " . $Order['InvFirstname'] . " " . $Order['InvSurname'] . ",</p>\n";
            $html .= "<p>Invoice number: ".$Order['InvoiceNumber']."</p>\n";
            $html .= "<p>Your order has been despatched. The details of the order are below.</p>\n";
            $html .= "<p>Kind regards,<br/><br/><strong>".SITENAME."</strong><br/><a href='mailto:" . SITEEMAIL . "'>" . SITEEMAIL . "</a></p>\n";

            //Basket contents
            $html .= "<p>Your order:</p>";
            $html .= $OO->outputOrderDetails($Order['OrderDetails'], true);

            //Plain text version
            $text = "Your order with " . SITENAME . "\r\n\r\n";
            $text .= "Dear " . $Order['InvFirstname'] . " " . $Order['InvSurname'] . ",\r\n\r\n";
            $text .= "Invoice number: ".$Order['InvoiceNumber']."\r\n\r\n";
            $text .= "Your order has been despatched. The details of the order are below.\r\n\r\n";
            $text .= "Kind regards,\r\n\r\n".SITENAME."\r\n\r\n" . SITEEMAIL . "\r\n\r\n";


            //Send the email
            $email = new PBEmail();
            $email->setRecipient($Order['InvEmail']);
            $email->setSenderEmail(SITESENDEREMAIL);
            $email->setSenderName(SITENAME);
            $email->setReplytoEmail(SITEEMAIL);
            $email->setReplytoName(SITENAME);
            $email->setSubject($subject);
            $email->setHtmlMessage($html);
            $email->setTextMessage($text);
            $email->setTemplateFile(DOCUMENT_ROOT . '/emails/template.htm');

            //Send it
            $email->sendMail();

            //Send copy to us
            $email->setSubject("COPY OF: ".$subject);
            $email->setRecipient(SITEEMAIL);
            $email->sendMail();
            $email->setRecipient('hello@peterbourne.co.uk');
            $email->sendMail();


            //Now delete the stored image
            //$OO->deleteAllOrderLineImages();
        }


        if ($success != true) {
            $_SESSION['Message'] = array('Type' => 'warning', 'Message' => "There was a problem updating the order. Please try again.");
            $retdata = array('status' => 'error', 'detail' => "Sorry - there was a problem updating the order on the database");
        } else {
            $_SESSION['Message'] = array('Type' => 'success', 'Message' => "The order has been despatched.");
            $retdata = array('status' => 'success', 'detail' => format_datetime($DespatchDate));
        }

        echo json_encode($retdata);
    }