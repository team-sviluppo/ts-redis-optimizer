<?php
/**
 * Plugin Name: Team Sviluppo Redis Optimizer
 * Description: Ottimizza l'integrazione con la cache REDIS
 * Github Plugin URI: https://www.teamsviluppo.it
 * Version: 1.1
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Fix ticket 31245 for wordpress and redis integration
 *
 * @see https://core.trac.wordpress.org/ticket/31245
 */
function wp_ticket_31245_patch( $option ) {
    if ( ! wp_installing() ) {
        $alloptions = wp_load_alloptions(); //alloptions should be cached at this point
        if ( isset( $alloptions[ $option ] ) ) { //only if option is among alloptions
            wp_cache_delete( 'alloptions', 'options' );
        }
    }
}
add_action( 'added_option',   'wp_ticket_31245_patch' );
add_action( 'updated_option', 'wp_ticket_31245_patch' );
add_action( 'deleted_option', 'wp_ticket_31245_patch' );

/**
 * Clear cache on post published, updated and after plugin update
 *
 */

function manual_clear_redis_cache() {
	if( class_exists( 'Redis' ) ) {
		$r = new Redis();
		if ($r->connect( WP_REDIS_PATH, 0 )) {
			if( false === wp_cache_flush() ) {
				if( WP_DEBUG === true ) {
					error_log( 'After post publish: Flushing PHP Redis failed.' );
				}
				return false;
			}
			else {
				if( WP_DEBUG === true ) {
					error_log( 'After post publish: PHP Redis flushed succesfully.' );
				}
				return true;
			}
		}
		else {
			if( WP_DEBUG === true ) {
				error_log( 'Could not connect to PHP Redis.' );
			}
		}
	}
}
add_action( 'publish_post', 'manual_clear_redis_cache', 10, 2 );
add_action( 'post_updated', 'manual_clear_redis_cache', 10, 3 );
add_action( 'upgrader_process_complete', 'manual_clear_redis_cache', 10, 2 );