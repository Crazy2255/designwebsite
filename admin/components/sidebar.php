<?php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
/* Sidebar Layout */
#layoutSidenav_nav {
    position: fixed;
    top: 56px;
    left: 0;
    bottom: 0;
    width: 250px;
    z-index: 1000;
    overflow-y: auto;
    background: #f8f9fa;
}

.sb-sidenav {
    height: 100%;
    padding-bottom: 60px;
    display: flex;
    flex-direction: column;
}

.sb-sidenav-menu {
    flex-grow: 1;
    padding: 0;
}

/* Menu Items */
.nav {
    display: flex;
    flex-direction: column;
    padding-bottom: 60px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #6c757d;
    text-decoration: none;
    position: relative;
    transition: all 0.2s ease;
}

.nav-link:hover {
    color: #0d6efd;
    background: rgba(13, 110, 253, 0.05);
}

.nav-link.active {
    color: #0d6efd;
    background: rgba(13, 110, 253, 0.1);
}

/* Collapse functionality */
.nav-link[data-bs-toggle="collapse"] {
    position: relative;
}

.sb-sidenav-collapse-arrow {
    margin-left: auto;
    transition: transform .2s ease;
}

.nav-link:not(.collapsed) .sb-sidenav-collapse-arrow {
    transform: rotate(-180deg);
}

/* Nested menu styles */
.sb-sidenav-menu-nested {
    padding-left: 1.5rem;
    background: rgba(0, 0, 0, 0.01);
    border-left: 3px solid rgba(13, 110, 253, 0.1);
}

.sb-sidenav-menu-nested .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.sb-sidenav-menu-nested .nav-link:hover,
.sb-sidenav-menu-nested .nav-link.active {
    color: #0d6efd;
}

/* Menu Labels */
.menu-label {
    padding: 1rem 1rem 0.5rem;
    font-size: 0.75rem;
    font-weight: bold;
    text-transform: uppercase;
    color: #6c757d;
}

/* Icons */
.sb-nav-link-icon {
    width: 1.25rem;
    margin-right: 0.5rem;
    font-size: 0.9rem;
    color: inherit;
    opacity: 0.8;
}

/* Footer */
.sb-sidenav-footer {
    position: fixed;
    bottom: 0;
    width: 250px;
    background: #f8f9fa;
    border-top: 1px solid #ddd;
    padding: 0.75rem;
    z-index: 1001;
}

/* Custom Scrollbar */
#layoutSidenav_nav::-webkit-scrollbar {
    width: 6px;
}

#layoutSidenav_nav::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#layoutSidenav_nav::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

#layoutSidenav_nav::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Main Content Adjustment */
#layoutSidenav_content {
    margin-left: 250px;
    padding-top: 56px;
}

/* Responsive */
@media (max-width: 768px) {
    #layoutSidenav_nav,
    .sb-sidenav-footer {
        width: 200px;
    }
    
    #layoutSidenav_content {
        margin-left: 200px;
    }
}

/* Bootstrap collapse overrides */
.collapse:not(.show) {
    display: none;
}

.collapse.show {
    display: block;
}

.collapsing {
    height: 0;
    overflow: hidden;
    transition: height 0.2s ease;
}

/* Collapse Animation Styles */
.collapse {
    display: none;
}

.collapse.show {
    display: block;
}

.nav-link[data-bs-toggle="collapse"] .sb-sidenav-collapse-arrow {
    transition: transform .2s ease;
}

.nav-link[data-bs-toggle="collapse"].collapsed .sb-sidenav-collapse-arrow {
    transform: rotate(0deg);
}

.nav-link[data-bs-toggle="collapse"]:not(.collapsed) .sb-sidenav-collapse-arrow {
    transform: rotate(-180deg);
}

/* Nested menu transition */
.sb-sidenav-menu-nested {
    transition: all 0.2s ease-in-out;
}
</style>


