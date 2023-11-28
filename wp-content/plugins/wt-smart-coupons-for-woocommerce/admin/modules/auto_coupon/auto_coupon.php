<?php
/**
 * Auto coupon admin
 *
 * @link       
 * @since 1.4.1   
 *
 * @package  Wt_Smart_Coupon  
 */
if (!defined('ABSPATH')) {
    exit;
}
class Wt_Smart_Coupon_Auto_Coupon_Admin
{
    public $module_base='auto_coupon';
    public $module_id='';
    public static $module_id_static='';
    private static $instance = null;

    public function __construct()
    {
        $this->module_id=Wt_Smart_Coupon::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;

        add_action('woocommerce_coupon_options', array($this, 'add_auto_coupon_options'), 10, 2);
        add_action('woocommerce_process_shop_coupon_meta', array($this, 'process_shop_coupon_meta'), 11, 2);
    }

    /**
     * Get Instance
     */
    public static function get_instance()
    {
        if(self::$instance==null)
        {
            self::$instance=new Wt_Smart_Coupon_Auto_Coupon_Admin();
        }
        return self::$instance;
    }

    /**
     * Add coupon meta field for setting AutoCoupon
     * @since 1.4.1
     */
    public function add_auto_coupon_options($coupon_id, $coupon)
    {

        $_wt_make_auto_coupon = get_post_meta($coupon_id, '_wt_make_auto_coupon', true);

        woocommerce_wp_checkbox(
            array(
                'id' => '_wt_make_auto_coupon',
                'label' => __('Apply coupon automatically', 'wt-smart-coupons-for-woocommerce'),
                'desc_tip' => true,
                'description' => __('Enable to apply coupon automatically without applying code. By default, it works for up to 5 recently created coupons.', 'wt-smart-coupons-for-woocommerce'),
                'wrapper_class' => 'wt_auto_coupon',
                'value' => wc_bool_to_string($_wt_make_auto_coupon),
            )
        );
    }

    /**
     * Save Auto coupon meta
     * @since 1.4.1
     */
    public function process_shop_coupon_meta($post_id, $post)
    {
        if(isset($_POST['_wt_make_auto_coupon']) && $_POST['_wt_make_auto_coupon']!='')
        {
            update_post_meta($post_id, '_wt_make_auto_coupon', true);
        } else {
            update_post_meta($post_id, '_wt_make_auto_coupon', false);
        }
    }
}
Wt_Smart_Coupon_Auto_Coupon_Admin::get_instance();