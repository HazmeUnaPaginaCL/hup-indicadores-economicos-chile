<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HUPIND_API {

    /** Hook y nombre del schedule de WP-Cron que refresca los datos en segundo plano. */
    const CRON_HOOK     = 'hupind_refresh';
    const CRON_SCHEDULE = 'hupind_refresh_interval';

    private static array $valid_ids = [
        'uf', 'utm', 'dolar', 'euro', 'ipc', 'ivp',
        'imacec', 'tpm', 'libra_cobre', 'tasa_desempleo',
        'bitcoin', 'dolar_intercambio', 'dolar_aduanero',
    ];

    /**
     * Lectura no bloqueante para el frontend.
     * Prioridad: caché vigente → fallback permanente → (solo en frío) un único fetch
     * protegido por lock. Nunca dispara N peticiones de 10s en una visita normal:
     * cuando existe fallback el frontend lo sirve y el cron refresca en segundo plano.
     */
    public static function get_indicator( string $id ): ?array {
        if ( ! in_array( $id, self::$valid_ids, true ) ) {
            return null;
        }

        $cached = get_transient( 'hupind_data_' . $id );
        if ( false !== $cached ) {
            return $cached;
        }

        // Sin caché vigente: servimos el último valor bueno sin bloquear.
        $fallback = self::get_fallback( $id );
        if ( null !== $fallback ) {
            return $fallback;
        }

        // Arranque en frío (sin caché ni fallback): un único intento por minuto e ID,
        // bloqueado con un lock para no saturar la API ante visitas concurrentes
        // (también actúa como caché negativo si la API está caída).
        if ( false !== get_transient( 'hupind_lock_' . $id ) ) {
            return null;
        }
        set_transient( 'hupind_lock_' . $id, 1, MINUTE_IN_SECONDS );

        return self::fetch_from_api( $id );
    }

    private static function fetch_from_api( string $id ): ?array {
        if ( ! in_array( $id, self::$valid_ids, true ) ) {
            return null;
        }

        // El dólar aduanero no es un endpoint de mindicador: se deriva de la serie
        // diaria del dólar observado (penúltimo día hábil del mes anterior).
        if ( 'dolar_aduanero' === $id ) {
            return self::fetch_dolar_aduanero();
        }

        $url      = 'https://mindicador.cl/api/' . rawurlencode( $id );
        $response = wp_remote_get( $url, [ 'timeout' => 10 ] );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return self::get_fallback( $id );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['serie'] ) || ! isset( $body['serie'][0] ) ) {
            return self::get_fallback( $id );
        }

        $actual   = (float) $body['serie'][0]['valor'];
        $anterior = isset( $body['serie'][1] ) ? (float) $body['serie'][1]['valor'] : $actual;
        $variacion = $anterior > 0 ? ( ( $actual - $anterior ) / $anterior ) * 100 : 0;

        $data = [
            'id'        => $id,
            'valor'     => $actual,
            'anterior'  => $anterior,
            'variacion' => round( $variacion, 3 ),
            'fecha'     => $body['serie'][0]['fecha'] ?? '',
        ];

        $duration = (int) get_option( 'hupind_cache_duration', 3600 );
        set_transient( 'hupind_data_'     . $id, $data, $duration );
        set_transient( 'hupind_fallback_' . $id, $data, 0 );

        // Solo escribir timestamp si el valor guardado ya tiene más de 60s de antigüedad,
        // evitando N writes a la BD cuando se refrescan varios indicadores seguidos.
        $last = (int) get_option( 'hupind_last_update', 0 );
        if ( time() - $last > 60 ) {
            update_option( 'hupind_last_update', time(), false );
        }

        return $data;
    }

    /* ── Dólar aduanero (derivado del dólar observado) ─────────────────── */

    /**
     * El dólar aduanero vigente en el mes en curso es el dólar observado del
     * penúltimo día hábil del mes anterior (regla del Servicio Nacional de Aduanas).
     * Se calcula a partir de la serie diaria del dólar observado de mindicador.cl;
     * no se descarga de aduana.cl. La variación es mes a mes (respecto al valor del
     * mes anterior, es decir el penúltimo día hábil de dos meses atrás).
     */
    private static function fetch_dolar_aduanero(): ?array {
        $year  = (int) wp_date( 'Y' );
        $month = (int) wp_date( 'n' );

        list( $y1, $m1 ) = self::prev_month( $year, $month ); // mes anterior (valor vigente)
        list( $y2, $m2 ) = self::prev_month( $y1, $m1 );      // dos meses atrás (para variación)

        // Serie(s) anual(es) necesaria(s): 1 llamada, o 2 si cruzan el borde de año.
        $series = self::fetch_dolar_serie( $y1 );
        if ( $y2 !== $y1 ) {
            $series = array_merge( self::fetch_dolar_serie( $y2 ), $series );
        }

        $actual_row = self::penultimo_habil( $series, $y1, $m1 );
        if ( null === $actual_row ) {
            // Sin datos suficientes (API caída en frío): servimos el último valor bueno.
            return self::get_fallback( 'dolar_aduanero' );
        }
        $anterior_row = self::penultimo_habil( $series, $y2, $m2 );

        $actual    = (float) $actual_row['valor'];
        $anterior  = $anterior_row ? (float) $anterior_row['valor'] : $actual;
        $variacion = $anterior > 0 ? ( ( $actual - $anterior ) / $anterior ) * 100 : 0;

        $data = [
            'id'        => 'dolar_aduanero',
            'valor'     => $actual,
            'anterior'  => $anterior,
            'variacion' => round( $variacion, 3 ),
            'fecha'     => $actual_row['fecha'] ?? '',
        ];

        $duration = (int) get_option( 'hupind_cache_duration', 3600 );
        set_transient( 'hupind_data_dolar_aduanero',     $data, $duration );
        set_transient( 'hupind_fallback_dolar_aduanero', $data, 0 );

        $last = (int) get_option( 'hupind_last_update', 0 );
        if ( time() - $last > 60 ) {
            update_option( 'hupind_last_update', time(), false );
        }

        return $data;
    }

    /** Devuelve [ año, mes ] del mes anterior al dado (maneja el salto de enero). */
    private static function prev_month( int $year, int $month ): array {
        $month--;
        if ( $month < 1 ) {
            $month = 12;
            $year--;
        }
        return [ $year, $month ];
    }

    /**
     * Descarga la serie diaria del dólar observado de un año completo desde mindicador.cl.
     * Se cachea por año (misma fuente y patrón wp_remote_get; no es un servicio nuevo).
     */
    private static function fetch_dolar_serie( int $year ): array {
        $cache_key = 'hupind_dolar_serie_' . $year;
        $cached    = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $url      = 'https://mindicador.cl/api/dolar/' . rawurlencode( (string) $year );
        $response = wp_remote_get( $url, [ 'timeout' => 10 ] );
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return [];
        }

        $body  = json_decode( wp_remote_retrieve_body( $response ), true );
        $serie = ( ! empty( $body['serie'] ) && is_array( $body['serie'] ) ) ? $body['serie'] : [];

        if ( ! empty( $serie ) ) {
            $duration = (int) get_option( 'hupind_cache_duration', 3600 );
            set_transient( $cache_key, $serie, $duration );
        }
        return $serie;
    }

    /**
     * Dado un conjunto de registros { fecha, valor }, retorna el penúltimo (2º desde el
     * final) cuya fecha cae en el año/mes indicado. La serie solo trae días hábiles, por
     * lo que "penúltimo registro del mes" = penúltimo día hábil. null si hay menos de 2.
     */
    private static function penultimo_habil( array $serie, int $year, int $month ): ?array {
        $rows = [];
        foreach ( $serie as $row ) {
            if ( empty( $row['fecha'] ) ) continue;
            $ts = strtotime( $row['fecha'] );
            if ( false === $ts ) continue;
            if ( (int) gmdate( 'Y', $ts ) === $year && (int) gmdate( 'n', $ts ) === $month ) {
                $rows[ $ts ] = $row;
            }
        }
        if ( count( $rows ) < 2 ) {
            return null;
        }
        ksort( $rows );
        $rows = array_values( $rows );
        return $rows[ count( $rows ) - 2 ];
    }

    public static function get_fallback( string $id ): ?array {
        $fallback = get_transient( 'hupind_fallback_' . $id );
        return false !== $fallback ? $fallback : null;
    }

    public static function get_all_active(): array {
        $indicators = get_option( 'hupind_indicators', HUPIND_Core::$indicators_default );
        usort( $indicators, fn( $a, $b ) => ( $a['order'] ?? 0 ) <=> ( $b['order'] ?? 0 ) );

        $result = [];
        foreach ( $indicators as $ind ) {
            if ( empty( $ind['enabled'] ) ) continue;
            $data = self::get_indicator( $ind['id'] );
            if ( null === $data ) continue;
            $data['label']    = $ind['label'];
            $data['decimals'] = $ind['decimals'] ?? 2;
            $result[] = $data;
        }
        return $result;
    }

    public static function get_by_ids( array $ids ): array {
        $config = get_option( 'hupind_indicators', HUPIND_Core::$indicators_default );
        $config_map = [];
        foreach ( $config as $ind ) {
            $config_map[ $ind['id'] ] = $ind;
        }

        $result = [];
        foreach ( $ids as $id ) {
            $id = sanitize_key( trim( $id ) );
            if ( ! in_array( $id, self::$valid_ids, true ) ) continue;
            $data = self::get_indicator( $id );
            if ( null === $data ) continue;
            $data['label']    = $config_map[ $id ]['label']    ?? strtoupper( $id );
            $data['decimals'] = $config_map[ $id ]['decimals'] ?? 2;
            $result[] = $data;
        }
        return $result;
    }

    /* ── WP-Cron: refresco en segundo plano ───────────────────────────── */

    /**
     * Registra el filtro de schedules, la acción del cron y asegura el evento programado.
     * Se llama en cada carga (plugins_loaded) para auto-reparar el schedule si faltara.
     */
    public static function init_cron(): void {
        add_filter( 'cron_schedules', [ __CLASS__, 'add_cron_schedule' ] );
        add_action( self::CRON_HOOK,  [ __CLASS__, 'refresh_all' ] );

        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time() + MINUTE_IN_SECONDS, self::CRON_SCHEDULE, self::CRON_HOOK );
        }
    }

    /**
     * Intervalo de actualización del cron = duración de caché configurada (mínimo 1h).
     */
    public static function add_cron_schedule( array $schedules ): array {
        $interval = (int) get_option( 'hupind_cache_duration', 3600 );
        if ( $interval < 3600 ) {
            $interval = 3600;
        }
        $schedules[ self::CRON_SCHEDULE ] = [
            'interval' => $interval,
            'display'  => 'HUP Indicadores: intervalo de actualización',
        ];
        return $schedules;
    }

    /**
     * Reprograma el evento para aplicar un nuevo intervalo (al cambiar la duración de caché).
     */
    public static function reschedule_cron(): void {
        wp_clear_scheduled_hook( self::CRON_HOOK );
        wp_schedule_event( time() + MINUTE_IN_SECONDS, self::CRON_SCHEDULE, self::CRON_HOOK );
    }

    /**
     * Callback del cron: refresca todos los indicadores en segundo plano para que el
     * frontend siempre lea de caché/fallback sin bloquear.
     */
    public static function refresh_all(): void {
        foreach ( self::$valid_ids as $id ) {
            self::fetch_from_api( $id );
        }
    }

    public static function clear_all_transients(): void {
        foreach ( self::$valid_ids as $id ) {
            delete_transient( 'hupind_data_' . $id );
            delete_transient( 'hupind_lock_' . $id );
        }

        // Series anuales del dólar observado usadas para derivar el dólar aduanero
        // (año en curso y anterior, que es el rango que puede consultar el cálculo).
        $year = (int) wp_date( 'Y' );
        delete_transient( 'hupind_dolar_serie_' . $year );
        delete_transient( 'hupind_dolar_serie_' . ( $year - 1 ) );
    }

    public static function get_last_update(): string {
        $ts = (int) get_option( 'hupind_last_update', 0 );
        if ( ! $ts ) return 'Sin datos aún';
        return date_i18n( 'd/m/Y H:i:s', $ts );
    }
}
