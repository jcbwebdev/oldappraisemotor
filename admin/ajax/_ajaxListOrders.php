<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\Ecommerce\Order;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkAdmin($_SERVER['PHP_SELF'], 'O');

        $q = $_POST["q"];
        $m = $_POST['m']; //Mode of operation
        $f = json_decode($_POST['f']); //JSON strong of filters

        //Prepare object
        $OO = new Order();

        //Now retrieve orders list, depending on mode of operation
        switch($m)
        {
            case 'surname':
                $Orders = $OO->listAllItems($q,'surname',$f);
                break;
            case 'invoice-number':
                $Orders = $OO->listAllItems($q,'inv-number',$f);
                break;
            case 'stripe-paymentintent':
                $Orders = $OO->listAllItems($q,'stripe-paymentintent',$f);
                break;
            case 'stripe-paymentmethod':
                $Orders = $OO->listAllItems($q,'stripe-paymentmethod',$f);
                break;
            default:
                $Orders = $OO->listAllItems($q,'surname',$f);
                break;
        }

        if (is_array($Orders) && count($Orders) >= 1)
        {
            unset($Order);
            //Start the output
            $output = "<table class='standard small-data'>";
            $output .= "<tr><th>Date</th><th>Invoice to</th><th>Amount</th><th>Status</th><th>Additional info</th></tr>";
            foreach ($Orders as $Order)
            {
                $output .= "<tr onmouseover=\"this.className='row_selected'\" onmouseout=\"this.className=''\" onclick=\"location.href='/admin/order_edit.php?id=".$Order['ID']."'\" style='cursor:pointer;'>";
                $output .= "<td>".format_datetime($Order['OrderDate'])."</td>";
                $output .= "<td>";
                $output .= "<strong>".$Order['InvFirstname']." ".$Order['InvSurname']."</strong><br/>";
                if ($Order['InvOrganisation'] != '') { $output .= $Order['InvOrganisation']."<br/>"; }
                if ($Order['InvAddress1'] != '') { $output .= $Order['InvAddress1']."<br/>"; }
                if ($Order['InvAddress2'] != '') { $output .= $Order['InvAddress2']."<br/>"; }
                if ($Order['InvTown'] != '') { $output .= $Order['InvTown']."<br/>"; }
                if ($Order['InvCounty'] != '') { $output .= $Order['InvCounty']."<br/>"; }
                if ($Order['InvPostcode'] != '') { $output .= $Order['InvPostcode']."<br/>"; }
                if ($Order['InvCountry'] != '') { $output .= $Order['InvCountry']; }
                $output .= "</td>";
                $output .= "<td>&pound;".number_format($Order['OrderGross'],2)."</td>";
                $output .= "<td><strong>".$Order['Status']."</strong></td>";
                $output .= "<td><label>Invoice number:</label> <strong>".$Order["InvoiceNumber"]."</strong>";
                if ($Order['Despatched'] == true) {
                    $output .= "<br/><label>Despatched:</label> ".format_datetime($Order['DespatchDate']);
                }
                $output .= "<label>Stripe result:</label> <strong>".$Order['Stripe_Result']."</strong><br/>";
                $output .= "<label>Payment Intent ID:</label> ".$Order['Stripe_PaymentIntentID'];
                $output .= "<label>Payment Method ID:</label> ".$Order['Stripe_PaymentMethodID'];
                $output .= "</td>";
                $output .= "</tr>";
            }
            $output .= "</table>";
        }
        else
        {
            $output = "<p><strong>Sorry</strong> - no orders found matching that search criteria</p>";
        }

        echo $output;
    }