<ul class='vertical menu sidemenu accordion-menu' data-accordion-menu >
    <li><a href='/'>Home</a></li>
    <li><a href='/admin/'>Admin</a></li>
    <?php if ($_SESSION['UserDetails']['AdminLevel'] == 'F') { ?>
    <li><a href='#' <?php if ($_SESSION['admin-section'] == 'home') {
            echo " class='is-active'";
        } ?>>Home page</a>
        <ul class='menu vertical <?php if ($_SESSION['admin-section'] == 'home') {
            echo " is-active";
        } ?>'>
            <li><a href='/admin/carousel_list.php' <?php if ($_SESSION['sub'] == 'carousel') {
                    echo " class='is-active'";
                } ?>>Carousel</a></li>
            <li><a href='/admin/homepage_panels_list.php' <?php if ($_SESSION['sub'] == 'homepage_panels') {
                    echo " class='is-active'";
                } ?>>Panels</a></li>
            <li><a href='/admin/homepage.php' <?php if ($_SESSION['sub'] == 'homepage') {
                    echo " class='is-active'";
                } ?>>Content</a></li>
            <li><a href='/admin/warningalert_list.php' <?php if ($_SESSION['sub'] == 'warnings') {
                    echo " class='is-active'";
                } ?>>Closure/info alerts</a></li>
        </ul>
    </li>
    <li><a href='#' <?php if ($_SESSION['admin-section'] == 'general') {
            echo " class='is-active'";
        } ?>>General</a>
        <ul class='menu vertical <?php if ($_SESSION['admin-section'] == 'general') {
            echo " is-active";
        } ?>'>
            <li><a href='/admin/news_list.php' <?php if ($_SESSION['sub'] == 'news') {
                    echo " class='is-active'";
                } ?>>News</a></li>
            <li><a href='/admin/calendar_list.php' <?php if ($_SESSION['sub'] == 'calendar') {
                    echo " class='is-active'";
                } ?>>Calendar</a></li>
            <li><a href='/admin/content_list.php' <?php if ($_SESSION['sub'] == 'content') {
                    echo " class='is-active'";
                } ?>>Content</a></li>
            <li><a href='/admin/testimonial_list.php' <?php if ($_SESSION['sub'] == 'testimonial') {
                    echo " class='is-active'";
                } ?>>Testimonials</a></li>
            <li><a href='/admin/people_list.php' <?php if ($_SESSION['sub'] == 'people') {
                    echo " class='is-active'";
                } ?>>People</a></li>
            <li><a href='/admin/policy_cat_list.php' <?php if ($_SESSION['sub'] == 'policy-cats') {
                    echo " class='is-active'";
                } ?>>Policy Categories</a></li>
            <li><a href='/admin/policy_list.php' <?php if ($_SESSION['sub'] == 'policies') {
                    echo " class='is-active'";
                } ?>>Policies</a></li>
            <li><a href='/admin/faq_list.php' <?php if ($_SESSION['sub'] == 'faqs') {
                    echo " class='is-active'";
                } ?>>FAQs</a></li>
            <li><a href='/admin/bloggers_list.php' <?php if ($_SESSION['sub'] == 'bloggers') {
                    echo " class='is-active'";
                } ?>>Bloggers</a></li>
            <li><a href='/admin/blog_list.php' <?php if ($_SESSION['sub'] == 'blogs') {
                    echo " class='is-active'";
                } ?>>Blogs</a></li>
        </ul>
    </li>
    <li><a href='#' <?php if ($_SESSION['admin-section'] == 'users') {
            echo " class='is-active'";
        } ?>>Website Users</a>
        <ul class='menu vertical <?php if ($_SESSION['admin-section'] == 'users') {
            echo " is-active";
        } ?>'>
            <li><a href='/admin/user_list.php' <?php if ($_SESSION['sub'] == 'users') { echo " class='is-active'"; } ?>>All members/users</a></li>
        </ul>
    </li>
    <?php } ?>
    <li><a href='/logout/'>Log out</a></li>
</ul>