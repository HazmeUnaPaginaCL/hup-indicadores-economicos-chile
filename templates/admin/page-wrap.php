<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// HUPIND_Admin::render_page() valida la sección y define $hupind_sections.
$hupind_current_tab = $hupind_current_tab ?? 'indicators';
$hupind_sections    = $hupind_sections ?? [];
?>
<div class="wrap">
  <h1 class="screen-reader-text">HUP - Indicadores Económicos (Chile)</h1>

  <div class="hup-app" style="--hup-plugin:#2271b1">
    <header class="hup-header">
      <div class="hup-header__icon">
        <span class="dashicons dashicons-chart-line" aria-hidden="true"></span>
      </div>
      <div class="hup-header__text">
        <h2 class="hup-header__title">
          HUP - Indicadores Económicos (Chile)
          <span class="hup-badge hup-badge--version">v<?php echo esc_html( HUPIND_VERSION ); ?></span>
        </h2>
        <p class="hup-header__desc">Publica indicadores económicos de Chile con un shortcode configurable.</p>
      </div>
      <div class="hup-header__support">
        <span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
        ¿Dudas o soporte?
        <a href="mailto:contacto@hazmeunapagina.cl">contacto@hazmeunapagina.cl</a>
      </div>
    </header>

    <div class="hup-layout">
      <nav class="hup-nav" aria-label="Secciones">
        <ul class="hup-nav__list">
          <?php foreach ( $hupind_sections as $hupind_slug => $hupind_section ) : ?>
            <li>
              <a class="hup-nav__item<?php echo $hupind_slug === $hupind_current_tab ? ' is-active' : ''; ?>"
                 href="<?php echo esc_url( add_query_arg( [ 'page' => 'hupind-indicadores', 'tab' => $hupind_slug ], admin_url( 'admin.php' ) ) ); ?>"
                 <?php echo $hupind_slug === $hupind_current_tab ? 'aria-current="page"' : ''; ?>>
                <span class="dashicons <?php echo esc_attr( $hupind_section[1] ); ?>" aria-hidden="true"></span>
                <?php echo esc_html( $hupind_section[0] ); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="hup-nav__footer">
          Hecho por <a href="https://hazmeunapagina.cl" target="_blank" rel="noopener">HUP</a>
        </div>
      </nav>

      <main class="hup-main">
        <div class="hup-page-head">
          <h2><?php echo esc_html( $hupind_sections[ $hupind_current_tab ][0] ); ?></h2>
          <p><?php echo esc_html( $hupind_sections[ $hupind_current_tab ][2] ); ?></p>
        </div>

        <?php
        switch ( $hupind_current_tab ) {
            case 'cache':
                require HUPIND_PATH . 'templates/admin/tab-cache.php';
                break;
            case 'guide':
                require HUPIND_PATH . 'templates/admin/tab-guide.php';
                break;
            default:
                require HUPIND_PATH . 'templates/admin/tab-indicators.php';
        }
        ?>
      </main>
    </div>
  </div>
</div>
