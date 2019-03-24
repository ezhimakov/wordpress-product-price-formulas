<?php

/*
Plugin Name: Woocommerce Product Price Formulas
Plugin URI:
Description: Calculate product price by formulas operating with product fields
Author: Ezhi Makov
Version: 1.0
Author URI: https://github.com/ezhimakov
*/


/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    function adding_js($hook) {
        wp_register_script(
            'wppf_script',
            plugin_dir_url(__FILE__) . '/wppf.js',
            array( 'jquery' ),
            '1.0',
            true
        );
        wp_enqueue_script('wppf_script' );
    }
    add_action('wp_enqueue_scripts', 'adding_js');

    function wppf_load_plugin_css() {
        $plugin_url = plugin_dir_url( __FILE__ );

        wp_enqueue_style( 'wppf_style', $plugin_url . 'style.css' );
    }
    add_action( 'wp_enqueue_scripts', 'wpse_load_plugin_css' );



    function wppf_register_settings() {
        add_option( 'wppf_options_group', '{}');
        register_setting( 'wppf_options_group', 'wppf_categories_ids', 'wppf_callback' );
    }
    add_action( 'admin_init', 'wppf_register_settings' );

    function wppf_register_options_page() {
        add_options_page('WPPF Settings', 'WPPF Settings', 'manage_options', 'wppf',
            'wppf_options_page');
    }
    add_action('admin_menu', 'wppf_register_options_page');

    function wppf_options_page()
    {
        $taxonomy     = 'product_cat';
        $orderby      = 'name';
        $show_count   = 0;      // 1 for yes, 0 for no
        $pad_counts   = 0;      // 1 for yes, 0 for no
        $hierarchical = 1;      // 1 for yes, 0 for no
        $title        = '';
        $empty        = 0;

        $args = array(
            'taxonomy'     => $taxonomy,
            'orderby'      => $orderby,
            'show_count'   => $show_count,
            'pad_counts'   => $pad_counts,
            'hierarchical' => $hierarchical,
            'title_li'     => $title,
            'hide_empty'   => $empty
        );
        $all_categories = get_categories( $args );

        ?>
        <div>
            <?php screen_icon(); ?>
            <h2>Woocommerce Product Price Formulas Settings</h2>
            <form method="post" action="options.php" method="post">
                <?php settings_fields( 'wppf_options_group' ); ?>
                <p>Select categories:</p>
                <?php
                $checked_cats = get_option('wppf_categories_ids');
                foreach ($all_categories as $cat) {
                    if($cat->category_parent == 0) {
                        $checked = in_array($cat->cat_ID, $checked_cats);
                        ?>
                        <p>
                            <label for="category-id-<?=$cat->cat_ID?>"><?=$cat->name?></label>
                            <input type="checkbox" name="wppf_categories_ids[]" id="category-id-<?=$cat->cat_ID?>"
                                   value="<?=$cat->cat_ID?>" <?php ($checked)?print 'checked': ''; ?> >
                        </p>
                    <?php    }
                }
                ?>
                <?php  submit_button(); ?>
            </form>
        </div>
        <?php
    }

    function get_my_prod_cat_ids($prod_id) {
        $terms = get_the_terms( $prod_id, 'product_cat' );
        if($terms) {
            foreach ($terms as $key => $term) {
                $cats_ids_array[$key] = $term->term_id;
            }
            return $cats_ids_array;
        }
    }

    function is_wppf_product($prod_id) {
        $wppf_cats = get_option('wppf_categories_ids');
        $product_cats = get_my_prod_cat_ids($prod_id);
        $wppf_cats_intersect = array_intersect($wppf_cats, $product_cats);
        if (count($wppf_cats_intersect) > 0) {
            $res = true;
        }else{
            $res = false;
        }
        return $res;
    }



    /**
     * Add a custom product data tab
     */
    add_filter( 'woocommerce_product_data_tabs', 'woo_materials_tab' );
    function woo_materials_tab( $tabs ) {
        global $post;
        if (is_wppf_product($post->ID)):
            $tabs['materials_tab'] = array(
                'label' 	=> __( 'Materials cost', 'woocommerce' ),
                'priority' 	=> 90,
                'target'=>'materials_data'
            );
        endif;
        return $tabs;
    }

    add_action('woocommerce_product_data_panels', 'wppf_materials_fields');
    function wppf_materials_fields() {
        ?>

        <div id = 'materials_data' class = 'panel woocommerce_options_panel' >
        <div class = 'options_group' >
            <?php
            $args = array(
                'id' => 'side_cost',
                'label' => __( 'Side Cost', 'cfwc' ),
                'class' => 'wppf-custom-field',
                'desc_tip' => true,
                'description'=>'FormulaID: side_cost'
            );
            woocommerce_wp_text_input( $args );

            $args = array(
                'id' => 'top_cost',
                'label' => __( 'Top Cost', 'cfwc' ),
                'class' => 'wppf-custom-field',
                'desc_tip' => true,
                'description'=>'FormulaID: top_cost'
            );
            woocommerce_wp_text_input( $args );

            $args = array(
                'id' => 'frame_sq_m',
                'label' => __( 'Frame sq.m.', 'cfwc' ),
                'class' => 'wppf-custom-field',
                'desc_tip' => true,
                'description'=>'FormulaID: frame_sq_m'
            );
            woocommerce_wp_text_input( $args );

            $args = array(
                'id' => 'min_charge',
                'label' => __( 'Min charge', 'cfwc' ),
                'class' => 'wppf-custom-field',
                'desc_tip' => true,
                'description'=>'FormulaID: min_charge'
            );
            woocommerce_wp_text_input( $args );

            $args = array(
                'id' => 'margin_field',
                'label' => __( 'Margin', 'cfwc' ),
                'class' => 'wppf-custom-field',
                'desc_tip' => true,
                'description'=>'FormulaID: margin_field'
            );
            woocommerce_wp_text_input( $args );

            $args = array(
                'id' => 'custom_length_range',
                'label' => __( 'Length Range', 'cfwc' ),
                'class' => 'wppf-custom-field',
                'desc_tip' => true,
                'description' => __( 'Enter length range. Format: min_value;max_value<br>FormulaID: custom_length_range', 'wppf' ),
            );
            woocommerce_wp_text_input( $args );

            $args = array(
                'id' => 'custom_height_range',
                'label' => __( 'Height Range', 'cfwc' ),
                'class' => 'wppf-custom-field',
                'desc_tip' => true,
                'description' => __( 'Enter height range. Format: min_value;max_value<br>FormulaID: custom_height_range', 'wppf' ),
            );
            woocommerce_wp_text_input( $args );

            $args = array(
                'id' => 'custom_width_range',
                'label' => __( 'Width Range', 'cfwc' ),
                'class' => 'wppf-custom-field',
                'desc_tip' => true,
                'description' => __( 'Enter width range. Format: min_value;max_value<br>FormulaID: custom_width_range', 'wppf' ),
            );
            woocommerce_wp_text_input( $args );
            ?>
        </div>
        </div><?php
    }

    add_filter( 'woocommerce_product_data_tabs', 'woo_new_product_tab' );
    function woo_new_product_tab( $tabs ) {
        global $post;
        if (is_wppf_product($post->ID)):
            $tabs['calculated_fields_tab'] = array(
                'label' 	=> __( 'Calculated fields', 'woocommerce' ),
                'priority' 	=> 100,
                'target'=>'calculated_fields_data'
            );
        endif;
        return $tabs;

    }

    add_action('woocommerce_product_data_panels', 'wppf_calculated_fields');
    function wppf_calculated_fields() {
    ?>
        <style>
            #calculated_fields_data .options_group{
                padding: 20px;
            }
        </style>
        <div id = 'calculated_fields_data' class = 'panel woocommerce_options_panel' >
            <div class = 'options_group' >
                <?php
                    $args = array(
                        'id' => 'sides_calculated',
                        'label' => __( 'Sides Needed Formula', 'cfwc' ),
                        'class' => 'wppf-custom-field',
                        'desc_tip' => true,
                        'placeholder'=>'e.g. ::field_FormulaID_from_tooltip::+::field_FormulaID_from_tooltip::',
                        'description' => __( 'FormulaID: sides_calculated', 'wppf' ),
                    );
                    woocommerce_wp_textarea_input( $args );

                    $args = array(
                        'id' => 'tops_calculated',
                        'label' => __( 'Tops Needed Formula', 'cfwc' ),
                        'class' => 'wppf-custom-field',
                        'desc_tip' => true,
                        'placeholder'=>'e.g. ::field_FormulaID_from_tooltip::+::field_FormulaID_from_tooltip::',
                        'description' => __( 'FormulaID: tops_calculated', 'wppf' ),
                    );
                    woocommerce_wp_textarea_input( $args );

                $args = array(
                    'id' => 'sides_cost_to_produce',
                    'label' => __( 'Sides cost to produce', 'cfwc' ),
                    'class' => 'wppf-custom-field',
                    'desc_tip' => true,
                    'placeholder'=>'e.g. ::field_FormulaID_from_tooltip::+::field_FormulaID_from_tooltip::',
                    'description' => __( 'FormulaID: sides_cost_to_produce', 'wppf' ),
                );
                woocommerce_wp_textarea_input( $args );

                $args = array(
                    'id' => 'tops_cost_to_produce',
                    'label' => __( 'Tops cost to produce', 'cfwc' ),
                    'class' => 'wppf-custom-field',
                    'desc_tip' => true,
                    'placeholder'=>'e.g. ::field_FormulaID_from_tooltip::+::field_FormulaID_from_tooltip::',
                    'description' => __( 'FormulaID: tops_cost_to_produce', 'wppf' ),
                );
                woocommerce_wp_textarea_input( $args );

                $args = array(
                    'id' => 'frame_cost_to_produce',
                    'label' => __( 'Frame cost to produce', 'cfwc' ),
                    'class' => 'wppf-custom-field',
                    'desc_tip' => true,
                    'placeholder'=>'e.g. ::field_FormulaID_from_tooltip::+::field_FormulaID_from_tooltip::',
                    'description' => __( 'FormulaID: frame_cost_to_produce', 'wppf' ),
                );
                woocommerce_wp_textarea_input( $args );

                    $args = array(
                        'id' => 'price_formula',
                        'label' => __( 'Price Formula', 'cfwc' ),
                        'placeholder'=>'e.g. ::field_FormulaID_from_tooltip::+::field_FormulaID_from_tooltip::',
                        'class' => 'wppf-custom-field',
                        'desc_tip' => false,
                    );
                    woocommerce_wp_textarea_input( $args );
                ?>
            </div>
        </div><?php
    }

    function wppf_save_custom_field( $post_id ) {
        $product = wc_get_product( $post_id );

        $side_cost = isset( $_POST['side_cost'] ) ? $_POST['side_cost'] : '';
        $product->update_meta_data( 'side_cost', sanitize_text_field( $side_cost ) );

        $top_cost = isset( $_POST['top_cost'] ) ? $_POST['top_cost'] : '';
        $product->update_meta_data( 'top_cost', sanitize_text_field( $top_cost ) );

        $frame_sq_m = isset( $_POST['frame_sq_m'] ) ? $_POST['frame_sq_m'] : '';
        $product->update_meta_data( 'frame_sq_m', sanitize_text_field( $frame_sq_m ) );

        $min_charge = isset( $_POST['min_charge'] ) ? $_POST['min_charge'] : '';
        $product->update_meta_data( 'min_charge', sanitize_text_field( $min_charge ) );

        $margin = isset( $_POST['margin_field'] ) ? $_POST['margin_field'] : '';
        $product->update_meta_data( 'margin_field', sanitize_text_field( $margin ) );

        $custom_length_range = isset( $_POST['custom_length_range'] ) ? $_POST['custom_length_range'] : '';
        $product->update_meta_data( 'custom_length_range', sanitize_text_field( $custom_length_range ) );

        $custom_height_range = isset( $_POST['custom_height_range'] ) ? $_POST['custom_height_range'] : '';
        $product->update_meta_data( 'custom_height_range', sanitize_text_field( $custom_height_range ) );

        $custom_width_range = isset( $_POST['custom_width_range'] ) ? $_POST['custom_width_range'] : '';
        $product->update_meta_data( 'custom_width_range', sanitize_text_field( $custom_width_range ) );

        $sides_needed_formula = isset( $_POST['sides_calculated'] ) ? $_POST['sides_calculated'] : '';
        $product->update_meta_data( 'sides_calculated', sanitize_text_field( $sides_needed_formula ) );

        $tops_needed_formula = isset( $_POST['tops_calculated'] ) ? $_POST['tops_calculated'] : '';
        $product->update_meta_data( 'tops_calculated', sanitize_text_field( $tops_needed_formula ) );

        $frame_cost_to_produce = isset( $_POST['frame_cost_to_produce'] ) ? $_POST['frame_cost_to_produce'] : '';
        $product->update_meta_data( 'frame_cost_to_produce', sanitize_text_field( $frame_cost_to_produce ) );

        $sides_cost_to_produce = isset( $_POST['sides_cost_to_produce'] ) ? $_POST['sides_cost_to_produce'] : '';
        $product->update_meta_data( 'sides_cost_to_produce', sanitize_text_field( $sides_cost_to_produce ) );

        $tops_cost_to_produce = isset( $_POST['tops_cost_to_produce'] ) ? $_POST['tops_cost_to_produce'] : '';
        $product->update_meta_data( 'tops_cost_to_produce', sanitize_text_field( $tops_cost_to_produce ) );

        $price_formula = isset( $_POST['price_formula'] ) ? $_POST['price_formula'] : '';
        $product->update_meta_data( 'price_formula', sanitize_text_field( $price_formula ) );

        $product->save();
    }
    add_action( 'woocommerce_process_product_meta', 'wppf_save_custom_field' );


    add_filter( 'wc_price', 'wppf_adding_front_price_fields', 10, 4 );
    function wppf_adding_front_price_fields( $return, $price, $args, $unformatted_price ) {

        if(is_product() && in_array('woocommerce_single_product_summary', $GLOBALS["wp_current_filter"] )):
            $negative          = $price < 0;
            $formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'],
                    '<span class="woocommerce-Price-currencySymbol">' .
                    get_woocommerce_currency_symbol( $args['currency'] ) .
                    '</span>', '<span class="product-price-int">'.$price.'</span>' );

            $return ='';

            $return.= '<span class="woocommerce-Price-amount amount">' . $formatted_price . '</span>';
            if ( $args['ex_tax_label'] && wc_tax_enabled() ) {
                $return .= ' <small class="woocommerce-Price-taxLabel tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
            }
        endif;
        return $return;
    }

    function wppf_dimensions_option(){
        global $post;
        if (is_wppf_product($post->ID)):
            $product_length_range = explode(';', get_post_meta($post->ID, 'custom_length_range', true));
            $product_width_range = explode(';', get_post_meta($post->ID, 'custom_width_range', true));
            $product_height_range = explode(';', get_post_meta($post->ID, 'custom_height_range', true));

            echo  '<p><input type="number" min="'.$product_length_range[0].'" max="'.$product_length_range[1].
                '" id="wppf_length" name="wppf_length" placeholder="Length" class="wppf-input" data-product-id="'.$post->ID.'" required></p>';
            echo '<p><input type="number" min="'.$product_width_range[0].'" max="'.$product_width_range[1].'" id="wppf_width" name="wppf_width" placeholder="Width" class="wppf-input" required></p>';
            echo '<p><input type="number" min="'.$product_height_range[0].'" max="'.$product_height_range[1].'" id="wppf_height" name="wppf_height" placeholder="Height" class="wppf-input" required></p>';
        endif;
    }
    add_action( 'woocommerce_before_add_to_cart_button', 'wppf_dimensions_option', 9 );



    add_action( 'wp_ajax_wppf_price', 'get_wppf_price_ajax' );
    add_action( 'wp_ajax_nopriv_wppf_price', 'get_wppf_price_ajax' );
    function get_wppf_price_ajax() {
        $price_formula = get_post_meta($_POST['id'], 'price_formula', true);
        $price = calculate_fields($price_formula, $_POST['id']);
        $p = eval('return '.$price.';');
        echo  round($p);
        wp_die();
    }

    function get_wppf_price($productID, $dimensions=array()) {
        $price_formula = get_post_meta($productID, 'price_formula', true);
        $price = calculate_fields($price_formula, $productID, $dimensions );
        $p = eval('return '.$price.';');
        return round($p);
    }

    function replace_dimensions(&$formula, $dimensions=array()){
        $re = '/::custom_(\w*)_range::/m';
        preg_match_all($re, $formula, $matches, PREG_SET_ORDER, 0);
        if(!empty($matches)){
            foreach ($matches as $match){
                if(empty($dimensions)) {
                    $formula = str_replace($match[0], (!empty($_POST[$match[1]])) ? $_POST[$match[1]] : 1, $formula);
                }else{
                    $formula = str_replace($match[0], (!empty($dimensions[$match[1]])) ? $dimensions[$match[1]] : 1, $formula);
                }
            }
        }
    }

    function calculate_fields($formula, $productID, $dimensions=array()){
        $cleaned = False;
        while ($cleaned == False):
            replace_dimensions($formula, $dimensions);
            $re = '/((?:::[a-z][a-z0-9_]*::))/m';
            preg_match_all($re, $formula, $matches, PREG_SET_ORDER, 0);
            if(!empty($matches)){
                foreach ($matches as $match){
                    $formula = str_replace($match[0],
                        '('.get_post_meta($productID, str_replace(':', '', $match[0]), true).')', $formula);
                }
                replace_dimensions($formula, $dimensions);
            }else{
                $cleaned = True;
            }
        endwhile;

        return $formula;
    }

    function wppf_add_cart_item_data( $cart_item, $product_id ){
        if (is_wppf_product($product_id)):
        $product_length_range = explode(';', get_post_meta($product_id, 'custom_length_range', true));
        $product_width_range = explode(';', get_post_meta($product_id, 'custom_width_range', true));
        $product_height_range = explode(';', get_post_meta($product_id, 'custom_height_range', true));

        $cart_item['wppf_length'] = (!empty($_POST['wppf_length']))?sanitize_text_field( $_POST['wppf_length'] ):$product_length_range[0];
        $cart_item['wppf_width'] = (!empty($_POST['wppf_width']))?sanitize_text_field( $_POST['wppf_width'] ):$product_width_range[0];
        $cart_item['wppf_height'] = (!empty($_POST['wppf_height']))?sanitize_text_field( $_POST['wppf_height'] ):$product_height_range[0];
        endif;

        return $cart_item;
    }
    add_filter( 'woocommerce_add_cart_item_data', 'wppf_add_cart_item_data', 10, 2 );



    /**
     * Add engraving text to order.
     *
     * @param WC_Order_Item_Product $item
     * @param string                $cart_item_key
     * @param array                 $values
     * @param WC_Order              $order
     */
    function wppf_add_engraving_text_to_order_items( $item, $cart_item_key, $values, $order ) {
        if ( !is_wppf_product($values['product_id']) ) {
            return;
        }

        $item->add_meta_data( __( 'Length', 'wppf' ), $values['wppf_length'] );
        $item->add_meta_data( __( 'Width', 'wppf' ), $values['wppf_width'] );
        $item->add_meta_data( __( 'Height', 'wppf' ), $values['wppf_height'] );
    }

    add_action( 'woocommerce_checkout_create_order_line_item', 'wppf_add_engraving_text_to_order_items', 10, 4 );

    add_action( 'woocommerce_before_calculate_totals', 'add_custom_price');
    function add_custom_price( $cart_obj ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return;

        foreach ( $cart_obj->get_cart() as $key => $value ) {
            if (is_wppf_product($value['product_id'])):
                $dimensions=['length'=>$value['wppf_length'], 'width'=>$value['wppf_width'], 'height'=>$value['wppf_height']];
                $price = get_wppf_price($value['product_id'], $dimensions);
                $value['data']->set_price( $price );
            endif;
        }
    }

    // Display items custom fields label and value in cart and checkout pages
    add_filter( 'woocommerce_get_item_data', 'render_meta_on_cart_and_checkout', 10, 2 );
    function render_meta_on_cart_and_checkout( $cart_data, $cart_item ){

        $custom_items = array();
        /* Woo 2.4.2 updates */
        if( !empty( $cart_data ) ) {
            $custom_items = $cart_data;
        }
        if( isset( $cart_item['wppf_length'] ) ) {
            $custom_items[] = array(
                'name' => 'Length',
                'value' => $cart_item['wppf_length'],
            );
        }
        if( isset( $cart_item['wppf_width'] ) ) {
            $custom_items[] = array(
                'name' => 'Width',
                'value' => $cart_item['wppf_width'],
            );
        }
        if( isset( $cart_item['wppf_height'] ) ) {
            $custom_items[] = array(
                'name' => 'Height',
                'value' => $cart_item['wppf_height'],
            );
        }
        return $custom_items;
    }




}





