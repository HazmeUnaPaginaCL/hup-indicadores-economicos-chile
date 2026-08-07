<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$hupind_indicators = get_option( 'hupind_indicators', HUPIND_Core::$indicators_default );
usort( $hupind_indicators, fn( $a, $b ) => ( $a['order'] ?? 0 ) <=> ( $b['order'] ?? 0 ) );

// ¿Está activo el Dólar Aduanero? Determina el estado inicial de su nota explicativa.
$hupind_aduanero_on = false;
foreach ( $hupind_indicators as $hupind_ind ) {
    if ( 'dolar_aduanero' === $hupind_ind['id'] ) {
        $hupind_aduanero_on = ! empty( $hupind_ind['enabled'] );
        break;
    }
}
?>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
  <?php wp_nonce_field( 'hupind_save' ); ?>
  <input type="hidden" name="action" value="hupind_save">
  <input type="hidden" name="_tab" value="indicators">

  <div class="hupind-builder-layout">

    <!-- Columna izquierda: indicadores + clases BEM -->
    <div class="hupind-left-col">

      <div class="hupind-card">

        <div class="hupind-card__header">
          <h2 class="hupind-card__title">Indicadores</h2>
          <span style="font-size:12px;color:var(--hupind-sidebar-text);">Activa los que quieres mostrar</span>
        </div>

        <div class="hupind-indicators-grid">
          <?php foreach ( $hupind_indicators as $hupind_ind ) :
            $hupind_id       = $hupind_ind['id'];
            $hupind_label    = $hupind_ind['label'];
            $hupind_enabled  = ! empty( $hupind_ind['enabled'] );
            $hupind_decimals = isset( $hupind_ind['decimals'] ) ? (int) $hupind_ind['decimals'] : 2;
          ?>
          <div class="hupind-indicator-item <?php echo $hupind_enabled ? 'is-active' : ''; ?>"
               data-indicator-id="<?php echo esc_attr( $hupind_id ); ?>">

            <label class="hupind-indicator-item__toggle">
              <input type="checkbox"
                     class="hupind-indicator-check"
                     name="hupind_indicators[<?php echo esc_attr( $hupind_id ); ?>][enabled]"
                     value="1"
                     <?php checked( $hupind_enabled ); ?>>
              <span class="hupind-switch__track"></span>
            </label>

            <div class="hupind-indicator-item__info">
              <span class="hupind-indicator-item__label"><?php echo esc_html( $hupind_label ); ?></span>
              <code class="hupind-indicator-item__id"><?php echo esc_html( $hupind_id ); ?></code>
            </div>

            <div class="hupind-indicator-item__decimals">
              <label style="font-size:10px;color:var(--hupind-sidebar-text);display:block;margin-bottom:2px;">Dec.</label>
              <input type="number"
                     class="hupind-input hupind-input-dec"
                     name="hupind_indicators[<?php echo esc_attr( $hupind_id ); ?>][decimals]"
                     value="<?php echo esc_attr( $hupind_decimals ); ?>"
                     min="0"
                     max="4"
                     step="1">
            </div>

          </div>
          <?php endforeach; ?>
        </div>

        <!-- Nota explicativa del Dólar Aduanero (visible solo cuando está activo) -->
        <div id="hupind-aduanero-note"
             class="hupind-aduanero-note<?php echo $hupind_aduanero_on ? '' : ' hupind-hidden'; ?>">
          <span class="dashicons dashicons-info-outline"></span>
          <p>
            El <strong>Dólar Aduanero</strong> se calcula automáticamente a partir del dólar
            observado del Banco Central: corresponde al valor del <strong>penúltimo día hábil
            del mes anterior</strong>, la misma regla que aplica el Servicio Nacional de Aduanas
            para el mes en curso. No se obtiene directamente desde aduana.cl.
          </p>
        </div>

        <div class="hupind-form-actions">
          <button type="submit" class="hupind-btn hupind-btn-primary">
            <span class="dashicons dashicons-saved"></span>
            Guardar cambios
          </button>
        </div>

      </div><!-- .hupind-card -->

      <!-- Referencia BEM — acordeón debajo de indicadores -->
      <details class="hupind-accordion">
        <summary>Clases CSS disponibles</summary>
        <div class="hupind-accordion__body">
          <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <tbody>
              <?php
              $hupind_rows = [
                [ '.hupind-indicadores--bar',             'Layout horizontal' ],
                [ '.hupind-indicadores--vertical',        'Layout vertical' ],
                [ '.hupind-indicadores__item--{id}',      'Por indicador (--uf, --dolar…)' ],
                [ '.hupind-indicadores__label',           'Nombre del indicador' ],
                [ '.hupind-indicadores__value',           'Valor formateado' ],
                [ '.hupind-indicadores__variation--up',   'Variación positiva' ],
                [ '.hupind-indicadores__variation--down', 'Variación negativa' ],
                [ '.hupind-indicadores__divider',         'Separador (horizontal)' ],
                [ '.hupind-indicadores__updated',         'Fecha de actualización' ],
              ];
              foreach ( $hupind_rows as $hupind_row ) : ?>
              <tr style="border-bottom:1px solid var(--hupind-border);">
                <td style="padding:7px 0;">
                  <code style="font-size:11px;background:#f0f0f1;padding:2px 5px;border-radius:3px;"><?php echo esc_html( $hupind_row[0] ); ?></code>
                </td>
                <td style="padding:7px 0 7px 12px;color:var(--hupind-sidebar-text);"><?php echo esc_html( $hupind_row[1] ); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </details>

    </div><!-- .hupind-left-col -->

    <!-- Columna derecha: opciones del shortcode -->
    <div class="hupind-builder-options">

      <div class="hupind-card">
        <div class="hupind-card__header">
          <h2 class="hupind-card__title">Opciones del shortcode</h2>
        </div>
        <div class="hupind-card__body">

          <!-- Layout -->
          <div class="hupind-form-row">
            <span class="hupind-form-label">Disposición</span>
            <div class="hupind-layout-options">
              <label class="hupind-layout-option">
                <input type="radio" name="sc_layout" value="horizontal" checked>
                <span><span class="dashicons dashicons-minus"></span> Horizontal</span>
              </label>
              <label class="hupind-layout-option">
                <input type="radio" name="sc_layout" value="vertical">
                <span><span class="dashicons dashicons-menu-alt"></span> Vertical</span>
              </label>
            </div>
          </div>

          <!-- Separador -->
          <div class="hupind-form-row" id="hupind-separator-row">
            <label class="hupind-form-label" for="sc-separator">Separador</label>
            <input type="text" id="sc-separator" name="sc_separator"
                   class="hupind-input" value="|" maxlength="10">
          </div>

          <!-- Formato -->
          <div class="hupind-form-row">
            <label class="hupind-form-label" for="sc-format">Formato de número</label>
            <select id="sc-format" name="sc_format" class="hupind-select">
              <option value="auto">Automático — $, US$ o %</option>
              <option value="clp">Forzar CLP — $38.245,67</option>
              <option value="raw">Sin signo — 38245,67</option>
            </select>
          </div>

          <!-- Clase extra -->
          <div class="hupind-form-row">
            <label class="hupind-form-label" for="sc-class">Clase CSS adicional</label>
            <input type="text" id="sc-class" name="sc_class"
                   class="hupind-input" placeholder="mi-clase-extra">
          </div>

          <!-- Formato de fecha (se muestra cuando show_date está activo) -->
          <div class="hupind-form-row" id="hupind-date-format-row" style="display:none;">
            <label class="hupind-form-label" for="sc-date-format">Formato de la fecha</label>
            <select id="sc-date-format" name="sc_date_format" class="hupind-select">
              <option value="short">03 jun 2026</option>
              <option value="long">3 de junio de 2026</option>
              <option value="numeric">03/06/2026</option>
              <option value="dashes">03-06-2026</option>
              <option value="dots">03.06.2026</option>
            </select>
          </div>

          <!-- Mostrar variación y fecha — lado a lado -->
          <div class="hupind-switches-row">

            <div class="hupind-switch-box">
              <span class="hupind-form-label">Mostrar variación %</span>
              <label class="hupind-switch">
                <input type="checkbox" id="sc-show-variation" name="sc_show_variation" value="true">
                <span class="hupind-switch__track"></span>
              </label>
            </div>

            <div class="hupind-switch-box">
              <span class="hupind-form-label">Mostrar fecha</span>
              <label class="hupind-switch">
                <input type="checkbox" id="sc-show-date" name="sc_show_date" value="true">
                <span class="hupind-switch__track"></span>
              </label>
            </div>

          </div>

        </div>
      </div>

      <!-- Shortcode generado -->
      <div class="hupind-card">
        <div class="hupind-card__header">
          <h2 class="hupind-card__title">Shortcode</h2>
        </div>
        <div class="hupind-card__body">
          <div id="hupind-sc-preview" class="hupind-shortcode-preview">[hupind_indicadores]</div>
          <div style="margin-top:12px;">
            <button type="button" class="hupind-btn hupind-btn-outline" id="hupind-copy-btn">
              <span class="dashicons dashicons-clipboard"></span>
              Copiar
            </button>
          </div>
          <p style="margin:12px 0 0;font-size:11px;color:var(--hupind-sidebar-text);">
            Pégalo en cualquier widget de texto, Bricks Builder o Elementor.
          </p>
        </div>
      </div>

    </div><!-- .hupind-builder-options -->

  </div><!-- .hupind-builder-layout -->

</form>
