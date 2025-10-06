<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CCA\Customer;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
        $q = rawurldecode($_POST["q"] ?? '');
        if (isset($_POST['m'])) {
            $m = $_POST['m']; //Mode of operation
        } else { $m = null; }

        //Prepare object
        $UO = new User();
        $CustO = new Customer();
        
        if (!is_object($UO) || !is_object($CustO)) {
            echo "NO OBJECTS";
            die();
        }

        //Now retrieve users list, depending on mode of operation
        switch($m) {
            case 'name-email':
                $Users = $UO->listAllItems($q,'name-email', 'asc', true);
                break;
            default:
                $Users = $UO->listAllItems($q,'name-email', 'asc', true);
                break;
        }
        
        //Start the output
        $output = "";
        
        if (is_array($Users) && count($Users) >= 1) {
            $output = "<table class='standard'>\n";
            $output .= "<tr><th>Name</th><th>Company</th><th>Contact details</th><th>Last logged in</th><th>Edit</th></tr>\n";
            
            foreach ($Users as $Item) {
                $output .= "<tr>";
                $output .= "<td>";
                if ($Item['Title'] ?? '' != '') {
                    $output .= $Item['Title']." ";
                }
                $output .= ($Item['Firstname'] ?? '')." ".($Item['Surname'] ??'');
                $output .= "</td>";
                $output .= "<td>";
                $Customer = $CustO->getItemById($Item['CustomerID']);
                if (is_array($Customer) && count($Customer) > 0) {
                    $output .= $Customer['Company'];
                }
                $output .= "</td>";
                $output .= "<td>";
                if ($Item['Mobile'] != '') {
                    $output .= "<a href='tel:".$Item['Mobile']."'>".$Item['Mobile']."</a><br/>";
                }
                if ($Item['Email'] != '') {
                    $output .= "<a href='mailto:".$Item['Email']."'>".$Item['Email']."</a>";
                }
                $output .= "</td>";
                $output .= "<td>".format_datetime($Item['LastLoggedIn'] ?? '')."</td>";
                $output .= "<td><a href='./user_edit.php?id=".$Item['ID']."'><i class='fi-pencil'></i></a></td>";
                $output .= "</tr>\n";
            }
            $output .= "</table>";
        } else {
            $output = "<p><strong>Sorry</strong> - no users found matching that search criteria</p>";
        }
        
        
        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'detail' => $output);
        }
    
        echo json_encode($retdata);
    }