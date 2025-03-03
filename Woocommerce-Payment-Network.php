<?php
/*
Plugin Name: WooCommerce InfinityPay
Plugin URI: http://woothemes.com/woocommerce/
Description: Provides the InfinityPay Payment Gateway for WooCommerce
Version: 1.3
Author: InfinityPay
Author URI: https://www.infinitypay.co.uk/
License: GPL2
*/

/*  Copyright 2019  InfinityPay  (support@infinitypay.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Flooroston, MA  02110-130, B1  USA

*/

/**
 * Initialise WC InfinityPay Gateway
 **/
add_action('plugins_loaded', 'init_wc_infinity_pay', 0);

/**
 * Initialise WC InfinityPay Gateway
 **/
if(function_exists('setup_module_database_tables')) {
    register_activation_hook( __FILE__, 'setup_module_database_tables' );
}

/**
 * Delete InfinityPay Gateway
 **/
if(function_exists('delete_plugin_database_table')) {
    register_uninstall_hook(__FILE__, 'delete_plugin_database_table');
}


function init_wc_infinity_pay() {

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    add_filter('plugin_action_links', 'add_wc_infinity_pay_action_plugin', 10, 5);

    include('includes/class-wc-payment-network.php');

    add_filter('woocommerce_payment_gateways', 'add_infinity_pay_payment_gateway' );

}

function add_wc_payment_network_action_plugin($actions, $plugin_file)
{
    static $plugin;

    if (!isset($plugin))
    {
        $plugin = plugin_basename(__FILE__);
    }

    if ($plugin == $plugin_file)
    {
        $actions = array_merge(array('settings' => '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=InfinityPay') . '">' . __('Settings', 'General') . '</a>'), $actions);
    }

    return $actions;
}

function add_infinity_pay_payment_gateway($methods) {
    $methods[] = 'WC_infinity_pay';
    return $methods;
}


function setup_module_database_tables() {
    $module_prefix = 'infinity_pay_';
    global $wpdb;
    global $jal_db_version;

    //Wallet table name.
    $wallet_table_name = $wpdb->prefix . 'woocommerce_' . $module_prefix . 'wallets';

    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wallet_table_name)) ===  null) {
        $table_name = $wallet_table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
			id BIGINT(9) NOT NULL AUTO_INCREMENT,
			merchants_id INT NOT NULL,
			users_id BIGINT NOT NULL,
			wallets_id BIGINT NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('jal_db_version', $jal_db_version);
    }
}

function delete_plugin_database_table() {
    $module_prefix = 'infinity_pay_';
    global $wpdb;
    $wpdb->show_errors();
    $table_name = $wpdb->prefix . 'woocommerce_' . $module_prefix . 'wallets';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    //delete_option("my_plugin_db_version");
    //error_log('Logging SQL table drop');
}
