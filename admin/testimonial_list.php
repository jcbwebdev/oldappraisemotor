<?php
    include("../assets/dbfuncs.php");
    checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
    use PeterBourneComms\CMS\Testimonial;
    
    $_SESSION['main'] = "admin";
    $_SESSION['admin-section'] = 'general';
    $_SESSION['sub'] = "testimonial";
    
    include(DOCUMENT_ROOT.'/assets/inc-page_start.php');
?>
    <title>Testimonials | Admin | <?php echo $sitetitle; ?></title>
<?php include(DOCUMENT_ROOT.'/assets/inc-page_head.php'); ?>
    <div class='grid-container space-above space-below'>
        <div class='grid-x grid-margin-x grid-margin-y '>
            <div class='medium-3 cell'>
                <?php include(DOCUMENT_ROOT.'/assets/pnl-sidemenu-admin.php'); ?>
            </div>
            <div class='medium-9 cell'>
                <h1>Testimonials</h1>
                <p>Click the line that you want to edit. Or
                    <a href="testimonial_edit.php?state=new"><i class="fi-plus"></i> click here to add a new testimonial</a>.
                </p>
                <?php
                    $TO = new Testimonial();
                    $Testimonials = $TO->getAllTestimonials();
                    
                    if (is_array($Testimonials) && count($Testimonials) >= 1) {
                        //Display the items
                        echo "<table class='standard'>";
                        echo "<tr><th>Quote</th><!--<th>Attribution</th><th>Author</th><th>Section</th>--></tr>";
                        foreach ($Testimonials as $Testimonial) {
                            echo "<tr onmouseover=\"this.className='row_selected'\" onmouseout=\"this.className=''\" onclick=\"location.href='testimonial_edit.php?state=edit&id=".$Testimonial['ID']."'\" style='cursor: pointer;'>";
                            echo "<td>".$Testimonial['Quote']."</td><!--<td>".$Testimonial['Attribution']."</td><td>".$Testimonial['AuthorName']."</td><td>".$Testimonial['ContentTitle']."</td>--></tr>";
                        }
                        echo "</table>";
                    } else {
                        echo "<p>There are no testimonials yet.</p>";
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