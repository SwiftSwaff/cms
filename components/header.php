<?php
if (!defined('IndexAccessed')) {
    die('Direct access not permitted');
}
?>
<header>
    <div class='header-wrap'>
        <div id='brand-icon'>
            <a href='/'>
                <picture>
                    <source srcset='/resources/xll_logo.webp' type='image/webp'>
                    <img alt='XLL Logo' width=185 height=73 src='/resources/xll_logo.png'>
                </picture>
            </a>
        </div>
        <?php include getenv("DOCUMENT_ROOT") . '/components/nav.php'; ?>
    </div>
</header>