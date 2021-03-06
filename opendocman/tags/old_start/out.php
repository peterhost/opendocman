<?php
// out.php - display a list/ of all available documents that user has permission to view (with file status)
// check to ensure valid session, else redirect
session_start();
if (!session_is_registered('SESSION_UID'))
{
        header('Location:error.php?ec=1');
        exit;
}
include ('./config.php');
// includes
draw_header($title);
draw_menu($SESSION_UID);
draw_status_bar('Document Listing', $last_message);
sort_browser(); 
$query = "SELECT * FROM dept_reviewer WHERE dept_reviewer.user_id = $SESSION_UID";
$result = mysql_db_query($database, $query, $connection) or die ("Error in Query".mysql_error());
$count = mysql_num_rows($result);
$department_id = array();
$index = 0;
$user_obj = new User($SESSION_UID, $connection, $database);

if($user_obj->isReviewer() && sizeof($user_obj->getReviewee()) > 0)
{
        echo "<a href='toBePublished.php'> You have ". sizeof($user_obj->getReviewee()). " documents waiting to be reviewed!</a>  <BR>";
}

$rejected_files_obj = $user_obj->getRejectedFiles();
if( null != $rejected_files_obj[0])
{
        echo "<a href='rejects.php'> ". sizeof($rejected_files_obj)." of your documents were rejected!</a> <BR>";
}

// get a list of documents the user has "view" permission for
// get current user's information-->department
if(!$starting_index)
{
	$starting_index = 0;
}

if(!$stoping_index)
{
	$limit=$GLOBALS['CONFIG']['page_limit'];
	$stoping_index = ($starting_index+$limit-1);
}

if(!$sort_by)
{
	$sort_by = 'id';
}

if(!$sort_order)
{
	$sort_order = 'a-z';
}

if(!$page)
{
	$page = 0;
}

//set values
$page_url = $_SERVER['PHP_SELF'] . '?submit=true';
list($user_department)=mysql_fetch_row($result);
$user_perms = new UserPermission($SESSION_UID, $connection, $database);
$file_obj_array = $user_perms->getAllowedFileOBJs();
$count = sizeof($file_obj_array);

$sorted_obj_array = obj_array_sort_interface($file_obj_array, $sort_order, $sort_by);
list_files($sorted_obj_array, $user_perms, $page_url,  $GLOBALS['CONFIG']['dataDir'], $sort_order,  $sort_by, $starting_index, $stoping_index, 0);
// clean up
	
	echo '</table>';
	echo '<br>';
	$limit=$GLOBALS['CONFIG']['page_limit'];
	$total_hit = sizeof($sorted_obj_array);
	list_nav_generator($total_hit, $limit,$page_url, $page, $sort_by, $sort_order);	
	echo '</center>';
	draw_footer();	
?>
