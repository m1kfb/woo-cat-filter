<?php
/**
 * Plugin Name
 *
 * @package     WooCommerce
 * @author      Mike Brown
 * @copyright   2016 Michael Brown T/a Lancaster IT Solutions
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Woocommerce Category Filter
 * Plugin URI:  https://lancit.uk/plugin/woocommerce/woo-cat-filter
 * Description: Simple Parent category and sub-cat filter.
 * Version:     1.2.1
 * Author:      Mike Brown
 * Author URI:  https://lancit.uk
 * Text Domain: woo-cat-filter
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
register_activation_hook( __FILE__, 'child_plugin_activate' );
function child_plugin_activate(){

    // Require parent plugin
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires Woocommerce to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
    }
}

function form_creation($atts) {
    $a = shortcode_atts(['field_1_label'=>'Make', 'field_2_label'=>'Model', 'submit_text'=>'Search', 'level3'=>false, 'field_3_label'=>'wheelbase'], $atts);
    $field1_label = $a['field_1_label'];
    $field2_label = $a['field_2_label'];
    $submit_label = $a['submit_text'];
    $level3 = $a['level3'];
    $field3_label = $a['field_3_label'];
    ?>
    <form action="<?= admin_url( 'admin-post.php' ) ?>" method="post">
        <input type='hidden' name='action' value='submit_form' />
        <div class="woo-cat-filter-form">

            <div class="field1">
                <label for="field1"><?= $field1_label ?></label>
                <div id="loading1" style="display: none;">Loading...</div>
                <div id="field1_container"></div>
            </div>
            <div class="field2">
                <label for="field2"><?= $field2_label ?></label>
                <div id="loading2" style="display: none;">Loading...</div>
                <div id="field2_container">
                    <select name="field2" id="field2">
                        <option value="">Please select</option>
                    </select>
                </div>
            </div>
            <?php if($level3) { ?>
            <div class="field3">
                <label for="field3"><?= $field3_label ?></label>
                <div id="loading3" style="display: none;">Loading...</div>
                <div id="field3_container">
                    <select name="field3" id="field3">
                        <option value="">Please select</option>
                    </select>
                </div>
            </div>
            <?php } ?>
            <div class="wcf-button">
                <input type="submit" value="<?= $submit_label ?>">
            </div>
        </div>
    </form>
    <script>
        (function($){
            $.ajax({
                type: "POST",
                url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                data: { action: 'field1_callback'},
                beforeSend: function() {$("#loading1").fadeIn('slow');},
                success: function(data){
                    $("#field1_container").empty();
                    $("#loading1").fadeOut('slow');
                    $("#field1_container").append(data);
                }
            });

            $('#field1_container').change(function() {
                $.ajax({
                    type: "POST",
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    data: { action: 'field2_callback', field1: $('#field1').val() },
                    beforeSend: function() {$("#loading2").fadeIn('slow');},
                    success: function(data){
                        $("#field2_container").empty();
                        $("#loading2").fadeOut('slow');
                        $("#field2_container").append(data);
                    }
                });
            });

            $('#field2_container').change(function() {
                $.ajax({
                    type: "POST",
                    url: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
                    data: { action: 'field3_callback', field2: $('#field2').val() },
                    beforeSend: function() {$("#loading3").fadeIn('slow');},
                    success: function(data){
                        $("#field3_container").empty();
                        $("#loading3").fadeOut('slow');
                        $("#field3_container").append(data);
                    }
                });
            });
        })(jQuery);
    </script>
    <?php
}
add_shortcode( 'wcfcatsearch', 'form_creation' );
function wcf_scripts() {
    wp_localize_script( 'custom-ajax-request', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}


function field1_callback() {
    wp_dropdown_categories(
        ['name'=>'field1', 'taxonomy'=>'product_cat', 'show_option_none'=>'Please Select', 'child_of'=> 0, 'depth'=> 1, 'order'=>'ASC', 'hierarchical'=> true, 'orderby' => 'name']
    );
    die();
}

function field2_callback() {
    $id = $_POST['field1'];
    wp_dropdown_categories(
        ['name'=>'field2', 'taxonomy'=>'product_cat', 'child_of'=>$id, 'show_option_none'=>'Please Select', 'hierarchical'=>true, 'depth'=> 1, 'order'=>'ASC', 'orderby' => 'name']
    );
    die();
}

function field3_callback() {
    $id = $_POST['field2'];
    wp_dropdown_categories(
        ['name'=>'field3', 'taxonomy'=>'product_cat', 'child_of'=>$id, 'show_option_none'=>'Please Select', 'hierarchical'=>true, 'depth'=> 1, 'order'=>'ASC', 'orderby' => 'name']
    );
    die();
}

function form_submit_callback() {
    if(isset($_POST['field3']) && !empty($_POST['field3'])) {
        $field3 = $_POST['field3'];
        $term = get_term($field3);
        //print_r(get_term_link($term->slug, 'product_cat'));
        $url = get_term_link($term->slug, 'product_cat');
        wp_redirect( $url );
        exit;
    }

    if(isset($_POST['field2'])) {
        $field2 = $_POST['field2'];
        $term = get_term($field2);
        //print_r(get_term_link($term->slug, 'product_cat'));
        $url = get_term_link($term->slug, 'product_cat');
        wp_redirect( $url );
        exit;
    }
}
add_action( 'wp_enqueue_scripts', 'wcf_scripts' );
add_action( 'wp_ajax_field1_callback', 'field1_callback');
add_action('wp_ajax_nopriv_field1_callback', 'field1_callback');
add_action( 'wp_ajax_field2_callback', 'field2_callback');
add_action('wp_ajax_nopriv_field2_callback', 'field2_callback');
add_action( 'wp_ajax_field3_callback', 'field3_callback');
add_action('wp_ajax_nopriv_field3_callback', 'field3_callback');
add_action('admin_post_submit_form', 'form_submit_callback');
add_action('admin_post_nopriv_submit_form', 'form_submit_callback');

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/m1kfb/woo-cat-filter/',
    __FILE__,
    'woo-cat-filter'
);