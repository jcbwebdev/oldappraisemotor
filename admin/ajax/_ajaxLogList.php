<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\HETA\Log;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
        $q = utf8_decode(rawurldecode($_POST["q"]));
        $m = $_POST['m']; //Mode of operation

        //Prepare object
        $LO = new Log();
        
        if (!is_object($LO)) {
            echo "NO OBJECT";
            die();
        }

        //Now retrieve users list, depending on mode of operation
        switch($m) {
            case 'action':
                $Logs = $LO->listAllItems($q,'action');
                break;
            case 'name':
                $Logs = $LO->listAllItems($q,'user-name');
                break;
            default:
                $Logs = $LO->listAllItems();
                break;
        }
        
        //Start the output
        $output = "";
        
        if (is_array($Logs) && count($Logs) >= 1) {
            $output = "<table class='standard small-data'>\n";
            $output .= "<tr><th>When</th><th>What</th><th>Who by</th></tr>\n";
            
            foreach ($Logs as $LogItem) {
                //$output .= "<tr onmouseover=\"this.className='row_selected'\" onmouseout=\"this.className=''\" onclick=\"location.href='/admin/organisation_details.php?state=edit&id=".$LogItem['ID']."'\" style='cursor:pointer;'>";
                $output .= "<tr onmouseover=\"this.className='row_selected'\" onmouseout=\"this.className=''\">";

                $output .= "<td>".format_datetime($LogItem['DateLogged'])."</td>";
                $output .= "<td><strong>".$LogItem['Action']."</strong><br/>".$LogItem['Detail']."</td>";
                $output .= "<td>".$LogItem['UserName']."</td>";
                $output .= "</tr>\n";
            }
            $output .= "</table>";
        } else {
            $output = "<p><strong>Sorry</strong> - no log entries found matching that search criteria</p>";
        }
    
        
        
        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'detail' => $output);
        }
    
        echo json_encode($retdata);
    }