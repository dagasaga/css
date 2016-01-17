<?php

class usersController
{
    public function register ()
    {
        // create register form
        $content = '';

        $token_handler = new security();
        $token_handler->set_token();
        $token = $token_handler->get_token();

        $register_form = "
        <form action='?controller=users&action=submit' method='post'>
          Username: <input type='text' name='username' placeholder='ex: john' autofocus>
            <br>
          email: <input type='text' name='email'>
          <br><br>
          <input type='submit' value='register'>
          <input type='hidden' value='{$token}' name='token'>
        </form>

        ";
        $content = $register_form;
        $output['content'] = $content;
        $output['page'] = 'views/register.php';
        return $output;
    }

    public function submit ()
    {
        // here, user has entered a username and an email
        // however, this is not an automatic user system; each request must be
        // assessed by the administrator
        // thus, here I'll just compose a simple mail and send it to the admin email found in the database

        // check if email was successfully sent, then:
        $token_handler = new security();
        $token_handler->check_token();

        $content  = "<p>Dear {$_POST['username']}, your account has not been created yet.";
        $content .= "<p>The administrator must first get in contact with you.";
        $content .= "<p>If you do not receive an email within a few hours, please contact the administrator directly.";
        $output['content'] = $content;
        $output['page'] = 'views/register.php';
        return $output;
        // Lastly, show that an email was send to the admin AND the user, stating that he/she will be contacted soon, with instructions
    }

    public function forgot ()
    {
        $content = '';

        $token_handler = new security();
        $token_handler->set_token();
        $token = $token_handler->get_token();

        $register_form = "
        <form action='?controller=users&action=reset' method='post'>
          Username: <input type='text' name='username' placeholder='ex: john' autofocus>
            <br>
          email: <input type='text' name='email'>
          <br><br>
          <input type='submit' value='reset'>
          <input type='hidden' value='{$token}' name='token'>
        </form>

        ";
        $content .= $register_form;
        $output['content'] = $content;
        $output['page'] = 'views/forgot.php';
        return $output;
    }
    public function reset()
    {
        $token_handler = new security();
        $token_handler->check_token();

        $content = '';

        // resets users password
        // sends an email containing a link + token with 6h validity
        // from this link, access this same method, but with confirm=yes in url


        if (isset($_GET['confirm'])) {
            if ($_GET['confirm'] == 'yes') {
                // check token with database
                // will arrive here from user's mail - show form to enter new password and UPDATE it in the database
            }
        } else {
            // send email to user with link to reset, redirecting here
            // ?controller=users&action=reset&confirm=yes&token=ETC
            // 1st, check if user + email exist in database
            $connection = new database();
            $sql = "SELECT username, email FROM users WHERE username=?";
            $data[] = $_POST['username'];
            $user_results = $connection->fetchAll($sql, $data);


            if ($connection->row_count = 1) {
                    // ok, found one user with this username
                    // but, does he/she has an email?
                if ($_POST['email'] <> '') {
                    if ($user_results[0]['email'] == $_POST['email']) {
                        // send email with proper link to reset password
                        $content .= "<p>Dear {$_POST['username']}, an email was sent to {$_POST['email']} with instructions on how to reset your password.";
                        $content .= "<p>It should arrive momentarily; if not, check your spam box or contact the administrator.";
                        // TODO: send email to reset password.
                        // Contains a link with a token that redirects to a special page - this only confirms that user has acces to the concerned email

                    } else {
                        $content .= "<p>Email not found or invalid. Please, try again.";
                        $content .= "<p>Contact the administrator if you think you do not have a registered email.";
                    }
                } else {
                    $content .= "<p>Email is obligatory. Please, try again.";
                }
            } else {
                $content .= "User not found. Please, try again!";
            }
        }
        $output['page'] = 'views/forgot.php';
        $output['content'] = $content;
        return $output;
    }

}
