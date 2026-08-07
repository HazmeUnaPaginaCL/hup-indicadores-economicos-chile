<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// $hupind_current_tab lo define HUPIND_Admin::render_page() antes del require; default defensivo por si se incluyera suelto.
$hupind_current_tab = $hupind_current_tab ?? 'indicators';
?>

<div class="hupind-wrap">

  <!-- Header limpio: icono + título + descripción + contacto -->
  <div class="hupind-header">
    <div class="hupind-header__brand">
      <div class="hupind-header__icon">
        <span class="dashicons dashicons-chart-line"></span>
      </div>
      <div>
        <h1 class="hupind-header__title">
          HUP - Indicadores Económicos (Chile)
          <span class="hupind-header__version">v<?php echo esc_html( HUPIND_VERSION ); ?></span>
        </h1>
        <p class="hupind-header__desc">Indicadores económicos en tiempo real desde mindicador.cl</p>
      </div>
    </div>
    <div class="hupind-header__contact">
      <span class="dashicons dashicons-email-alt"></span>
      <span>¿Dudas o soporte? <a href="mailto:contacto@hazmeunapagina.cl">contacto@hazmeunapagina.cl</a></span>
    </div>
  </div>

  <!-- Layout -->
  <div class="hupind-layout">

    <!-- Sidebar -->
    <nav class="hupind-sidebar">

      <button class="hupind-tab-btn <?php echo 'indicators' === $hupind_current_tab ? 'is-active' : ''; ?>"
              data-tab="indicators"
              type="button">
        <span class="hupind-tab-icon hupind-tab-icon--blue">
          <span class="dashicons dashicons-list-view"></span>
        </span>
        Indicadores
      </button>

      <button class="hupind-tab-btn <?php echo 'cache' === $hupind_current_tab ? 'is-active' : ''; ?>"
              data-tab="cache"
              type="button">
        <span class="hupind-tab-icon hupind-tab-icon--orange">
          <span class="dashicons dashicons-database"></span>
        </span>
        Caché
      </button>

      <div class="hupind-sidebar-footer">
        Hecho con ❤️ por HUP
      </div>

    </nav>

    <!-- Content -->
    <div class="hupind-content">

      <div id="hupind-tab-indicators"
           class="hupind-tab-panel <?php echo 'indicators' !== $hupind_current_tab ? 'hupind-hidden' : ''; ?>">
        <?php require HUPIND_PATH . 'templates/admin/tab-indicators.php'; ?>
      </div>

      <div id="hupind-tab-cache"
           class="hupind-tab-panel <?php echo 'cache' !== $hupind_current_tab ? 'hupind-hidden' : ''; ?>">
        <?php require HUPIND_PATH . 'templates/admin/tab-cache.php'; ?>
      </div>

    </div><!-- .hupind-content -->

  </div><!-- .hupind-layout -->

</div><!-- .hupind-wrap -->

<div id="hupind-toast" class="hupind-toast" role="status" aria-live="polite"></div>
