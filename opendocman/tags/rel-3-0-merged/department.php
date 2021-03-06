<?php
/*
   department.php - Administer Departments
   Copyright (C) 2002, 2003, 2004  Stephen Lawrence

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

// check for valid session 
session_start();
if (!isset($_SESSION['uid']))
{
    header('Location:index.php?redirection=' . urlencode( $_SERVER['PHP_SELF'] . '?' . $HTTP_SERVER_VARS['QUERY_STRING'] ) );
    exit;
}

// includes
include('config.php');
// Check to see if user is admin
$user_obj = new User($_SESSION['uid'], $GLOBALS['connection'], $GLOBALS['database']);
$secureurl = new phpsecureurl;
if(!$user_obj->isAdmin())
{
    header('Location:' . $secureurl->encode('error.php?ec=4'));
    exit;
}

// open a connection to the database
if(isset($_REQUEST['submit']) and $_REQUEST['submit'] != 'Cancel')
{
    header('Location:' . $secureurl->encode('error.php?ec=4'));
    exit;
}


/*
   Add A New Department
 */
if(isset($_GET['submit']) && $_GET['submit']=='add')
{
    if (!isset($_POST['last_message']))
    {
        $_POST['last_message']='';
    }
    draw_header('Add New Department');
    draw_menu($_SESSION['uid']);
    draw_status_bar('Add New Department', $_POST['last_message']);
    ?>
<center>
 <table border="0" cellspacing="5" cellpadding="5">
   <form action="commitchange.php" method="POST" enctype="multipart/form-data">
    <tr>
     <td><b>Department</b></td>
     <td colspan="3"><input name="department" type="text"></td>
    </tr>
	<input type="hidden" name="submit" value="Add Department">
    <tr>
    <td></td>
     <td colspan="4" align="center"><input type="Submit" name="submit" value="Add Department"></td>
    </form>
    </tr>
    <tr>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <td></td>
     <td align="center">
      <input type="Submit" name="submit" value="Cancel">
     </td>
     </form>
    </tr>
  </table>
  </center>
<?php
draw_footer();
}
elseif(isset($_REQUEST['submit']) && $_REQUEST['submit'] =='delete')
{
        // If demo mode, don't allow them to update the demo account
        if (@$GLOBALS['CONFIG']['demo'] == 'true')
        {
                @draw_status_bar('Delete Department ' ,$_POST['last_message']);
                echo 'Sorry, demo mode only, you can\'t do that';
                draw_footer();
                exit;
        }
        if (!isset($_POST['last_message']))
        {
                $_POST['last_message']='';
        }
	$delete='';
	draw_header('Department Deletion');
	draw_status_bar('Delete Department', $_POST['last_message']);
 // query to show item
	echo '<center>'; 
	echo '<table border="0">';
	$query = "SELECT id, name FROM " . $GLOBALS['CONFIG']['table_prefix'] . "department where id='$_POST[item]'";
    $result = mysql_query($query, $GLOBALS['connection']) or die ("Error in query: $query. " . mysql_error());
    while(list($lid, $lname) = mysql_fetch_row($result))
    {
        echo '<tr><td>Id # :</td><td>' . $lid . '</td></tr>';
        echo '<tr><td>Name :</td><td>' . $lname . '</td></tr>';
    }
?>
    <form action="commitchange.php?id=<?php echo $_POST['item'];?> " method="POST" enctype="multipart/form-data">
     <tr>
      <td valign="top">Are you sure you want to delete this?</td>
	  <td colspan="4" align="center"><input type="Submit" name="deletedepartment" value="Yes"></td>
    </form>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
     <td colspan="4" align="center"><input type="Submit" name="" value="No, Cancel"></td>
    </form>
    </tr>
   </form>
<?php
draw_footer();
}
elseif(isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'deletepick')
{
        if (!isset($_POST['last_message']))
        {
                $_POST['last_message']='';
        }
$deletepick='';
draw_header('Department Selection');
draw_status_bar('Choose Department to Delete', $_POST['last_message']);
?>
    <center>
        <table border="0" cellspacing="5" cellpadding="5">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
        <tr>
        <td><b>Department</b></td>
        <td colspan="3"><select name="item">
<?php
	$query = 'SELECT id, name FROM ' . $GLOBALS['CONFIG']['table_prefix'] . 'department ORDER BY name';
	$result = mysql_query($query, $GLOBALS['connection']) or die ("Error in query: $query. " . mysql_error());
    while(list($lid, $lname) = mysql_fetch_row($result))
    {
        $str = '<option value="' . $lid . '"';
        $str .= '>' . $lname . '</option>';
        echo $str;
    }
    mysql_free_result ($result);
    $deletepick='';
?>
    </select></td>
    <tr>
     <td colspan="4" align="center"><input type="Submit" name="delete" value="Delete"></td>
    </tr>
    </form>
   	</table>
	</center>
    </body>
    </html>
<?php
}
elseif(isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'showitem')
{
        if (!isset($_POST['last_message']))
        {
                $_POST['last_message']='';
        }
 // query to show item
	draw_header('Department Information');
    draw_menu($_SESSION['uid']);
 	draw_status_bar('Display Item Information', $_POST['last_message']);
    echo '<center>';
	//select name
	$query = "SELECT name,id FROM " . $GLOBALS['CONFIG']['table_prefix'] . "department where id='$_POST[item]'";
	$result = mysql_query($query, $GLOBALS['connection']) or die ("Error in query: $query. " . mysql_error());
    echo '<table name="main" cellspacing="15" border="0">';
    echo '<th>ID</th><th>Dept. Name</th>';
	list($ldepartment) = mysql_fetch_row($result);
	echo '<tr><td>' . $_POST['item'] . '</td>';
	echo '<td>' . $ldepartment . '</td></tr>';
?>
    <tr>
    <td align="center" colspan="2"><b>Users in this department</b></td>
    </tr>
<?php
    // Display all users assigned to this department
    $query = "SELECT " . $GLOBALS['CONFIG']['table_prefix'] . "department.id, " . $GLOBALS['CONFIG']['table_prefix'] . "user.first_name, " . $GLOBALS['CONFIG']['table_prefix'] . "user.last_name FROM " . $GLOBALS['CONFIG']['table_prefix'] . "department, " . $GLOBALS['CONFIG']['table_prefix'] . "user where " . $GLOBALS['CONFIG']['table_prefix'] . "department.id='$_POST[item]' and " . $GLOBALS['CONFIG']['table_prefix'] . "user.department='$_POST[item]'";
    $result = mysql_query($query, $GLOBALS['connection']) or die ("Error in query: $query. " . mysql_error());
    while(list($lid, $lfirst_name, $llast_name) = mysql_fetch_row($result))
	{	
        echo '<tr><td colspan="2">'.$lfirst_name.' '.$llast_name.'</td></tr>';
    }
?>
    <form action="admin.php?last_message=<?php echo $_POST['last_message']; ?>" method="POST" enctype="multipart/form-data">
    <tr>
     <td colspan="4" align="center"><input type="Submit" name="" value="Back"></td>
    </tr>
    </table>
   </form>
<?php
draw_footer();
}
elseif(isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'showpick')
{
    if (!isset($_POST['last_message']))
    {
        $_POST['last_message']='';
    }
    draw_header('Department Selection');
    draw_menu($_SESSION['uid']);
    draw_status_bar('Choose item to view', $_POST['last_message']);
    $showpick='';
    ?>
	<center>
	<table border="0" cellspacing="5" cellpadding="5">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?last_message=<?php echo $_POST['last_message']; ?>" method="POST" enctype="multipart/form-data">
	<tr>
	<input type="hidden" name="state" value="<?php echo ($_GET['state']+1); ?>">
	<td><b>Department</b></td>
	<td colspan=3><select name="item">
<?php 
	$query = 'SELECT id, name FROM ' . $GLOBALS['CONFIG']['table_prefix'] . 'department ORDER BY name';
	$result = mysql_query($query, $GLOBALS['connection']) or die ("Error in query: $query. " . mysql_error());
	while(list($lid, $lname) = mysql_fetch_row($result))
	{
		echo '<option value="' . $lid . '">' . $lname . '</option>';
	}

	mysql_free_result ($result);
?>
	</select></td>
	<tr>
	<td colspan="2" align="center"><input type="Submit" name="submit" value="Show Department">
	</form><p>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="Submit" name="submit" value="Cancel">
	</form>
	</td>
	</tr>
	</table>
	</center>
<?php 
draw_footer();
}
elseif(isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'modify')
{
        if (!isset($_POST['last_message']))
        {
                $_POST['last_message']='';
        }
	$dept_obj = new Department($_REQUEST['item'], $GLOBALS['connection'], $GLOBALS['database']);
	draw_header('Department Update');
    draw_menu($_SESSION['uid']);
	draw_status_bar('Update Department: ' . $dept_obj->getName(),$_POST['last_message']);
?>
	<center>
	<table border="1" cellspacing="5" cellpadding="5">
	 <form action="commitchange.php" method="POST" enctype="multipart/form-data">
	  <tr>
<?php
	// query to get a list of users
	$query = "SELECT id, name FROM " . $GLOBALS['CONFIG']['table_prefix'] . "department where id='$_REQUEST[item]'";
	$result = mysql_query($query, $GLOBALS['connection']) or die ("Error in query: $query. " . mysql_error());
	while(list($lid, $lname) = mysql_fetch_row($result))
	{
?>
               <tr>
		<td><input type="textbox" name="name" value="<?php echo $lname; ?>"></td>
		<td><input type="hidden" name="id" value="<?php echo $lid; ?>"></td>
               </tr>
<?php
	}
	mysql_free_result ($result);
?>        </tr>
	  <tr>
           <td>
            <input type="Submit" name="submit" value="Update Department">
           </td>
          </form>
          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" >
           <input type="Submit" name="submit" value="Cancel">
          </form>
        </tr>
	</table>
	</center>
<?php
	draw_footer();
}
elseif(isset($_REQUEST['submit']) && $_REQUEST['submit'] == 'updatepick')
{
        if (!isset($_POST['last_message']))
        {
                $_POST['last_message']='';
        }
	draw_header('Department Selection');
    draw_menu($_SESSION['uid']);
	draw_status_bar('Modify Department',$_POST['last_message']);
?>
	<center>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" enctype="multipart/form-data">
	<INPUT type="hidden" name="state" value="<?php echo ($_REQUEST['state']+1); ?>">
	<table border="0" cellspacing="5" cellpadding="5">
	<tr>
	<td><b>Department to modify:</b></td>
	<td colspan="3"><select name="item">
<?php
	// query to get a list of departments
	$query = "SELECT id, name FROM " . $GLOBALS['CONFIG']['table_prefix'] . "department ORDER BY name";
	$result = mysql_query($query, $GLOBALS['connection']) or die ("Error in query: $query. " . mysql_error());

	while(list($lid, $lname) = mysql_fetch_row($result))
	{
		echo '<option value="' . $lid . '">' . $lname . '</option>';
	}
	mysql_free_result ($result);
?>
	</td>
	</tr>
	<tr>
	<td colspan="4" align="right">
	<input type="Submit" name="submit" value="modify">
	</td>
	</form>
	<td colspan="4" align="center">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<input type="Submit" name="submit" value="Cancel">
	</form>
	</td>
	</tr>
	</table>
	</center>
    <?php
    draw_footer();
}
elseif (isset($_POST['submit']) and $_POST['submit'] == 'Cancel')
{
    
    $_POST['last_message'] ="Action Cancelled";
    header('Location:' . $secureurl->encode('admin.php'));
}
else
{
    $_POST['last_message'] ="Unrecognizable action";
    header('Location:' . $secureurl->encode('admin.php'));
}

?>
