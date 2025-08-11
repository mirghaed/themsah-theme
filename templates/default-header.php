<?php if (! defined('ABSPATH')) exit; ?>
<?php if ( Themsah_Theme_Template_Loader::is_elementor_preview_context() ) return; ?>
<?php
$primary = Themsah_Theme_Options::get_option('primary_color', '#2663ff');
?>
<header class="site-header" style="background:#fff;">
  <div class="container">
    <div class="logo">
      <?php
      $header_logo = Themsah_Theme_Options::get_option('header_logo_image', '');
      if ( ! empty($header_logo) ) {
          printf('<a href="%1$s"><img src="%2$s" alt="%3$s" /></a>', esc_url(home_url('/')), esc_url($header_logo), esc_attr(get_bloginfo('name')));
      } elseif ( function_exists('the_custom_logo') && has_custom_logo() ) {
          the_custom_logo();
      } else {
          printf('<a href="%1$s" style="font-weight:700;color:%2$s;">%3$s</a>', esc_url(home_url('/')), esc_attr($primary), esc_html(get_bloginfo('name')));
      }
      ?>
    </div>
    <nav class="main-nav" aria-label="<?php esc_attr_e('منوی اصلی', 'themsah-theme'); ?>">
      <?php
      $menu_id = Themsah_Theme_Options::get_option('header_menu', '');
      if ( $menu_id ) {
          wp_nav_menu( array( 'menu' => intval($menu_id), 'container' => false ) );
      } else {
          wp_nav_menu( array( 'theme_location' => 'main-menu', 'container' => false ) );
      }
      ?>
    </nav>
    <div class="header-btn">
      <?php
      $btn_text = Themsah_Theme_Options::get_option('header_button_text', 'تماس با ما');
      $btn_link = Themsah_Theme_Options::get_option('header_button_link', '#');
      printf('<a class="mytheme-btn" href="%s">%s</a>', esc_url($btn_link), esc_html($btn_text));
      ?>
    </div>
  </div>
</header>
