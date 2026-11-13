<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$hupind_indicators = get_option( 'hupind_indicators', HUPIND_Core::$indicators_default );
usort( $hupind_indicators, fn( $a, $b ) => ( $a['order'] ?? 0 ) <=> ( $b['order'] ?? 0 ) );

$hupind_aduanero_on = false;
foreach ( $hupind_indicators as $hupind_ind ) {
    if ( 'dolar_aduanero' === $hupind_ind['id'] ) {
        $hupind_aduanero_on = ! empty( $hupind_ind['enabled'] );
        break;
    }
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Solo muestra la confirmación después de un guardado validado.
$hupind_saved = isset( $_GET['saved'] ) && is_string( $_GET['saved'] ) && '1' === sanitize_key( wp_unslash( $_GET['saved'] ) );
?>
<div class="hup-grid hup-grid--aside">
  <form class="hup-card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'hupind_save' ); ?>
    <input type="hidden" name="action" value="hupind_save">
    <input type="hidden" name="_tab" value="indicators">

    <div class="hup-card__head">
      <div>
        <h3 class="hup-card__title">Indicadores visibles</h3>
        <p class="hup-card__desc">Elige los indicadores y sus decimales para todo el sitio.</p>
      </div>
    </div>
    <div class="hup-card__body">
      <div class="hupind-indicators-grid">
        <?php foreach ( $hupind_indicators as $hupind_ind ) :
            $hupind_id       = $hupind_ind['id'];
            $hupind_label    = $hupind_ind['label'];
            $hupind_enabled  = ! empty( $hupind_ind['enabled'] );
            $hupind_decimals = isset( $hupind_ind['decimals'] ) ? (int) $hupind_ind['decimals'] : 2;
            ?>
          <div class="hup-row hupind-indicator-option" data-indicator-id="<?php echo esc_attr( $hupind_id ); ?>">
            <label class="hup-toggle">
              <input type="checkbox"
                     id="hupind-enabled-<?php echo esc_attr( $hupind_id ); ?>"
                     class="hupind-indicator-check"
                     name="hupind_indicators[<?php echo esc_attr( $hupind_id ); ?>][enabled]"
                     value="1"
                     <?php checked( $hupind_enabled ); ?>>
              <span class="hup-toggle__track" aria-hidden="true"></span>
              <span class="screen-reader-text">Mostrar <?php echo esc_html( $hupind_label ); ?></span>
            </label>
            <div class="hup-row__text">
              <div class="hup-row__title"><label for="hupind-enabled-<?php echo esc_attr( $hupind_id ); ?>"><?php echo esc_html( $hupind_label ); ?></label></div>
              <div class="hup-row__desc"><span class="hup-tag"><?php echo esc_html( $hupind_id ); ?></span></div>
            </div>
            <div class="hupind-indicator-option__decimals">
              <label for="hupind-decimals-<?php echo esc_attr( $hupind_id ); ?>">Dec.</label>
              <input id="hupind-decimals-<?php echo esc_attr( $hupind_id ); ?>"
                     type="number"
                     class="hup-input hup-input--sm"
                     name="hupind_indicators[<?php echo esc_attr( $hupind_id ); ?>][decimals]"
                     aria-label="Decimales de <?php echo esc_attr( $hupind_label ); ?>"
                     value="<?php echo esc_attr( $hupind_decimals ); ?>"
                     min="0" max="4" step="1">
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div id="hupind-aduanero-note" class="hup-notice"<?php echo $hupind_aduanero_on ? '' : ' hidden'; ?>>
        <span class="dashicons dashicons-info" aria-hidden="true"></span>
        <p>El Dólar Aduanero se calcula con el valor del penúltimo día hábil del mes anterior. <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'hupind-indicadores', 'tab' => 'guide' ], admin_url( 'admin.php' ) ) ); ?>">Ver cómo se obtiene</a>.</p>
      </div>
    </div>
    <div class="hup-card__foot">
      <button type="submit" class="hup-btn">
        <span class="dashicons dashicons-yes" aria-hidden="true"></span>Guardar cambios
      </button>
      <?php if ( $hupind_saved ) : ?>
        <span class="hup-save-status"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>Cambios guardados</span>
      <?php endif; ?>
    </div>
  </form>

  <div class="hup-stack">
    <section class="hup-card" aria-labelledby="hupind-shortcode-options-title">
      <div class="hup-card__head">
        <div>
          <h3 class="hup-card__title" id="hupind-shortcode-options-title">Opciones del shortcode</h3>
          <p class="hup-card__desc">Estos ajustes modifican el código que copiarás.</p>
        </div>
      </div>
      <div class="hup-card__body">
        <div class="hup-field hup-field--stacked">
          <span class="hup-label">Disposición</span>
          <div class="hup-control">
            <div class="hup-seg" role="radiogroup" aria-label="Disposición">
              <label><input type="radio" name="sc_layout" value="horizontal" checked><span><span class="dashicons dashicons-minus" aria-hidden="true"></span>Horizontal</span></label>
              <label><input type="radio" name="sc_layout" value="vertical"><span><span class="dashicons dashicons-menu-alt" aria-hidden="true"></span>Vertical</span></label>
            </div>
          </div>
        </div>

        <div class="hup-field hup-field--stacked" id="hupind-separator-row">
          <label class="hup-label" for="sc-separator">Separador</label>
          <div class="hup-control">
            <input type="text" id="sc-separator" class="hup-input hup-input--sm" value="|" maxlength="10">
          </div>
        </div>

        <div class="hup-field hup-field--stacked">
          <label class="hup-label" for="sc-format">Formato de número</label>
          <div class="hup-control">
            <select id="sc-format" class="hup-select">
              <option value="auto">Automático — $, US$ o %</option>
              <option value="clp">Forzar CLP — $38.245,67</option>
              <option value="raw">Sin signo — 38245,67</option>
            </select>
          </div>
        </div>

        <div class="hup-field hup-field--stacked">
          <label class="hup-label" for="sc-class">Clase CSS adicional</label>
          <div class="hup-control">
            <input type="text" id="sc-class" class="hup-input hup-input--mono" placeholder="mi-clase-extra">
          </div>
        </div>

        <div class="hup-field hup-field--stacked">
          <span class="hup-label">Datos adicionales</span>
          <div class="hup-control">
            <label class="hup-toggle">
              <input type="checkbox" id="sc-show-variation" value="true">
              <span class="hup-toggle__track" aria-hidden="true"></span>Mostrar variación
            </label>
            <label class="hup-toggle">
              <input type="checkbox" id="sc-show-date" value="true">
              <span class="hup-toggle__track" aria-hidden="true"></span>Mostrar fecha
            </label>
          </div>
        </div>

        <div class="hup-field hup-field--stacked" id="hupind-date-format-row" hidden>
          <label class="hup-label" for="sc-date-format">Formato de fecha</label>
          <div class="hup-control">
            <select id="sc-date-format" class="hup-select">
              <option value="short">03 jun 2026</option>
              <option value="long">3 de junio de 2026</option>
              <option value="numeric">03/06/2026</option>
              <option value="dashes">03-06-2026</option>
              <option value="dots">03.06.2026</option>
            </select>
          </div>
        </div>
      </div>
    </section>

    <section class="hup-card" aria-labelledby="hupind-shortcode-title">
      <div class="hup-card__head">
        <h3 class="hup-card__title" id="hupind-shortcode-title">Shortcode generado</h3>
      </div>
      <div class="hup-card__body">
        <div class="hup-code">
          <code id="hupind-sc-preview">[hupind_indicadores]</code>
          <button type="button" class="hup-btn hup-btn--sm" id="hupind-copy-btn" data-hup-copy="#hupind-sc-preview">
            <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
            <span class="hup-btn__label">Copiar</span>
          </button>
        </div>
        <div id="hupind-no-indicators" class="hup-notice hup-notice--warning" hidden>
          <span class="dashicons dashicons-warning" aria-hidden="true"></span>
          <p>Activa al menos un indicador para generar un shortcode útil.</p>
        </div>
        <p class="hup-hint">Pégalo en un bloque Shortcode de WordPress o en tu maquetador. <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'hupind-indicadores', 'tab' => 'guide' ], admin_url( 'admin.php' ) ) ); ?>">Ver la guía</a>.</p>
      </div>
    </section>
  </div>
</div>
