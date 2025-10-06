<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\Customer;
    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CCA\AuctionRoom;
    use PeterBourneComms\CCA\Note;
    
    if (isset($_GET['id'])) {
        $id = clean_int($_GET['id']);
    }
    
    $CustO = new Customer();
    
    if (!is_object($CustO)) {
        die();
    }
    
    if (isset($_SESSION['error']) && $_SESSION['error'] === true) {
        //This is an edit - take all variable from the SESSION
    } else {
        unset($_SESSION['PostedForm']);
    }
    
    unset($_SESSION['error']);
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'customer';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Customers | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Customer details</h1>
            <p>Start a new customer record.</p>
            <div class='grid-x grid-margin-x'>
                <div class='medium-8 cell'>
                    <form action="./customer_newExec.php" name="form1" enctype="multipart/form-data" method="post" class="standard">
                        <div class='grid-x grid-margin-x'>
                            <div class='medium-6 cell'>
                                <h2>Company information</h2>
                                <?php if (isset($_SESSION['companyerror'])) {
                                    echo $_SESSION['companyerror'];
                                    unset($_SESSION['companyerror']);
                                } ?>
                                <p>
                                    <label for="Company">Company:</label><input type="text" class='ajax-search' name="Company" id="Company" value="<?php echo check_output($_SESSION['PostedForm']['Company'] ?? ''); ?>" placeholder="Company"/>
                                </p>
                            </div>
                        </div>
                        <button class="button" name="submit" type="submit">Save customer</button>
                    </form>
                </div>
                <div class='medium-4 cell'>
                    <div id='results-list'></div>
                </div>
            </div>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./customer_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Customers</a>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script>
        $(document).ready(function () {
            //Timeout and ajax lookup functionality
            var delayTimer;

            //Search delay
            $('.ajax-search').on('keyup', function () {
                doSearch(encodeURIComponent($(this).val()), $(this).data('search-type'));
            });

            /**
             * Search function
             * @param text
             * @param mode
             */
            function doSearch(text, mode) {
                clearTimeout(delayTimer);
                delayTimer = setTimeout(function () {
                    if (mode === '') {
                        mode = 'name-email';
                    }
                    
                    //Search
                    $.ajax({
                        type: "POST",
                        url: './ajax/_ajaxCustomerSimilarList.php',
                        data: {
                            q: text,
                            m: mode
                        },
                        dataType: 'json'
                    }).done(function (result) {
                        if (result.err) {
                            //error
                            //console.log(result.err);
                            alert('There was a problem: ' + result.err);
                        } else {
                            //Display the returned data
                            $('#results-list').html(result.detail);
                        }
                    });
                }, 500); // Will do the ajax stuff after 500 ms, or 0.5s
            }
        });
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>