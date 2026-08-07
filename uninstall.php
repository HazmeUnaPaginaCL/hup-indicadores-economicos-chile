<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

delete_option( 'hupind_indicators' );
delete_option( 'hupind_cache_duration' );
delete_option( 'hupind_last_update' );

$hupind_ids = [
    'uf', 'utm', 'dolar', 'euro', 'ipc', 'ivp',
    'imacec', 'tpm', 'libra_cobre', 'tasa_desempleo',
    'bitcoin', 'dolar_intercambio', 'dolar_aduanero',
];

foreach ( $hupind_ids as $hupind_id ) {
    delete_transient( 'hupind_data_'     . $hupind_id );
    delete_transient( 'hupind_fallback_' . $hupind_id );
    delete_transient( 'hupind_lock_'     . $hupind_id );
}

// Series anuales del dólar observado que se cachean para derivar el dólar aduanero.
$hupind_year = (int) gmdate( 'Y' );
for ( $hupind_y = $hupind_year - 2; $hupind_y <= $hupind_year + 1; $hupind_y++ ) {
    delete_transient( 'hupind_dolar_serie_' . $hupind_y );
}

// Evento de WP-Cron (por si la desactivación no llegó a limpiarlo).
wp_clear_scheduled_hook( 'hupind_refresh' );
