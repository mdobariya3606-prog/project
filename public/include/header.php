<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="../css/style.css" >
</head>
<body>
    <header class="header">
        <h2>Google Cloud</h2> 
        <nav>
            <a href="../admin/dashboard.php" class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
            <a href="list_file.php" class="<?php echo ($currentPage === 'list_file.php') ? 'active' : ''; ?>">List files</a>
            <a href="add_file.php" class="<?php echo ($currentPage === 'add_file.php') ? 'active' : ''; ?>">Add file</a>
            <a href="share_file.php" class="<?php echo ($currentPage === 'share_file.php') ? 'active' : ''; ?>">Share file</a>
            <a href="../user/profile.php" class="<?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">Profile</a>
            <a href="../user/change-password.php" class="<?php echo ($currentPage === 'change-password.php') ? 'active' : ''; ?>">Reset password</a>
            <a href="../auth/logout.php" onclick="return confirm('Logout???')">Logout</a>
        </nav>
    </header>
</body>
</html>