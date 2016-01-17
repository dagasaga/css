<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="content-language" content="fr" />
    <title>CSS AEC-Foyer Lataste - ADTJK</title>

    <link rel="stylesheet" type="text/css" href="login_style.css" />

</head>


<body>
<img src=<?php echo "'http://".WEBSITE_URL."/images/logo.png'"; ?> alt="logo" class="login_logo">

<div id="content">

    <div class="login-card">
        <h1>Register</h1>
        <?php if (isset($output['register_form'])) echo $output['register_form']; ?>

        <?php if (isset($output['content'])) echo $output['content']; ?>

        <div class="login-help">
            <a href="?controller=login&action=login">Login</a> | <a href="?controller=users&action=forgot">Forgot Password</a>
        </div>
    </div>
</div>

<div id="footer">
    <?php if (isset($output['footer'])) echo $output['footer']; ?>
</div>

</body>


</html>