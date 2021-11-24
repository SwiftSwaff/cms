<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Ophidian CMS</title>
        <link rel='stylesheet' href='/admin/css/style.css'>
        <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
        <script type='text/javascript' src='/admin/js/admin.js'></script>
    </head>
    <body>
        <div id='wrapper'>
            <header>
                Logged in as: <?php echo $_SESSION["User"]; ?> (<a href='/admin/logout.php'>Logout</a>)
            </header>
            <div class='flex-container'>
                <?php include getenv("DOCUMENT_ROOT") . "/admin/components/nav.php"; ?>
                <main><?php echo $html; ?></main>
            </div>
        </div>
    </body>
</html>