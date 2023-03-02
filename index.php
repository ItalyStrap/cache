<?php
/*
Plugin Name: Cache
Description: PSR-16 implementation
Plugin URI: https://italystrap.com
Author: Enea Overclokk
Author URI: https://italystrap.com
Version: 1.0.0
License: GPL2
Text Domain: text-domain
Domain Path: Domain Path
*/

/*

    Copyright (C) Year  Enea Overclokk  Email

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require __DIR__ . '/vendor/autoload.php';

/**
 * @see \get_option()
 * @see \add_option()
 * @see \update_option()
 * @see \delete_option()
 *
 * @see \get_site_option()
 * @see \add_site_option()
 * @see \update_site_option()
 * @see \delete_site_option()
 *
 * @see \get_network_option()
 * @see \add_network_option()
 * @see \update_network_option()
 * @see \delete_network_option()
 *
 * @see \get_transient()
 * @see \set_transient()
 * @see \delete_transient()
 *
 * @see \get_site_transient()
 * @see \set_site_transient()
 * @see \delete_site_transient()
 *
 * @see \wp_cache_add()
 * @see \wp_cache_get()
 * @see \wp_cache_set()
 * @see \wp_cache_delete()
 * @see \wp_cache_flush()
 *
 * @see \wp_cache_replace()
 * @see \wp_cache_get_multiple()
 */

/**
 * debug_example
 */
//function cache_example() {
//
//}
//
//add_action( 'wp_footer', 'cache_example' );
