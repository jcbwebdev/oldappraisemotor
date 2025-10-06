<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */

    use PeterBourneComms\Ecommerce\Product;
    use PeterBourneComms\Ecommerce\ProductType;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");


        checkAdmin($_SERVER['PHP_SELF'], 'E');

        $q = $_GET["q"];
        $m = $_GET['m']; //Mode of operation

        //Prepare object
        $PO = new Product();
        $PTO = new ProductType();

        //Now retrieve products list, depending on mode of operation
        switch($m)
        {
            case 'title':
                $Products = $PO->listAllItems($q,'title');
                break;
            case 'type-title':
                $Products = $PO->listAllItems($q, 'type-title');
                break;
            default:
                $Products = $PO->listAllItems($q,'title');
                break;
        }

        if (is_array($Products) && count($Products) >= 1)
        {
            unset($Product);
            //Start the output
            $output = "<table class='standard'>";
            $output .= "<tr><th colspan='2'>Product</th><th>Price</th><th>Category</th><th>Types filtering</th></tr>";
            foreach ($Products as $Product)
            {
                //Image
                if ($Product['ImgFilename'] != '' && file_exists(DOCUMENT_ROOT.$Product['ImgPath']."small/".$Product['ImgFilename'])) {
                    $image = FixOutput($Product['ImgPath']."small/".$Product['ImgFilename']);
                } else {
                    $image = "/assets/img/placeholder.png";
                }

                $output .= "<tr onmouseover=\"this.className='row_selected'\" onmouseout=\"this.className=''\" onclick=\"location.href='/admin/product_edit.php?id=".$Product['ID']."'\" style='cursor:pointer;'>";
                $output .= "<td style='width:150px;'><img src='".$image."' alt='".FixOutput($Product['Title'])."' title='".FixOutput($Product['Title'])."' style='width: 150px;'/></td>";
                $output .= "<td>".$Product['Title']."</td>";
                $output .= "<td>&pound;".number_format($Product['Price'],2)."</td>";
                $output .= "<td>".$Product['CategoryInfo']['Title']."</td>";
                $output .= "<td>";
                //$output .= count($Product['ProductTypes']);
                if (is_array($Product['ProductTypes']) && count($Product['ProductTypes']) > 0) {
                    $counter = 1;
                    foreach($Product['ProductTypes'] as $Type) {
                        $TypeInfo = $PTO->getItemById($Type['TypeID']);
                        if (is_array($TypeInfo) && count($TypeInfo) > 0) {
                            if ($counter !== 1) { $output.= ", "; }
                            $output .= $TypeInfo['Title'];
                            $counter++;
                        }
                    }
                }
                $output .= "</td>";
                $output .= "</tr>";
            }
            $output .= "</table>";
        }
        else
        {
            $output = "<p><strong>Sorry</strong> - no products found matching that search criteria</p>";
        }

        echo $output;
    }