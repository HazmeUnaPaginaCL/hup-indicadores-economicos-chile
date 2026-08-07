<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HUPIND_Core {

    private static ?self $instance = null;

    public static array $indicators_default = [
        [ 'id' => 'uf',               'label' => 'UF',               'enabled' => true,  'order' => 0,  'decimals' => 2 ],
        [ 'id' => 'utm',              'label' => 'UTM',              'enabled' => true,  'order' => 1,  'decimals' => 0 ],
        [ 'id' => 'dolar',            'label' => 'Dólar',            'enabled' => true,  'order' => 2,  'decimals' => 2 ],
        [ 'id' => 'euro',             'label' => 'Euro',             'enabled' => true,  'order' => 3,  'decimals' => 2 ],
        [ 'id' => 'ipc',              'label' => 'IPC',              'enabled' => false, 'order' => 4,  'decimals' => 2 ],
        [ 'id' => 'ivp',              'label' => 'IVP',              'enabled' => false, 'order' => 5,  'decimals' => 2 ],
        [ 'id' => 'imacec',           'label' => 'IMACEC',           'enabled' => false, 'order' => 6,  'decimals' => 2 ],
        [ 'id' => 'tpm',              'label' => 'TPM',              'enabled' => false, 'order' => 7,  'decimals' => 2 ],
        [ 'id' => 'libra_cobre',      'label' => 'Libra de Cobre',   'enabled' => false, 'order' => 8,  'decimals' => 4 ],
        [ 'id' => 'tasa_desempleo',   'label' => 'Tasa Desempleo',   'enabled' => false, 'order' => 9,  'decimals' => 1 ],
        [ 'id' => 'bitcoin',          'label' => 'Bitcoin',          'enabled' => false, 'order' => 10, 'decimals' => 0 ],
        [ 'id' => 'dolar_intercambio','label' => 'Dólar Intercambio','enabled' => false, 'order' => 11, 'decimals' => 2 ],
        [ 'id' => 'dolar_aduanero',   'label' => 'Dólar Aduanero',   'enabled' => false, 'order' => 12, 'decimals' => 2 ],
    ];

    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_modules();
    }

    public static function activate(): void {
        if ( false === get_option( 'hupind_indicators' ) ) {
            update_option( 'hupind_indicators', self::$indicators_default, false );
        }
        if ( false === get_option( 'hupind_cache_duration' ) ) {
            update_option( 'hupind_cache_duration', 3600, false );
        }

        // La clase API no está cargada durante la activación (plugins_loaded ya pasó):
        // la incluimos para programar el cron y disparar un refresco inmediato sin
        // bloquear la activación.
        require_once HUPIND_PATH . 'includes/class-hupind-api.php';
        HUPIND_API::init_cron();
        wp_schedule_single_event( time(), HUPIND_API::CRON_HOOK );
    }

    public static function deactivate(): void {
        wp_clear_scheduled_hook( HUPIND_API::CRON_HOOK );
    }

    private function load_dependencies(): void {
        require_once HUPIND_PATH . 'includes/class-hupind-api.php';
        require_once HUPIND_PATH . 'includes/class-hupind-admin.php';
        require_once HUPIND_PATH . 'includes/class-hupind-shortcode.php';
    }

    private function init_modules(): void {
        HUPIND_API::init_cron();
        new HUPIND_Admin();
        new HUPIND_Shortcode();

        // Al actualizar el plugin, WordPress NO ejecuta el hook de activación, así que
        // los indicadores nuevos no llegarían a la opción ya guardada. Reconciliamos en
        // admin (una sola escritura, cuando falta alguno) para no perder la config previa.
        add_action( 'admin_init', [ __CLASS__, 'sync_indicators' ] );
    }

    /**
     * Reconciliación no destructiva: agrega a `hupind_indicators` cualquier indicador de
     * `$indicators_default` que aún no exista (p. ej. el Dólar Aduanero al actualizar a
     * 1.1.0), conservando el orden y los ajustes del usuario. Solo escribe si falta alguno.
     */
    public static function sync_indicators(): void {
        $saved = get_option( 'hupind_indicators' );
        if ( ! is_array( $saved ) ) {
            return; // Sin opción previa: se usarán los defaults completos.
        }

        $existing = [];
        foreach ( $saved as $ind ) {
            if ( isset( $ind['id'] ) ) {
                $existing[ $ind['id'] ] = true;
            }
        }

        $added = false;
        foreach ( self::$indicators_default as $default ) {
            if ( ! isset( $existing[ $default['id'] ] ) ) {
                $saved[] = $default;
                $added   = true;
            }
        }

        if ( $added ) {
            update_option( 'hupind_indicators', $saved, false );
            // Al actualizar el plugin no corre el hook de activación, así que agendamos un
            // refresco inmediato en segundo plano para poblar el/los indicador(es) nuevo(s)
            // sin esperar al próximo ciclo del cron (evita la latencia de hasta 1h).
            wp_schedule_single_event( time(), HUPIND_API::CRON_HOOK );
        }
    }
}
