<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$hupind_current_duration = (int) get_option( 'hupind_cache_duration', 3600 );
$hupind_last_update      = HUPIND_API::get_last_update();

$hupind_durations = [
  3600  => '1 hora',
  21600 => '6 horas',
  43200 => '12 horas',
  86400 => '24 horas',
];
?>

<!-- Stats -->
<div class="hupind-stats">

  <div class="hupind-stat">
    <div class="hupind-stat__label">Último fetch exitoso</div>
    <div class="hupind-stat__value" id="hupind-last-update">
      <?php echo esc_html( $hupind_last_update ); ?>
    </div>
  </div>

  <div class="hupind-stat">
    <div class="hupind-stat__label">Duración de caché actual</div>
    <div class="hupind-stat__value">
      <?php echo esc_html( $hupind_durations[ $hupind_current_duration ] ?? $hupind_current_duration . ' s' ); ?>
    </div>
  </div>

</div>

<!-- Configuración de caché -->
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
  <?php wp_nonce_field( 'hupind_save' ); ?>
  <input type="hidden" name="action" value="hupind_save">
  <input type="hidden" name="_tab" value="cache">

  <div class="hupind-card">

    <div class="hupind-card__header">
      <h2 class="hupind-card__title">Duración del caché</h2>
    </div>

    <div class="hupind-card__body">

      <p style="font-size:13px;color:var(--hupind-sidebar-text);margin-top:0;">
        Los valores se almacenan en WordPress Transients. Un caché más largo reduce
        las peticiones a mindicador.cl; uno más corto mantiene los datos más actualizados.
      </p>

      <div class="hupind-duration-options">
        <?php foreach ( $hupind_durations as $hupind_seconds => $hupind_label ) : ?>
        <div class="hupind-duration-option">
          <input type="radio"
                 id="hupind-duration-<?php echo esc_attr( $hupind_seconds ); ?>"
                 name="hupind_cache_duration"
                 value="<?php echo esc_attr( $hupind_seconds ); ?>"
                 <?php checked( $hupind_current_duration, $hupind_seconds ); ?>>
          <label for="hupind-duration-<?php echo esc_attr( $hupind_seconds ); ?>">
            <?php echo esc_html( $hupind_label ); ?>
          </label>
        </div>
        <?php endforeach; ?>
      </div>

    </div>

    <div class="hupind-form-actions">
      <button type="submit" class="hupind-btn hupind-btn-primary">
        <span class="dashicons dashicons-saved"></span>
        Guardar duración
      </button>
    </div>

  </div>

</form>

<!-- Limpiar caché -->
<div class="hupind-card">

  <div class="hupind-card__header">
    <h2 class="hupind-card__title">Limpiar caché ahora</h2>
  </div>

  <div class="hupind-card__body">

    <p style="font-size:13px;color:var(--hupind-sidebar-text);margin-top:0;">
      Elimina los transients de caché activos. Los valores de fallback se conservan
      para evitar errores en producción si la API no responde.
      La próxima carga del shortcode realizará un nuevo fetch a mindicador.cl.
    </p>

    <button type="button"
            id="hupind-clear-cache-btn"
            class="hupind-btn hupind-btn-danger"
            data-label="Limpiar caché">
      <span class="dashicons dashicons-trash"></span>
      Limpiar caché
    </button>

  </div>

</div>
