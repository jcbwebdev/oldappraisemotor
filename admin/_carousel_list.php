<?php
    include('../assets/dbfuncs.php');
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CMS\Carousel;
    
    $_SESSION['main'] = 'admin';
    $_SESSION['admin-section'] = 'home';
    $_SESSION['sub'] = 'carousel';
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Carousel | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above space-below'>
        <div class='grid-x grid-margin-x grid-margin-y '>
            <div class='medium-3 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu-admin.php'); ?>
            </div>
            <div class='medium-9 cell'>
                <h1>Carousel images</h1>
                <p>
                    <a href="carousel_details.php?state=new"><i class="fi-plus"></i> Add new image</a> or click the line that you want to edit.
                </p>
                <?php
                    $CarouselObj = new Carousel();
                    $Carousel = $CarouselObj->listAllItems();
                    
                    if (count($Carousel) >= 1) {
                        echo "<table class='standard'>";
                        echo "  <tr><th>&nbsp;</th><!--<th>Display order</th>--><th>Author</th><th>&nbsp;</th></tr>\n";
                        
                        foreach ($Carousel as $Slide) {
                            echo "  <tr onmouseover=\"this.className='row_selected'\" onmouseout=\"this.className=''\" onclick=\"location.href='carousel_details.php?state=edit&id=".$Slide['ID']."'\" style='cursor: pointer;'>";
                            echo "    <td>".$Slide['Title']."</td><!--<td class=\"datecol\">".$Slide['DisplayOrder']."</td>--><td>".$Slide['AuthorName']."</td><td>";
                            if ($Slide['ImgFilename'] != '') {
                                echo "<img src=\"/user_uploads/images/carousel/".$Slide['ImgFilename']."\" alt='Carousel image' style='width: 200px;' />";
                            }
                            echo "</td></tr>";
                        }
                        
                        echo "</table>";
                    } else {
                        echo "<p>There are no carousel images yet. <a href='carousel_details.php?state=new'>Add new image</a>.</p>";
                    }
                ?>
            </div>
        </div>

        <div class='cca-panel space-above bottom-nav'><p>Admin options</p>
            <a href='./' class='bottom-nav-option'><i class='fi-arrow-up'></i> Admin</a>
            <a href='../' class='bottom-nav-option'><i class='fi-eject'></i> Home</a>
        </div>
    </div>
<?php include(DOCUMENT_ROOT.'/assets/inc-body_end.php'); ?>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_end.php'); ?>