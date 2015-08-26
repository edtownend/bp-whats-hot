<?php
/*
Plugin Name: What's Hot Activity Tab for BuddyPress
Plugin URI: http://dmsqd.com
Description: Adds a What's Hot Tab to the Activity Stream in BuddyPress.
Author: DMSQD
Version: 0.2
Author URI: http://dmsqd.com
Text Domain: bp-whats-hot
*/

/**
 * Create the tab
 */

function bp_whats_hot_add_tab() {
    ?>
    <li class="" id="activity-whats-hot">
        <a href="<?php bp_activity_directory_permalink(); ?>" title="<?php esc_attr_e( 'The top content', 'bp-whats-hot' ); ?>"><?php printf( __( "What's Hot", 'bp-whats-hot' )); ?></a>
    </li>
    <?php
}
add_action( 'bp_before_activity_type_tab_friends', 'bp_whats_hot_add_tab');

/**
 * Amend the query
 */

function bp_whats_hot_amend_query($activity_ids_sql) {

    // Check scope is whats-hot
    if ( false === strpos(bp_ajax_querystring('activity'), 'scope=whats-hot') ) {
        return $activity_ids_sql;
    }

    // This is the goal
    // SELECT DISTINCT a.id FROM wp_bp_activity a INNER JOIN wp_bp_activity c ON a.id = c.secondary_item_id WHERE a.is_spam = 0 AND a.hide_sitewide = 0 AND a.type NOT IN ('activity_comment', 'last_activity') ORDER BY c.date_recorded DESC LIMIT 0, 21

    // Add inner join clause
    $where_pos = strpos($activity_ids_sql, 'WHERE');
    $activity_ids_sql = substr($activity_ids_sql, 0, $where_pos)
        . ' INNER JOIN wp_bp_activity c ON a.id = c.secondary_item_id '
        . substr($activity_ids_sql, $where_pos);

    // Alter ORDER BY
    $activity_ids_sql = preg_replace(
        '/ORDER BY a.date_recorded (DESC|ASC)/',
        'ORDER BY GREATEST(c.date_recorded, a.date_recorded) DESC',
        $activity_ids_sql
    );

    return $activity_ids_sql;
}
add_filter('bp_activity_paged_activities_sql', 'bp_whats_hot_amend_query');
