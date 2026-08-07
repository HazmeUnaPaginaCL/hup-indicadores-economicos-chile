<?php
/**
 * Plugin Name: HUP - Indicadores Económicos (Chile)
 * Description: Muestra indicadores económicos de Chile (UF, UTM, Dólar, Euro, IPC y más) obtenidos en tiempo real desde la API pública de mindicador.cl. Shortcode [hupind_indicadores] configurable, listo para Bricks Builder, Elementor y cualquier área del sitio.
 * Version:     1.1.1
 * Author:      HazmeUnaPagina.cl
 * Author URI:  https://hazmeunapagina.cl
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hup-indicadores-economicos-chile
 * Requires at least: 6.0
 * Tested up to: 7.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'HUPIND_VERSION',  '1.1.1' );
define( 'HUPIND_PATH',     plugin_dir_path( __FILE__ ) );
define( 'HUPIND_URL',      plugin_dir_url( __FILE__ ) );
define( 'HUPIND_BASENAME', plugin_basename( __FILE__ ) );

require_once HUPIND_PATH . 'includes/class-hupind-core.php';

register_activation_hook( __FILE__, [ 'HUPIND_Core', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'HUPIND_Core', 'deactivate' ] );

add_action( 'plugins_loaded', [ 'HUPIND_Core', 'get_instance' ], 10 );
