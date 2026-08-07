<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HUPIND_Shortcode {

    private static array $months_es = [
        1  => 'ene', 2  => 'feb', 3  => 'mar', 4  => 'abr',
        5  => 'may', 6  => 'jun', 7  => 'jul', 8  => 'ago',
        9  => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic',
    ];

    private static array $months_es_full = [
        1  => 'enero',      2  => 'febrero',   3  => 'marzo',     4  => 'abril',
        5  => 'mayo',       6  => 'junio',      7  => 'julio',     8  => 'agosto',
        9  => 'septiembre', 10 => 'octubre',   11 => 'noviembre', 12 => 'diciembre',
    ];

    // Unidad monetaria fija por indicador (no configurable por el usuario)
    private static array $unit_map = [
        'uf'                => 'clp',
        'utm'               => 'clp',
        'dolar'             => 'clp',
        'euro'              => 'clp',
        'ipc'               => 'pct',
        'ivp'               => 'clp',
        'imacec'            => 'pct',
        'tpm'               => 'pct',
        'libra_cobre'       => 'usd',
        'tasa_desempleo'    => 'pct',
        'bitcoin'           => 'usd',
        'dolar_intercambio' => 'clp',
        'dolar_aduanero'    => 'clp',
    ];

    private static array $valid_date_formats = [ 'short', 'long', 'numeric', 'dashes', 'dots' ];

    public function __construct() {
        add_action( 'init', [ $this, 'register' ] );
    }

    public function register(): void {
        add_shortcode( 'hupind_indicadores', [ $this, 'render' ] );
    }

    public function render( $atts ): string {
        $atts = shortcode_atts( [
            'show'           => '',
            'layout'         => 'horizontal',
            'separator'      => '|',
            'class'          => '',
            'show_date'      => 'false',
            'show_variation' => 'false',
            'format'         => 'auto',
            'date_format'    => 'short',
            'error_text'     => 'No se pudieron cargar los indicadores desde mindicador.cl. Vuelve a intentarlo más tarde.',
        ], $atts, 'hupind_indicadores' );

        $show_ids = array_filter( array_map( 'trim', explode( ',', $atts['show'] ) ) );

        if ( ! empty( $show_ids ) ) {
            $items = HUPIND_API::get_by_ids( $show_ids );
        } else {
            $items = HUPIND_API::get_all_active();
        }

        $extra_class = $atts['class'] ? ' ' . esc_attr( $atts['class'] ) : '';

        // Arranque en frío sin datos ni fallback y con la API caída: no hay nada que mostrar.
        // Se muestra un aviso personalizable (error_text=""); vacía el aviso para no renderizar nada.
        if ( empty( $items ) ) {
            $error_text = trim( $atts['error_text'] );
            if ( '' === $error_text ) return '';
            return '<div class="hupind-indicadores hupind-indicadores--error' . $extra_class . '" role="status">'
                 . '<span class="hupind-indicadores__error">' . esc_html( $error_text ) . '</span>'
                 . '</div>';
        }

        $layout         = 'vertical' === $atts['layout'] ? 'vertical' : 'horizontal';
        $layout_mod     = 'vertical' === $layout ? 'hupind-indicadores--vertical' : 'hupind-indicadores--bar';
        $show_date      = 'true' === strtolower( $atts['show_date'] );
        $show_variation = 'true' === strtolower( $atts['show_variation'] );
        $format         = sanitize_key( $atts['format'] );
        $separator      = esc_html( $atts['separator'] );
        $date_format    = in_array( $atts['date_format'], self::$valid_date_formats, true )
                            ? $atts['date_format'] : 'short';

        $layout_style = 'vertical' === $layout
            ? 'display:flex;flex-direction:column;gap:8px;'
            : 'display:flex;flex-wrap:wrap;align-items:center;gap:8px;';

        $html = '<div class="hupind-indicadores ' . $layout_mod . $extra_class . '" style="' . $layout_style . '">';

        $last = count( $items ) - 1;
        foreach ( $items as $i => $item ) {
            $id   = esc_attr( $item['id'] );
            $unit = self::$unit_map[ $item['id'] ] ?? 'clp';
            $val  = self::format_value( (float) $item['valor'], (int) ( $item['decimals'] ?? 2 ), $format, $unit );

            $html .= '<div class="hupind-indicadores__item hupind-indicadores__item--' . $id . '">';
            // Espacio real entre el nombre y el valor (p. ej. "UF $38.123,45").
            $html .= '<span class="hupind-indicadores__label">' . esc_html( $item['label'] ) . '</span> ';
            $html .= '<span class="hupind-indicadores__value">' . esc_html( $val ) . '</span>';

            if ( $show_variation ) {
                if ( 'pct' === $unit ) {
                    // Indicadores que ya son porcentajes: diferencia absoluta en puntos porcentuales
                    $delta = (float) $item['valor'] - (float) ( $item['anterior'] ?? $item['valor'] );
                    $disp  = number_format( abs( $delta ), 2, ',', '.' ) . ' pp';
                } else {
                    // Indicadores de precio/divisa: variación porcentual relativa
                    $delta = (float) ( $item['variacion'] ?? 0 );
                    $disp  = number_format( abs( $delta ), 2, ',', '.' ) . '%';
                }
                $mod   = $delta >= 0 ? 'up' : 'down';
                $arrow = $delta >= 0 ? '▲' : '▼';
                // Color por defecto sobreescribible con --hupind-color-up / --down en el CSS del tema
                $color = $delta >= 0
                    ? 'var(--hupind-color-up,#00a32a)'
                    : 'var(--hupind-color-down,#d63638)';
                // Espacio real antes de la variación ("$38.123,45 ▲ 0,12%").
                $html .= ' <span class="hupind-indicadores__variation hupind-indicadores__variation--' . $mod . '" style="color:' . $color . '">'
                       . $arrow . ' ' . esc_html( $disp )
                       . '</span>';
            }

            $html .= '</div>';

            if ( $i < $last && 'horizontal' === $layout ) {
                $html .= '<span class="hupind-indicadores__divider" aria-hidden="true">' . $separator . '</span>';
            }
        }

        if ( $show_date ) {
            $ts       = ! empty( $items[0]['fecha'] ) ? strtotime( $items[0]['fecha'] ) : 0;
            $date_str = $ts ? self::format_date_es( $ts, $date_format ) : '';
            if ( $date_str ) {
                $html .= '<span class="hupind-indicadores__updated">Actualizado al ' . esc_html( $date_str ) . '</span>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    private static function format_value( float $val, int $decimals, string $format, string $unit ): string {
        $formatted = number_format( $val, $decimals, ',', '.' );

        if ( 'raw' === $format ) return $formatted;

        // 'clp' fuerza prefijo $ en todos (compatibilidad con shortcodes existentes)
        if ( 'clp' === $format ) return '$' . $formatted;

        // 'auto' (default): usa la unidad propia de cada indicador
        switch ( $unit ) {
            case 'pct': return $formatted . '%';
            case 'usd': return 'US$' . $formatted;
            default:    return '$' . $formatted; // clp
        }
    }

    private static function format_date_es( int $ts, string $fmt = 'short' ): string {
        $day      = (int) wp_date( 'j', $ts );
        $day0     = wp_date( 'd', $ts );
        $month    = (int) wp_date( 'n', $ts );
        $month0   = wp_date( 'm', $ts );
        $year     = wp_date( 'Y', $ts );
        $mon      = self::$months_es[ $month ] ?? '';
        $mon_full = self::$months_es_full[ $month ] ?? '';

        switch ( $fmt ) {
            case 'long':
                return sprintf( '%d de %s de %s', $day, $mon_full, $year );
            case 'numeric':
                return sprintf( '%s/%s/%s', $day0, $month0, $year );
            case 'dashes':
                return sprintf( '%s-%s-%s', $day0, $month0, $year );
            case 'dots':
                return sprintf( '%s.%s.%s', $day0, $month0, $year );
            default: // short
                return sprintf( '%02d %s %s', $day, $mon, $year );
        }
    }
}
