<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$hupind_rows = [
    [ '.hupind-indicadores--bar', 'Disposición horizontal' ],
    [ '.hupind-indicadores--vertical', 'Disposición vertical' ],
    [ '.hupind-indicadores__item--{id}', 'Un indicador específico, por ejemplo --uf' ],
    [ '.hupind-indicadores__label', 'Nombre del indicador' ],
    [ '.hupind-indicadores__value', 'Valor formateado' ],
    [ '.hupind-indicadores__variation--up', 'Variación positiva' ],
    [ '.hupind-indicadores__variation--down', 'Variación negativa' ],
    [ '.hupind-indicadores__divider', 'Separador horizontal' ],
    [ '.hupind-indicadores__updated', 'Fecha de actualización' ],
];
?>
<div class="hup-stack">
  <section class="hup-card" aria-labelledby="hupind-first-steps-title">
    <div class="hup-card__head">
      <h3 class="hup-card__title" id="hupind-first-steps-title">Primeros pasos</h3>
    </div>
    <div class="hup-card__body">
      <ol class="hup-steps">
        <li>
          <div class="hup-steps__title">Elige tus indicadores</div>
          <p class="hup-hint">Activa los valores que quieres publicar y pulsa «Guardar cambios» en <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'hupind-indicadores', 'tab' => 'indicators' ], admin_url( 'admin.php' ) ) ); ?>">Indicadores</a>.</p>
        </li>
        <li>
          <div class="hup-steps__title">Prepara el shortcode</div>
          <p class="hup-hint">Ajusta la disposición, el formato y los datos adicionales. El código cambia al instante.</p>
        </li>
        <li>
          <div class="hup-steps__title">Cópialo en tu página</div>
          <p class="hup-hint">Usa un bloque Shortcode de WordPress, un widget Shortcode de Elementor o un elemento Shortcode de Bricks.</p>
        </li>
      </ol>
      <details class="hup-accordion">
        <summary>¿Cómo lo uso en una plantilla PHP?</summary>
        <div><div class="hup-code"><code><?php echo esc_html( "<?php echo do_shortcode( '[hupind_indicadores]' ); ?>" ); ?></code></div></div>
      </details>
    </div>
  </section>

  <section class="hup-card" aria-labelledby="hupind-css-title">
    <div class="hup-card__head">
      <div>
        <h3 class="hup-card__title" id="hupind-css-title">Clases CSS disponibles</h3>
        <p class="hup-card__desc">Personaliza la salida del shortcode desde tu tema o maquetador.</p>
      </div>
    </div>
    <div class="hup-card__body">
      <div class="hup-rows">
        <?php foreach ( $hupind_rows as $hupind_row ) : ?>
          <div class="hup-row">
            <div class="hup-row__text">
              <div class="hup-row__title"><code><?php echo esc_html( $hupind_row[0] ); ?></code></div>
              <div class="hup-row__desc"><?php echo esc_html( $hupind_row[1] ); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="hup-hint">Puedes cambiar los colores de variación con <span class="hup-tag">--hupind-color-up</span> y <span class="hup-tag">--hupind-color-down</span>.</p>
    </div>
  </section>

  <section class="hup-card" aria-labelledby="hupind-data-title">
    <div class="hup-card__head">
      <h3 class="hup-card__title" id="hupind-data-title">Datos y actualización</h3>
    </div>
    <div class="hup-card__body">
      <details class="hup-accordion">
        <summary>¿De dónde vienen los indicadores?</summary>
        <div>El plugin consulta la API pública de <a href="https://mindicador.cl" target="_blank" rel="noopener">mindicador.cl</a> para recibir los valores. No envía datos personales ni del sitio al servicio.</div>
      </details>
      <details class="hup-accordion">
        <summary>¿Cómo se calcula el Dólar Aduanero?</summary>
        <div>Se deriva de la serie del dólar observado del Banco Central. Para el mes en curso se usa el valor del penúltimo día hábil del mes anterior, según la regla del Servicio Nacional de Aduanas. No se obtiene directamente de aduana.cl.</div>
      </details>
      <details class="hup-accordion">
        <summary>¿Qué pasa si la API no responde?</summary>
        <div>El sitio muestra la última copia válida. En una instalación nueva sin datos previos, el shortcode puede mostrar un aviso hasta que se complete la primera actualización.</div>
      </details>
    </div>
  </section>
</div>
