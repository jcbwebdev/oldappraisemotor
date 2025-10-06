<link rel="stylesheet" href="/assets/css/app.css">
<link rel='stylesheet' href='/assets/css/app-admin.min.css'>

<link rel="apple-touch-icon" sizes="180x180" href="/assets/img/favicons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/img/favicons/favicon-16x16.png">
<link rel="manifest" href="/assets/img/favicons/site.webmanifest">
<link rel="mask-icon" href="/assets/img/favicons/safari-pinned-tab.svg" color="#f5a31d">
<link rel="shortcut icon" href="/assets/img/favicons/favicon.ico">
<meta name="msapplication-TileColor" content="#f5a31d">
<meta name="msapplication-config" content="/assets/img/favicons/browserconfig.xml">
<meta name="theme-color" content="#ffffff">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

<?php
    if (isset($MetaDesc)) {
        echo "<meta name='description' content='".$MetaDesc."' />\n";
    }
    if (isset($MetaKey)) {
        echo "<meta name='keywords' content='".$MetaKey."' />\n";
    }
?>
</head>
<body>
<div class="page-container off-canvas-wrapper">
    <div class="off-canvas position-right" id="offCanvasRight" data-off-canvas data-force-top="true">
        <?php
            include(DOCUMENT_ROOT.'/assets/pnl-mainmenu.php');
            //echo $offcanvas."\n";
        ?>
    </div>

    <div class="off-canvas-content" data-off-canvas-content>
        <?php
            if (isset($_SESSION['main']) && $_SESSION['main'] == 'admin') {
                echo "<div class='admin-header'><div class='grid-container'><h2>Administration of website</h2></div></div>\n";
            }
        ?>
        <div id='stickynavanchor' class='stickynavanchor'>
            <?php
                if (isset($_SESSION['UserDetails']) && is_numeric($_SESSION['UserDetails']['ID']) && $_SESSION['UserDetails']['ID'] > 0) {
            ?>
            <div class='sticky' data-sticky data-anchor='stickynavanchor' data-options='marginTop: 0; stickyOn: small;'>
                <div class='sticky-container' data-sticky-container>
                    <div class='header-bg'>
                        <div class='grid-container'>
                            <div class='header-area'>
                                <div class='header-logo'>
                                    <a href='/'><img src='/assets/img/logo.svg' alt='<?php echo $_SESSION['SiteSettings']['Title']; ?>' /></a>
                                </div>
                                <div class='header-menu'>
                                    <?php
                                        if (isset($_SESSION['UserDetails']['ID']) && $_SESSION['UserDetails']['ID'] > 0 && isset($_SESSION['UserDetails']['AdminLevel']) && $_SESSION['UserDetails']['AdminLevel'] != '') {
                                            echo "<a class='button brown-button admin-button' href='/admin/'><i class='fi-wrench'></i>&nbsp;&nbsp;Admin</a>";
                                        }
                                    
                                        //echo $menutodisplay;
                                    ?>
                                    <!--<button class='menu-icon dark' type='button'></button>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } else { ?>
                <div class='header-logo-only'>
                    <a href='/'><img src='/assets/img/logo.svg' alt='<?php echo $_SESSION['SiteSettings']['Title']; ?>' /></a>
                </div>
            <?php } ?>
            <?php
                if (isset($_SESSION['Message'])) {
                    switch ($_SESSION['Message']['Type']) {
                        case 'alert':
                            $class = 'alert';
                            break;
                        case 'warning':
                            $class = 'warning';
                            break;
                        case 'success':
                            $class = 'success';
                            break;
                        case 'primary':
                            $class = 'primary';
                            break;
                        case 'secondary':
                            $class = 'secondary';
                            break;
                        default:
                            $class = 'warning';
                            break;
                    }
                    echo "<div class='grid-container space-above'><div class='callout user-message ".$class."' data-toggler data-animate='scale-in-down'>";
                    echo $_SESSION['Message']['Message'];
                    echo "</div></div>";
                    unset($_SESSION['Message']);
                }
            ?>