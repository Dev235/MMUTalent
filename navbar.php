<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$hideSidebar = in_array($currentPage, ['login.php', 'registration.php']);
?>

<?php if (!$hideSidebar): ?>
    <!-- ☰  open-button -->
    <span id="openBtn" onclick="openNavBar()">☰</span>

    <!-- ── SIDE NAVIGATION ─────────────────────────────────────────────── -->
    <nav id="mySidenav" class="sideNavBar">
        <span id="closeBtn" onclick="closeNavBar()">&times;</span>
        <h3>Side Navigation Bar</h3>
        <a href="#">Menu 1</a>
        <a href="#">Menu 2</a>
        <a href="#">Menu 3</a>
        <a href="#">Menu 4</a>
</nav>
    
<!-- toggle logic -->
    <script src="js/navbar.js" defer></script>
<?php endif; ?>