<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CCA\Customer;
    use PeterBourneComms\CCA\AuctionRoom;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
        $q = rawurldecode($_POST["q"] ?? '');
        if (isset($_POST['m'])) {
            $m = $_POST['m']; //Mode of operation
        } else { $m = null; }

        //Prepare object
        $CO = new Customer();
        
        if (!is_object($CO)) {
            echo "NO OBJECT";
            die();
        }

        if ($q !== '') {
            //Now retrieve users list, depending on mode of operation
            switch ($m) {
                case 'name-email':
                    $SimilarCustomers = $CO->listAllItems($q, 'name-email', 'asc', true);
                    break;
                default:
                    $SimilarCustomers = $CO->listAllItems($q, 'customer-fuzzy', 'asc', true);
                    break;
            }
            
            //Start the output
            $output = "";
            
            if (is_array($SimilarCustomers) && count($SimilarCustomers) >= 1) {
                $output = "<h3>WARNING:</h3><p>There are similar customers with this name already (click to go and edit there instead):</p>";
                $output .= "<table class='standard'>";
                foreach ($SimilarCustomers as $Item) {
                    $output .= "<tr><td class='conflict-company-option' data-company-name='".FixOutput($Item['Company'])."' data-customer-id='".$Item['ID']."' onclick='location.href=\"./customer_edit.php?id=".$Item['ID']."\"'>".$Item['Company']."</td></tr>\n";
                }
                $output .= "</table>";
            }
        } else {
            $output = "";
        }
        
        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'detail' => $output);
        }
    
        echo json_encode($retdata);
    }