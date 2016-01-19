<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="content-language" content="fr" />
        <title>CSS AEC-Foyer Lataste - ADTJK</title>

        <link rel="stylesheet" type="text/css" href="style.css" />
        <link rel="shortcut icon" href="http://ivanbragatto.byethost24.com/ico/favicon.ico">
    </head>


    <body>
        <div id="header">
            <div id="logo">
                <img src="images/logo.png" height="50px">
            </div>

            <div id="centeredmenu">
                <ul>
                    <nav>
                    <?php if (isset($output['main_menu'])) echo $output['main_menu']; ?>
                    </nav>

                </ul>

            </div>
            <div class="school_year_header">
                <?php if (isset($_SESSION['current_school_year'])) echo $_SESSION['current_school_year']; ?>
            </div>

            <div class="upright">
                <?php if (isset($_SESSION['css_username'])) echo "Hello {$_SESSION['css_username']} | "; ?>

                <?php if (isset($output['upright_menu'])) echo $output['upright_menu']; ?>

            </div>
        </div>

        <div id="content">
            <div class="page_path"><?php echo 'Showing '; if (isset($_GET['controller'])) echo $_GET['controller']; ?>
            <?php

                //if (isset($_SESSION['current_school_year_id'])) echo $_SESSION['current_school_year_id'].' - ';
                if (isset($output['menu2'])) echo $output['menu2'];
            ?>

            </div>
            <div id="scrollableContent">

                <div id="paddingContent">

                    <?php if (isset($output['content'])) echo $output['content']; ?>


                </div>


            </div>

        </div>

        <div id="footer">
            <?php if (isset($output['footer'])) {
                echo $output['footer'];
            } else {
                $current_year = date ('Y');
                echo "Copyright {$current_year} - all rights reserved - webmastermind: ivan.bragatto@gmail.com";
            }?>
        </div>

    <?php if (file_exists ('js/functions.php')) require_once 'js/functions.php'; ?>
    </body>

</html>
