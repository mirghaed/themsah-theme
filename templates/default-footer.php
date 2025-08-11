<?php if (! defined('ABSPATH')) exit; ?>
<?php $primary = Themsah_Theme_Options::get_option('primary_color', '#2663ff'); ?>
<footer class="site-footer" style="background: <?php echo esc_attr($primary); ?>; text-align:center;">
  <div class="container">
    <div>
      <p><?php echo wp_kses_post( Themsah_Theme_Options::get_option('footer_text', 'تمامی حقوق برای این سایت محفوظ است.') ); ?></p>
    </div>
    <div>
      <?php /* فقط کپی‌رایت */ ?>
    </div>
  </div>
</footer>
