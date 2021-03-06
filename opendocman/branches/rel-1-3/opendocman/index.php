<?php
// Report all PHP errors (bitwise 63 may be used in PHP 3)
// includes
session_start();
require('config.php');

if(!isset($_POST['login']) && $GLOBALS['CONFIG']['authen'] =='mysql')
{
    if($GLOBALS['CONFIG']['SSL_enforced'] == 'On' && !isset($_SERVER['HTTPS']))   header('Location: https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
	
	
	?>
<!--

        index.php - displays a login form

        -->
        <html>
        <head>
        <TITLE><?php echo $GLOBALS['CONFIG']['title']; ?></TITLE>
        <basefont face="Verdana">
        </head>

        <body bgcolor="White">

        <table cellspacing="0" cellpadding="0">
        <tr>
        <td align="left"><img src="images/logo.gif" alt="Site Logo" border=0></td>
        </tr>
        </table>

        <table border="0" cellspacing="5" cellpadding="5">
        <tr>
        <td valign="top">
        <center>
        <table border="0" cellspacing="5" cellpadding="5">
        <form action="index.php" method="post">
        <?php
		if(isset($_REQUEST['redirection']))
			echo '<input type="hidden" name="redirection" value="' . $_REQUEST['redirection'] . '">' . "\n"; ?>
		<tr>
        <td><?php echo $GLOBALS['lang']['username']; ?></td>
        <td><input type="Text" name="frmuser" size="15"></td>
        </tr>
        <tr>
        <td><?php echo $GLOBALS['lang']['password']; ?></td>
        <td><input type="password" name="frmpass" size="15"></td>
        </tr>
        <tr>
        <td colspan="2" align="CENTER"><input type="Submit" name="login" value="<?php echo $GLOBALS['lang']['enter']; ?>"></td>
		
        </tr>
        </form>
        </table>
        </center>
        </td>
        <td valign="top">
        <?php echo $GLOBALS['lang']['welcome']; ?>
        <p>
        <?php echo $GLOBALS['lang']['welcome2']; ?>
		<p>
		<a href="anonymous.php?mode=showall"><?php echo $GLOBALS['lang']['anonymous_link']; ?></a>
        </td>
        <td width="20%">
        &nbsp;
    </td>
        </tr>
        </table>

        </center>

<?php
        draw_footer();
}
elseif(isset($_POST['login']))
{
    if(!valid_username($_POST['frmuser']))
    {
        echo "<font color=red>The username or password was invalid. Please try again.</font>";
        exit;
    }


    $frmuser = $_POST['frmuser'];
    $frmpass = $_POST['frmpass'];
        // check login and password
        // connect and execute query
        $query = "SELECT id, username, password FROM " . $GLOBALS['CONFIG']['table_prefix'] . "user WHERE username = '$frmuser' AND password = password('$frmpass')";
        $result = mysql_query("$query") or die ("Error in query: $query. " . mysql_error());
        // if row exists - login/pass is correct
        if (mysql_num_rows($result) == 1)
        {
                // register the user's ID
                list($id, $username, $password) = mysql_fetch_row($result);
                // initiate a session
                $_SESSION['uid'] = $id;
                // redirect to main page
                if(isset($_REQUEST['redirection']))
					header('Location:' . $_REQUEST['redirection']);
				else
					header('Location:out.php');
                mysql_free_result ($result);	
                // close connection
        }
        else
                // login/pass check failed
        {
                mysql_free_result ($result);	
                // redirect to error page
                header('Location: error.php?ec=0');
        }

}
elseif($GLOBALS['CONFIG']['authen'] =='kerbauth')
{

        // check login and password
        // connect and execute query
        if (!isset($_COOKIE['AuthUser']))
        {
                header('Location: https://secureweb.ucdavis.edu:443/cgi-auth/sendback?'.$GLOBALS['CONFIG']['base_url']);
        }
        else
        {
                list ($userid, $id2, $id3) = split ('[-]', $_COOKIE['AuthUser']);
                //// query to get id num from username
                $query = "SELECT id FROM " . $GLOBALS['CONFIG']['table_prefix'] . "user WHERE username='$userid'";
                $result = mysql_query($query) or die ('Error in query: '.$query . mysql_error());
                // if row exists then the user has an account
                if (mysql_num_rows($result) == 1)
                {
                        // initiate a session
                        session_start();
                        // register the user's ID
                        session_register('uid');
                        list($id) = mysql_fetch_row($result);
                        $_SESSION['uid'] = $id;
                        // redirect to main page
                        header('Location:out.php');
                        mysql_free_result ($result);	
                        // close connection
                }
                // User passed auth, but does not have an account
                else 
                {
                        header('Location:error.php?ec=19');
                }
        }
}
else
{
        echo 'Check your config';
}

?>
