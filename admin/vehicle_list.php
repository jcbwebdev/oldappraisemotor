<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'vehicles';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Vehicles | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Vehicles</h1>
            <p>All vehicles on the system.</p>
            <p><a class='button' href='./vehicle_new.php'><i class='fi-plus'></i> Add new vehicle</a></p>
            <div class="callout general-panel">
                <p class="ajax_search">Search for Vehicle - use Reg or customer name to filter list:</p>
        
                <input class='ajax-search' type="text" autocomplete="off" placeholder="Reg or Customer" name="membersearch1" data-search-type='reg-customer'/>
            </div>
            <div id="results-list"></div>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
    <script>
        $(document).ready(function () {
            //Timeout and ajax lookup functionality
            var delayTimer;
            var lastsearchmode;
            var lastsearchtext;

            //Search delay
            $('.ajax-search').on('keyup', function () {
                doSearch(encodeURIComponent($(this).val()), $(this).data('search-type'));
            });

            //Initial search/list
            doSearch();

            /**
             * Search function
             * @param text
             * @param mode
             */
            function doSearch(text, mode) {
                clearTimeout(delayTimer);
                delayTimer = setTimeout(function () {
                    if (mode === '') { mode = 'reg-customer'; }
                    //Store last search type
                    lastsearchmode = mode;
                    lastsearchtext = text;

                    //Search
                    $.ajax({
                        type: "POST",
                        url: './ajax/_ajaxVehicleList.php',
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