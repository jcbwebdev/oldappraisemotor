<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = 'customers';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Customers | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='cca-panel'>
            <h1>Customers</h1>
            <p>All companies registered and Active on the system.</p>
            <p><a class='button' href='./customer_new.php'><i class='fi-plus'></i> Add new customer</a></p>
            <div class="callout general-panel">
                <p class="ajax_search">Search for Company or user surname or email to filter list:</p>
        
                <input class='ajax-search' type="text" autocomplete="off" placeholder="Company or Surname" name="membersearch1" data-search-type='name-email'/>
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
                    if (mode === '') { mode = 'name-email'; }
                    //Store last search type
                    lastsearchmode = mode;
                    lastsearchtext = text;

                    //Search
                    $.ajax({
                        type: "POST",
                        url: './ajax/_ajaxCustomerList.php',
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