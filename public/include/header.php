<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header class="header">
        <h1 class="title">DocVault</h1>
        <nav>

            <?php if ($_SESSION['admin']) { ?>
                <a href="../admin/dashboard.php" class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
                <a href="../admin/manage-users.php" class="<?php echo ($currentPage === 'manage-users.php') ? 'active' : ''; ?>">Manage users</a>
                <a href="../admin/all-uploaded-files.php" class="<?php echo ($currentPage === 'all-uploaded-files.php') ? 'active' : ''; ?>">All Uploaded files</a>
            <?php } else { ?>
                <a href="../user/dashboard.php" class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
            <?php } ?>

            <a href="../files/all-files.php" class="<?php echo ($currentPage === 'all-files.php') ? 'active' : ''; ?>">Manage Files</a>

            <?php if (!$_SESSION['admin']) { ?>
                <a href="../files/shared-files.php" class="<?php echo ($currentPage === 'shared-files.php') ? 'active' : ''; ?>">Shared file</a>
                <a href="../user/change-password.php" class="<?php echo ($currentPage === 'change-password.php') ? 'active' : ''; ?>">Change password</a>
            <?php } ?>
            <a href="../user/profile.php" class="<?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">Profile</a>
            <a href="../auth/logout.php" onclick="return confirm('Logout???')">Logout</a>
        </nav>
    </header>
</body>

</html>