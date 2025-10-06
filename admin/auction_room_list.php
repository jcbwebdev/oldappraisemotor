<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\AuctionRoom;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'auction-room';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Auction Rooms | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Auction Rooms</h1>
            <p>Add new, or edit existing</p>
            <p><a href='./auction_room_edit.php?state=new' class='button'>Add Auction Room</a></p>
            <?php
                $ARO = new AuctionRoom();
                if (is_object($ARO)) {
                    $AuctionRooms = $ARO->listAllItems();
                    if (is_array($AuctionRooms) && count($AuctionRooms) > 0) {
                        echo "<table class='standard' style='max-width: 500px;'>";
                        echo "<tr><th>&nbsp;</th><th>Title</th><th>Edit</th></tr>\n";
                        foreach($AuctionRooms as $Item) {
                            echo "<tr>";
                            echo "<td>";
                            if ($Item['ImgFilename'] != '' && file_exists(DOCUMENT_ROOT.$Item['ImgPath'].$Item['ImgFilename'])) {
                                echo "<img src='".check_output($Item['ImgPath'].$Item['ImgFilename'])."' alt='Logo' style='width: 60px; height: auto;' />";
                            }
                            echo "</td>";
                            echo "<td>".$Item['Title']."</td>";
                            echo "<td><a href='./auction_room_edit.php?state=edit&id=".$Item['ID']."'><i class='fi-pencil'></i></a></td>";
                            echo "</tr>\n";
                        }
                        echo "</table>";
                    }
                }
            ?>


            <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
                <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
                <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>