<div id="layoutSidenav_nav">
    <nav class="sb-sidenav" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="menu-label">CORE</div>
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    Dashboard
                </a>
                
                <div class="menu-label">E-COMMERCE</div>
                <a class="nav-link <?php echo in_array($current_page, ['index.php', 'create.php', 'edit.php']) && strpos($_SERVER['PHP_SELF'], '/Category/') !== false ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseCategories" 
                   aria-expanded="false">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    Categories
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['c-index.php', 'c-create.php', 'c-edit.php']) ? 'show' : ''; ?>" id="collapseCategories">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'c-index.php' ? 'active' : ''; ?>" 
                           href="c-index.php">
                            All Categories
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'c-create.php' ? 'active' : ''; ?>" 
                           href="c-create.php">
                            Add Category
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['w-index.php', 'w-create.php', 'w-edit.php']) ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseWooden" 
                   aria-expanded="false">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-tree"></i>
                    </div>
                    Wooden Items
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['w-index.php', 'w-create.php', 'w-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseWooden">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'w-index.php' ? 'active' : ''; ?>" 
                           href="w-index.php">
                            All Wooden Items
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'w-create.php' ? 'active' : ''; ?>" 
                           href="w-create.php">
                            Add Wooden Item
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['co-index.php', 'co-create.php', 'co-edit.php']) ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseColors" 
                   aria-expanded="false">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    Colors
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['co-index.php', 'co-create.php', 'co-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseColors">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'co-index.php' ? 'active' : ''; ?>" 
                           href="co-index.php">
                            All Colors
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'co-create.php' ? 'active' : ''; ?>" 
                           href="co-create.php">
                            Add Color
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['m-index.php', 'm-create.php', 'm-edit.php']) ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseMaterials" 
                   aria-expanded="false">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    Materials
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['m-index.php', 'm-create.php', 'm-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseMaterials">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'm-index.php' ? 'active' : ''; ?>" 
                           href="m-index.php">
                            All Materials
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'm-create.php' ? 'active' : ''; ?>" 
                           href="m-create.php">
                            Add Material
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['mr-index.php', 'mr-create.php', 'mr-edit.php']) ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseMarbles" 
                   aria-expanded="false">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-gem"></i>
                    </div>
                    Marbles
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['mr-index.php', 'mr-create.php', 'mr-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseMarbles">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mr-index.php' ? 'active' : ''; ?>" 
                           href="mr-index.php">
                            All Marbles
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mr-create.php' ? 'active' : ''; ?>" 
                           href="mr-create.php">
                            Add Marble
                        </a>
                    </nav>
                </div>

               

                <a class="nav-link <?php echo in_array($current_page, ['sub-index.php', 'sub-create.php', 'sub-edit.php']) ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseSubcategories" 
                   aria-expanded="false">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    Subcategories
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['sub-index.php', 'sub-create.php', 'sub-edit.php']) ? 'show' : ''; ?>" id="collapseSubcategories">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sub-index.php' ? 'active' : ''; ?>" 
                           href="sub-index.php">
                            All Subcategories
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sub-create.php' ? 'active' : ''; ?>" 
                           href="sub-create.php">
                            Add Subcategory
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array($current_page, ['p-index.php', 'p-create.php', 'p-edit.php']) ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseProducts" 
                   aria-expanded="false">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    Products
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse" id="collapseProducts">
                <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'p-index.php' ? 'active' : ''; ?>" 
                           href="p-index.php">
                            All Products
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'p-create.php' ? 'active' : ''; ?>" 
                           href="p-create.php">
                            Add Product
                        </a>
                    </nav>
                </div>
                
                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['e-index.php']) ? 'active' : 'collapsed'; ?>" 
                   href="e-index.php">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    Enquiries
                </a>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['contact-index.php']) ? 'active' : 'collapsed'; ?>" 
                   href="contact-index.php">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-address-book"></i>
                    </div>
                    Contact Enquiries
                </a>
                
                <div class="menu-label">SETTINGS</div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['cs-index.php', 'cs-create.php', 'cs-edit.php']) ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseCaseStudies" 
                   aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['cs-index.php', 'cs-create.php', 'cs-edit.php']) ? 'true' : 'false'; ?>">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    Case Studies
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['cs-index.php', 'cs-create.php', 'cs-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseCaseStudies">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cs-index.php' ? 'active' : ''; ?>" 
                           href="cs-index.php">
                            All Case Studies
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cs-create.php' ? 'active' : ''; ?>" 
                           href="cs-create.php">
                            Add Case Study
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['cl-index.php', 'cl-create.php', 'cl-edit.php']) ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseClients" 
                   aria-expanded="false">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    Clients
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['cl-index.php', 'cl-create.php', 'cl-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseClients">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cl-index.php' ? 'active' : ''; ?>" 
                           href="cl-index.php">
                            All Clients
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'cl-create.php' ? 'active' : ''; ?>" 
                           href="cl-create.php">
                            Add Client
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['di-index.php', 'di-create.php', 'di-edit.php']) ? 'active' : ''; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseDesignIdeas" 
                   aria-expanded="false">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    Design Ideas
                    <div class="ms-auto">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['di-index.php', 'di-create.php', 'di-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseDesignIdeas">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'di-index.php' ? 'active' : ''; ?>" 
                           href="di-index.php">
                            All Design Ideas
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'di-create.php' ? 'active' : ''; ?>" 
                           href="di-create.php">
                            Add Design Idea
                        </a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['h-index.php', 'h-create.php', 'h-edit.php']) ? 'active' : 'collapsed'; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseHero" 
                   aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['h-index.php', 'h-create.php', 'h-edit.php']) ? 'true' : 'false'; ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-image"></i></div>
                    Hero Section
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['h-index.php', 'h-create.php', 'h-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseHero">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'h-index.php' ? 'active' : ''; ?>" 
                           href="h-index.php">All Hero</a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'h-create.php' ? 'active' : ''; ?>" 
                           href="h-create.php">Add Hero</a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['g-index.php', 'g-create.php', 'g-edit.php']) ? 'active' : 'collapsed'; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseGallery" 
                   aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['g-index.php', 'g-create.php', 'g-edit.php']) ? 'true' : 'false'; ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-images"></i></div>
                    Gallery
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['g-index.php', 'g-create.php', 'g-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseGallery">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'g-index.php' ? 'active' : ''; ?>" 
                           href="g-index.php">All Images</a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'g-create.php' ? 'active' : ''; ?>" 
                           href="g-create.php">Add Image</a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['t-index.php', 't-create.php', 't-edit.php']) ? 'active' : 'collapsed'; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseTestimonials" 
                   aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['t-index.php', 't-create.php', 't-edit.php']) ? 'true' : 'false'; ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-quote-right"></i></div>
                    Testimonials
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['t-index.php', 't-create.php', 't-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseTestimonials">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 't-index.php' ? 'active' : ''; ?>" 
                           href="t-index.php">All Testimonials</a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 't-create.php' ? 'active' : ''; ?>" 
                           href="t-create.php">Add Testimonial</a>
                    </nav>
                </div>

                <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['b-index.php', 'b-create.php', 'b-edit.php']) ? 'active' : 'collapsed'; ?>" 
                   href="#" 
                   data-bs-toggle="collapse" 
                   data-bs-target="#collapseBlogs" 
                   aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['b-index.php', 'b-create.php', 'b-edit.php']) ? 'true' : 'false'; ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-blog"></i></div>
                    Blogs
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['b-index.php', 'b-create.php', 'b-edit.php']) ? 'show' : ''; ?>" 
                     id="collapseBlogs">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'b-index.php' ? 'active' : ''; ?>" 
                           href="b-index.php">All Blogs</a>
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'b-create.php' ? 'active' : ''; ?>" 
                           href="b-create.php">Add Blog</a>
                    </nav>
                </div>
                
                <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    Profile
                </a>
                <a class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <div class="sb-nav-link-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    Settings
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <div class="logged-user">admin</div>
        </div>
    </nav>
