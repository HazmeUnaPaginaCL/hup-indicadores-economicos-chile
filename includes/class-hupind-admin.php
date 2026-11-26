<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HUPIND_Admin {

    /** Versión de hup-ui copiada en hup-ui/ (sistema de diseño HUP). */
    const UI_VERSION = '1.4.0';

    /** Color propio del plugin: solo el ícono del header. */
    const COLOR = '#15803d';

    private string $page_hook = '';

    public function __construct() {
        // Prioridad alta a propósito: deja los paneles HUP al final de Herramientas.
        add_action( 'admin_menu',            [ $this, 'register_menu' ], 9990 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_post_hupind_save',     [ $this, 'handle_save' ] );
        add_action( 'wp_ajax_hupind_clear_cache', [ $this, 'ajax_clear_cache' ] );
        add_filter( 'plugin_action_links_' . HUPIND_BASENAME, [ $this, 'add_settings_link' ] );
    }

    public function register_menu(): void {
        $this->page_hook = (string) add_management_page(
            'HUP - Indicadores Económicos (Chile)',
            'HUP - Indicadores Económicos',
            'manage_options',
            'hupind-indicadores',
            [ $this, 'render_page' ]
        );
    }

    /**
     * Añade un enlace "Configuración" en la fila del plugin (listado de plugins)
     * que lleva directamente a su pantalla de configuración.
     */
    public function add_settings_link( array $links ): array {
        $url = admin_url( 'tools.php?page=hupind-indicadores' );
        array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Configuración', 'hup-indicadores-economicos-chile' ) . '</a>' );
        return $links;
    }

    public function enqueue_assets( string $hook ): void {
        if ( $this->page_hook !== $hook ) return;

        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style(
            'hup-ui-tokens-hupind',
            HUPIND_URL . 'hup-ui/tokens.css',
            [],
            self::UI_VERSION
        );
        wp_enqueue_style(
            'hup-ui-hupind',
            HUPIND_URL . 'hup-ui/hup-admin.css',
            [ 'hup-ui-tokens-hupind' ],
            self::UI_VERSION
        );
        wp_enqueue_style(
            'hupind-admin',
            HUPIND_URL . 'assets/css/hupind-admin.css',
            [ 'hup-ui-hupind' ],
            HUPIND_VERSION
        );
        wp_enqueue_script(
            'hup-ui-hupind',
            HUPIND_URL . 'hup-ui/hup-admin.js',
            [],
            self::UI_VERSION,
            true
        );

        wp_enqueue_script(
            'hupind-admin',
            HUPIND_URL . 'assets/js/hupind-admin.js',
            [ 'hup-ui-hupind' ],
            HUPIND_VERSION,
            true
        );

        wp_localize_script( 'hupind-admin', 'hupindData', [
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonceClear' => wp_create_nonce( 'hupind_clear_cache' ),
            'i18n'       => [
                'clearing' => 'Actualizando...',
                'error'    => 'Ocurrió un error. Intenta de nuevo.',
            ],
        ] );
    }

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $hupind_sections = [
            'indicators' => [ 'Indicadores', 'dashicons-chart-line', 'Elige los valores que mostrarás y prepara tu shortcode.' ],
            'cache'      => [ 'Caché', 'dashicons-database', 'Configura cada cuánto se actualizan los datos.' ],
            'guide'      => [ 'Guía', 'dashicons-book', 'Aprende a publicar y personalizar los indicadores.' ],
            // Siempre la última, tras un separador.
            'otros'      => [ 'Otros plugins HUP', 'dashicons-admin-plugins', 'Más plugins gratuitos de HUP para tu sitio WordPress.' ],
        ];
        $allowed_tabs = array_keys( $hupind_sections );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Solo lectura del tab activo para mostrar; no procesa datos ni tiene efectos secundarios.
        $hupind_current_tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'indicators';
        if ( ! in_array( $hupind_current_tab, $allowed_tabs, true ) ) {
            $hupind_current_tab = 'indicators';
        }

        require HUPIND_PATH . 'templates/admin/page-wrap.php';
    }

    public function handle_save(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sin permiso.' );
        check_admin_referer( 'hupind_save' );

        // Duración de caché
        if ( isset( $_POST['hupind_cache_duration'] ) ) {
            $allowed  = [ 3600, 21600, 43200, 86400 ];
            $duration = absint( wp_unslash( $_POST['hupind_cache_duration'] ) );
            if ( in_array( $duration, $allowed, true ) ) {
                $previous = (int) get_option( 'hupind_cache_duration', 3600 );
                update_option( 'hupind_cache_duration', $duration, false );
                // Si cambió la duración, reprogramamos el cron para aplicar el nuevo intervalo.
                if ( $previous !== $duration ) {
                    HUPIND_API::reschedule_cron();
                }
            }
        }

        // Indicadores
        if ( isset( $_POST['hupind_indicators'] ) && is_array( $_POST['hupind_indicators'] ) ) {
            $existing = get_option( 'hupind_indicators', HUPIND_Core::$indicators_default );
            $existing_map = [];
            foreach ( $existing as $ind ) {
                $existing_map[ $ind['id'] ] = $ind;
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cada clave y valor se sanitiza individualmente dentro del bucle (sanitize_key, casts y ! empty()).
            $posted = wp_unslash( $_POST['hupind_indicators'] );
            foreach ( $posted as $raw_id => $values ) {
                $id = sanitize_key( $raw_id );
                if ( ! isset( $existing_map[ $id ] ) ) continue;
                if ( ! is_array( $values ) ) continue;
                $existing_map[ $id ]['enabled']  = ! empty( $values['enabled'] );
                $existing_map[ $id ]['decimals'] = min( 4, max( 0, (int) ( $values['decimals'] ?? 2 ) ) );
            }

            update_option( 'hupind_indicators', array_values( $existing_map ), false );
        }

        $tab = isset( $_POST['_tab'] ) ? sanitize_key( wp_unslash( $_POST['_tab'] ) ) : 'indicators';
        if ( ! in_array( $tab, [ 'indicators', 'cache' ], true ) ) $tab = 'indicators';
        wp_safe_redirect( add_query_arg( [ 'page' => 'hupind-indicadores', 'tab' => $tab, 'saved' => '1' ], admin_url( 'tools.php' ) ) );
        exit;
    }

    public function ajax_clear_cache(): void {
        check_ajax_referer( 'hupind_clear_cache' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Sin permiso.' ] );
        }

        HUPIND_API::clear_all_transients();

        // Valores nuevos en el momento: sin esto, el sitio seguía mostrando la
        // última copia hasta el próximo cron.
        $result  = HUPIND_API::refresh( 5, 2, 20 );
        $pending = $result['total'] - $result['updated'];

        if ( $pending > 0 ) {
            wp_schedule_single_event( time(), HUPIND_API::CRON_HOOK );
        }

        if ( 0 === $result['updated'] ) {
            wp_send_json_error( [ 'message' => 'mindicador.cl no responde. Se sigue mostrando la última copia válida y se reintentará en segundo plano.' ] );
        }

        wp_send_json_success( [
            'last_update' => HUPIND_API::get_last_update(),
            'message'     => $pending > 0
                ? sprintf( 'Se actualizaron %1$d de %2$d indicadores. El resto se actualizará en segundo plano.', $result['updated'], $result['total'] )
                : 'Indicadores actualizados.',
        ] );
    }
}
