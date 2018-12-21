<?php
/*
Plugin Name: NERVA - WooCommerce Gateway
Plugin URI: https://getnerva.org
Description: Extends WooCommerce by Adding the NERVA Gateway
Version: 2.0
Author: NERVA Project
Author URI: https://getnerva.org
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'nerva_init', 0);


function nerva_gateway($methods)
{
	$methods[] = 'Nerva_Gateway';
	return $methods;
}

function nerva_add_my_currency($currencies)
{
	$currencies['XNV'] = __('Nerva', 'woocommerce');
	return $currencies;
}

function nerva_add_my_currency_symbol($currency_symbol, $currency)
{
	switch ($currency) {
		case 'XNV':
			$currency_symbol = 'XNV';
			break;
	}
	return $currency_symbol;
}


function nerva_scripts(){
	wp_enqueue_style('materials_icons', 'https://fonts.googleapis.com/icon?family=Material+Icons');
	wp_enqueue_style('montserrat_font', 'http://fonts.googleapis.com/css?family=Lato:400,700');
	wp_enqueue_style('nerva_gateway-style',  plugin_dir_url( __FILE__ ).'assets/style.css');
}

function nerva_init()
{
    /* If the class doesn't exist (== WooCommerce isn't installed), return NULL */
    if (!class_exists('WC_Payment_Gateway')) return;


    /* If we made it this far, then include our Gateway Class */
    include_once('include/nerva_payments.php');
    require_once('library.php');
	
	add_filter('woocommerce_currencies', 'nerva_add_my_currency');
	add_filter('woocommerce_currency_symbol', 'nerva_add_my_currency_symbol', 10, 2);
	
	// Lets add it too WooCommerce
	add_filter('woocommerce_payment_gateways', 'nerva_gateway');
	
	//registering css/js
	add_action( 'wp_enqueue_scripts', 'nerva_scripts' );
}

/*
 * Add custom link
 * The url will be http://yourworpress/wp-admin/admin.php?=wc-settings&tab=checkout
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'nerva_payment');
function nerva_payment($links)
{
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '">' . __('Settings', 'nerva_payment') . '</a>',
    );

    return array_merge($plugin_links, $links);
}

add_action('admin_menu', 'nerva_create_menu');
function nerva_create_menu()
{
    add_menu_page(
        __('Nerva', 'textdomain'),
        'Nerva',
        'manage_options',
        'admin.php?page=wc-settings&tab=checkout&section=nerva_gateway',
        '',
        plugins_url('nerva/assets/png-nerva-logo-16x16.png'),
        56 // Position on menu, woocommerce has 55.5, products has 55.6

    );
}


//register for installation
function nerva_plugin_activate() {
	include_once('include/nerva_payments.php');
	Nerva_Gateway::install();
}
register_activation_hook( __FILE__, 'nerva_plugin_activate' );


function nervaGateway_ajaxReload(){
	$gateway = new Nerva_Gateway();
	$gateway->handlePaymentAjax();
	exit;
}

add_action( 'wp_ajax_nerva_gateway_ajax_reload', 'nervaGateway_ajaxReload' );
add_action( 'wp_ajax_nopriv_nerva_gateway_ajax_reload', 'nervaGateway_ajaxReload' );

function sv_wc_add_order_meta_box_action( $actions ) {
    global $theorder;

	if ( $theorder->is_paid() ){
        return $actions;
    }

    $actions['wc_custom_order_action'] = __( 'Recheck payment', 'my-textdomain' );
    return $actions;
}

function sv_wc_recheck_payment( $order )
{
	$payid = $order->get_meta('Payment ID');
	$amount = $order->get_meta('Amount requested (XNV)');
    $message = sprintf( __( 'Payment rechecked by %s', 'my-textdomain' ), wp_get_current_user()->display_name );
	$order->add_order_note( $message );

	$gateway = new Nerva_Gateway();
	$gateway->verify_payment($payid, $amount, $order);
}

add_action( 'woocommerce_order_actions', 'sv_wc_add_order_meta_box_action' );	
add_action( 'woocommerce_order_action_wc_custom_order_action', 'sv_wc_recheck_payment' );