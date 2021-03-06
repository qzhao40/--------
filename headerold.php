
<header>
  <a id="logo-link" href="/" style="padding:0px;margin:0px">
    <div id="logo">&nbsp;</div>
    <div id="logotext">Manitoba Genealogical Society</div>
  </a>

  <div class="navDash">
    <ul>
      <?php if (session_name() === 'login') : ?>
        <ul>
          <?php if ($_SESSION['verified']) : ?>
            <li><a href="/member/" class="head-link">Search</a></li>
            <?php if ($_SESSION['Type'] === 8 || $_SESSION['Type'] >= 10 && $_SESSION['Type'] <= 12) : ?>
              <li><a href="/volunteer/" class="head-link">Volunteer</a></li>
            <?php endif ?>
            <?php if ($_SESSION['Type'] === 6) : ?>
              <li><a href="/member/membersList.php" class="head-link">Branch Member List</a></li>
            <?php endif ?>
            <?php if ($_SESSION['Type'] === 10 || $_SESSION['Type'] === 12) : ?>
              <li><a href="/volunteer/Memberships/" class="head-link">Memberships</a></li>
            <?php endif ?>
            <?php if ($_SESSION['Type'] === 10) : ?>
              <li><a href="/admin/" class="head-link">Admin</a></li>
            <?php endif ?>
            <li><a href="/store/" class="head-link">Store</a></li>
            <li><a href="/ErrorForm.php" class="head-link">Error Report</a></li>
          <?php endif ?>
          <?php if (strtolower($_SESSION['uname']) !== 'inhouse') : ?>
            <li><a href="/myAccount/" class="head-link">My Account</a></li>
          <?php endif ?>
          <li><?php require 'logout.php' ?></li>
        </ul>
        <div id="submenu"></div>
      <?php else : ?>
        <li><a href="/login.php" class="head-link">Login &nbsp;</a></li>
        <li><a> | </a></li>
        <li><a href="/register/" class="head-link">&nbsp; Register &nbsp;</a></li>
        <li><a> | </a></li>
        <li><a href="/store/" class="head-link">Store</a></li>
      <?php endif ?>
    </ul>
  </div>
</header>

<?php if (isset($_SESSION['verified']) && !$_SESSION['verified']) : ?>
  <script type="text/javascript">
    localStorage.subheader = 'my account';
  </script>
<?php endif ?>

<?php if (session_name() === 'login') : ?>
  <script src="/subheader.js"></script>
<?php else : ?>
  <script type="text/javascript">
    localStorage.removeItem('subheader');
  </script>
<?php endif ?>
