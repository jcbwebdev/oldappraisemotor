<?php
    include('../assets/dbfuncs.php');

    use PeterBourneComms\CMS\Content;
    use PeterBourneComms\CMS\News;
    use PeterBourneComms\CMS\Calendar;
    use PeterBourneComms\Schools\Staff;
    
    unset($_SESSION['subsub']);

    if ($_REQUEST['action'] == 'search' && $_REQUEST['SearchField'] != '')
    {
        $_SESSION['SearchField'] = $_REQUEST['SearchField'];
    }

    function multiSort()
    {
        //get args of the function
        $args = func_get_args();
        $c = count($args);
        if ($c < 2)
        {
            return false;
        }
        //get the array to sort
        $array = array_splice($args, 0, 1);
        $array = $array[0];
        //sort with an anoymous function using args
        usort($array, function ($a, $b) use ($args) {

            $i = 0;
            $c = count($args);
            $cmp = 0;
            while ($cmp == 0 && $i < $c)
            {
                $cmp = strcmp($a[$args[$i]], $b[$args[$i]]);
                $i++;
            }

            return $cmp;

        });

        return $array;

    }

    unset($_SESSION['main']);
    unset($_SESSION['sub']);


    include(DOCUMENT_ROOT . '/assets/inc-page_start.php');
?>
    <title>Search | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above'>
        <div class='grid-x grid-margin-x'>
            <div class='medium-4 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu.php'); ?>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-testimonial.php'); ?>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-quick-links.php'); ?>
            </div>
            <div class='medium-8 cell'>
                <h1>Search</h1>

                <?php
                    //Display form
                    echo "<form id='advsearch' name='advsearch' method='get' action='/search/?action=search' class='standard'>";
                    echo "<p><label for='SearchField' class='search'>Search for:</label>";
                    echo "<input name='SearchField' type='search' id='SearchField' value='" . check_output($_SESSION['SearchField']) . "' />";
                    echo "</p>";

                    echo "<span class='button' style='cursor: pointer;' onmouseover=\"this.className='button down'\" onmouseout=\"this.className='button'\" onclick=\"javascript:document.advsearch.submit()\">Search</span>";
                    echo "<input type='hidden' name='action' id='action' value='search' />";
                    echo "</form>";

                    //If search form submitted - carry out search
                    if ($_REQUEST['action'] == 'search' && $_REQUEST['SearchField'] != '')
                    {
                        $search_field = strtolower(filter_var($_REQUEST['SearchField'], FILTER_SANITIZE_STRING));
                        //Build the search
                        $arr_results = array();


                        //Content
                        $PBC = new Content();
                        $content_results = $PBC->searchContent($_REQUEST['SearchField']);
                        foreach ($content_results as $result)
                        {
                            $thisresult = array('Title' => $result['Title'], 'Content' => $result['Content'], 'Link' => $result['Link'], 'ResultType' => 'Content', 'DateDisplay' => $result['DateDisplay'], 'Weighting' => $result['Weighting']);
                            //Add to main results array
                            $arr_results[] = $thisresult;
                        }
                        unset($result);


                        //News
                        $PBN = new News();
                        $content_results = $PBN->searchContent($_REQUEST['SearchField']);
                        foreach ($content_results as $result)
                        {
                            $thisresult = array('Title' => $result['Title'], 'Content' => $result['Content'], 'Link' => $result['Link'], 'ResultType' => 'News', 'DateDisplay' => $result['DateDisplay'], 'Weighting' => $result['Weighting']);
                            //Add to main results array
                            $arr_results[] = $thisresult;
                        }
                        unset($result);


                        $arr_results = multiSort($arr_results, 'Weighting', 'DateDisplay');


                        //Now output the results
                        echo "<h2>Your search for \"<strong>" . check_output($search_field) . "</strong>\" found the following pages:</h2>";
                        unset($thisresult);
                        foreach ($arr_results as $thisresult)
                        {
                            echo "<div class='search-result' onclick='location.href=\"" . $thisresult['Link'] . "\"'>";
                            echo "<p class='search-result-linkinfo'><a href='" . $thisresult['Link'] . "'><span class='search-result-title'>" . $thisresult['Title'] . "</span></a></p>";
                            echo "<p class='search-result-text'>" . $thisresult['Content'] . "...<br/>";
                            echo "<span class='search-result-url'>" . substr($thisresult['Link'], 2) . "</span></p>";
                            echo "</div>";
                        }

                        //echo "<p class='help-text'>For News articles and Letters - only the last 6 months are searched.</p>";
                    }

                ?>
            </div>
            <div class='large-3 medium-4 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-panels.php'); ?>
            </div>
        </div>
    </div>
<?php include(DOCUMENT_ROOT . '/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT . '/assets/inc-page_end.php'); ?>