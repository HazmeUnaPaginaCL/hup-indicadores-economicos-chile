<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$hupind_current_duration = (int) get_option( 'hupind_cache_duration', 3600 );
$hupind_last_update      = HUPIND_API::get_last_update();
$hupind_durations        = [
    3600  => '1 hora',
    21600 => '6 horas',
    43200 => '12 horas',
    86400 => '24 horas',
];
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Solo muestra la confirmación después de un guardado validado.
$hupind_saved = isset( $_GET['saved'] ) && is_string( $_GET['saved'] ) && '1' === sanitize_key( wp_unslash( $_GET['saved'] ) );
?>
<div class="hup-stack">
  <div class="hup-grid hup-grid--2">
    <div class="hup-stat">
      <div class="hup-stat__label">Última actualización exitosa</div>
      <div class="hup-stat__value" id="hupind-last-update"><?php echo esc_html( $hupind_last_update ); ?></div>
    </div>
    <div class="hup-stat">
      <div class="hup-stat__label">Duración actual de la caché</div>
      <div class="hup-stat__value"><?php echo esc_html( $hupind_durations[ $hupind_current_duration ] ?? $hupind_current_duration . ' s' ); ?></div>
    </div>
  </div>

  <form class="hup-card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( 'hupind_save' ); ?>
    <input type="hidden" name="action" value="hupind_save">
    <input type="hidden" name="_tab" value="cache">
    <div class="hup-card__head">
      <div>
        <h3 class="hup-card__title">Duración de la caché</h3>
        <p class="hup-card__desc">Un intervalo más largo reduce las consultas a mindicador.cl.</p>
      </div>
    </div>
    <div class="hup-card__body">
      <div class="hup-field">
        <span class="hup-label">Actualizar cada
          <span class="hup-tip" tabindex="0" data-tip="Los datos se actualizan en segundo plano con WP-Cron.">
            <span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
          </span>
        </span>
        <div class="hup-control">
          <div class="hup-seg" role="radiogroup" aria-label="Duración de la caché">
            <?php foreach ( $hupind_durations as $hupind_seconds => $hupind_label ) : ?>
              <label>
                <input type="radio" name="hupind_cache_duration" value="<?php echo esc_attr( $hupind_seconds ); ?>" <?php checked( $hupind_current_duration, $hupind_seconds ); ?>>
                <span><?php echo esc_html( $hupind_label ); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
    <div class="hup-card__foot">
      <button type="submit" class="hup-btn"><span class="dashicons dashicons-yes" aria-hidden="true"></span>Guardar duración</button>
      <?php if ( $hupind_saved ) : ?>
        <span class="hup-save-status"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>Cambios guardados</span>
      <?php endif; ?>
    </div>
  </form>

  <section class="hup-card hup-card--danger" aria-labelledby="hupind-clear-cache-title">
    <div class="hup-card__head">
      <h3 class="hup-card__title" id="hupind-clear-cache-title">Limpiar caché</h3>
    </div>
    <div class="hup-card__body">
      <p class="hup-hint">Elimina los datos temporales activos. La última copia válida se conserva y WP-Cron vuelve a consultar la API.</p>
      <div>
        <button type="button" id="hupind-clear-cache-btn" class="hup-btn hup-btn--danger"
                data-hup-confirm="¿Limpiar los datos temporales de los indicadores?">
          <span class="dashicons dashicons-trash" aria-hidden="true"></span>
          <span class="hup-spinner" hidden aria-hidden="true"></span>
          <span class="hup-btn__label">Limpiar caché</span>
        </button>
      </div>
    </div>
  </section>
</div>
