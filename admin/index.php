<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    $_SESSION['main'] = 'admin';
    unset($_SESSION['sub']);
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <div class='grid-x grid-margin-x'>
                <div class='large-3 medium-6 cell'>
                    <?php if ($_SESSION['UserDetails']['AdminLevel'] == 'F') { ?>
                        <h3>General</h3>
                        <!--<a class='button admin-menu-button' href="customer_list.php">Companies / Users</a>-->
                        <a class='button admin-menu-button' href="customer_awaiting_approval_list.php">New customers to approve</a>
                        <a class='button admin-menu-button' href="customer_list.php">Customers</a>
                        <a class='button admin-menu-button' href="user_list.php">Users</a>
                        <a class='button admin-menu-button' href="auction_room_list.php">Auction rooms/brands</a>
                    <?php } ?>
                </div>
                <div class="large-3 medium-6 cell">
                    <h3>Vehicles &amp Auctions</h3>
                    <a class='button admin-menu-button' href="vehicle_list.php">Vehicles</a>
                    <a class='button admin-menu-button' href="auction_list.php">Auctions</a>
                    <?php if ($_SESSION['UserDetails']['AdminLevel'] == 'F') { ?>
                        <!--<h3>General Content</h3>
                        <a class='button small' href="content_list.php">General content</a>
                        <a class='button small' href="testimonial_list.php">Testimonials</a>-->
                    <?php } ?>
                </div>
                <div class="large-3 medium-6 cell">
                    <h3>Admin</h3>
                    <?php
                        if ($_SESSION['UserDetails']['AdminLevel'] == 'F' && $_SESSION['UserDetails']['ID'] == 1) {
                            echo "<a class='button admin-menu-button' href='site_settings.php'>Site Settings</a>";
                        }
                    ?>

                </div>
            </div>
            <div class='grid-x grid-margin-x space-above'>
                <div class='large-3 medium-6 cell'>
                    <a class='button admin-menu-button' href="/logout/">Log out</a>
                </div>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>