<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\Customer;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'approval';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>New Customers | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>New customers requiring approval/auction room assignment</h1>
            <p>Any customers who have signed up on the website and need approving are listed here. Any potential duplicate company names are also highlighted - and you can assign/override company names once you are approving/editing customers.</p>
            <?php
                $CO = new Customer();
                if (is_object($CO)) {
                    $NewCustomers = $CO->listAllItems(null, 'new');
                    if (is_array($NewCustomers) && count($NewCustomers) > 0) {
                        echo "<h3>New sign ups</h3>";
                        echo "<table class='standard'>";
                        echo "<tr><th>Customer</th><th>Email</th><th>Telephone</th><th>Signed up</th><th>Edit</th></tr>\n";
                        foreach ($NewCustomers as $Item) {
                            echo "<tr>";
                            echo "<td>";
                            if (isset($Item['Users']) && is_array($Item['Users'])) {
                                $User = $Item['Users'][0];
                                echo "<strong>".$User['Firstname']." ".$User['Surname']."</strong><br/>";
                            }
                            echo $Item['Company']."</td>";
                            echo "<td>".$Item['Email']."</td><td>".$Item['Tel'];
                            if ($Item['Mobile'] != '') {
                                echo "<br/> / ".$Item['Mobile'];
                            }
                            echo "</td><td>".format_datetime($Item['DateRegistered'])."</td>";
                            echo "<td><a href='./customer_approval.php?state=edit&id=".$Item['ID']."'><i class='fi-pencil'></i></a></td>";
                            echo "</tr>\n";
                        }
                        echo "</table>";
                    } else {
                        echo "<p>There are no new customers to process at the moment</p>";
                    }
                }
            ?>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>