</div> 


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var sidenavAccordion = document.getElementById('sidenavAccordion');
    
    // Get all menu items with collapse
    var menuItems = document.querySelectorAll('.nav-link[data-bs-toggle="collapse"]');
    
    // Initialize all menus as collapsed
    menuItems.forEach(function(item) {
        var targetId = item.getAttribute('data-bs-target');
        var target = document.querySelector(targetId);
        
        if (target) {
            // Check if this menu should be active
            var isActive = item.classList.contains('active') || 
                          target.querySelector('.nav-link.active') !== null;
            
            if (!isActive) {
                target.classList.add('collapse');
                item.classList.add('collapsed');
                item.setAttribute('aria-expanded', 'false');
            } else {
                target.classList.remove('collapse');
                item.classList.remove('collapsed');
                item.setAttribute('aria-expanded', 'true');
            }
        }
    });

    // Add click event listeners
    menuItems.forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            var targetId = this.getAttribute('data-bs-target');
            var target = document.querySelector(targetId);
            
            if (!target) return;
            
            var isExpanded = target.classList.contains('show');
            
            // Close all other expanded items
            menuItems.forEach(function(otherItem) {
                if (otherItem !== item) {
                    var otherId = otherItem.getAttribute('data-bs-target');
                    var other = document.querySelector(otherId);
                    
                    if (other && other.classList.contains('show')) {
                        other.classList.remove('show');
                        otherItem.classList.add('collapsed');
                        otherItem.setAttribute('aria-expanded', 'false');
                    }
                }
            });
            
            // Toggle current item
            if (isExpanded) {
                target.classList.remove('show');
                this.classList.add('collapsed');
                this.setAttribute('aria-expanded', 'false');
            } else {
                target.classList.add('show');
                this.classList.remove('collapsed');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });
});
</script>