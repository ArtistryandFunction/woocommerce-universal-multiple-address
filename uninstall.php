<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// delete all the custom rows the plugin created
global $wpdb;
// code for deleting rows goes here... https://wordpress.stackexchange.com/questions/125380/how-to-delete-all-records-from-or-empty-a-custom-database-table ... https://developer.wordpress.org/reference/classes/wpdb/delete/
// future versions will have admin settings and will ask admin users if they want keep are delete records
?>
