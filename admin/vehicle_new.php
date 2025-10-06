<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CCA\Vehicle;
    
    if (isset($_GET['id'])) {
        $id = clean_int($_GET['id']);
    }
    
    $VO = new Vehicle();
    
    if (!is_object($VO)) {
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
    $_SESSION['sub'] = 'vehicles';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Vehicles | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Vehicle details</h1>
            <p>Start a new vehicle record.</p>
            <form action="./vehicle_newExec.php" name="form1" enctype="multipart/form-data" method="post" class="standard">
                <div class='grid-x grid-margin-x'>
                    <div class='medium-6 cell'>
                        <?php if (isset($_SESSION['companyerror'])) {
                            echo $_SESSION['companyerror'];
                            unset($_SESSION['companyerror']);
                        } ?>
                        <p>
                            <label for="Company">Company:</label><input type="text" class='ajax-search' name="Company" id="Company" value="<?php echo check_output($_SESSION['PostedForm']['Company'] ?? ''); ?>" placeholder="Company"/>
                        </p>
                        <div id='results-list'></div>
                    </div>
                    <div class='medium-6 cell'>
                        <?php if (isset($_SESSION['regerror'])) {
                            echo $_SESSION['regerror'];
                            unset($_SESSION['regerror']);
                        } ?>
                        <p>
                            <label for="Reg">Registration:</label><input type="text" name="Reg" id="Reg" value="<?php echo check_output($_SESSION['PostedForm']['Reg'] ?? ''); ?>" placeholder="Registration"/>
                        </p>
                        <button class="button" name="submit" type="submit">Lookup Registration</button>
                    </div>
                </div>
                <input id='CustomerID' name='CustomerID' type='hidden' value='<?php echo check_output($_SESSION['PostedForm']['CustomerID'] ?? ''); ?>' />
            </form>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./vehicle_list.php' class='bottom-nav-option'><i class='fi-arrow-left'></i> Vehicles</a>
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
                        url: './ajax/_ajaxVehicleCustomerList.php',
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


            /**
             * Select Customer functionality from the ajax
             */
            $('body').on('click','#SelectCustomer', function(e) {
                e.preventDefault();
                var customerid = $(this).data('customer-id');
                var customername = $(this).data('customer-name');
                
                //Assign to fields
                $('#CustomerID').val(customerid);
                $('#Company').val(customername);
                
                //Hide results
                $('#results-list').html('');
            });
        });
    </script>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>