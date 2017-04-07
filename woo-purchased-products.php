<?php
/*
Plugin Name: Woo Purchased Products
Plugin URI:
Description: Shows list of products purchased by the logged in user in his account
Author: Mithu A Quayium
Author URI:
Version: 1.1
Text Domain: wcpp
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Required minimums and constants
 */
define( 'WCPP_VERSION', '1.0' );
define( 'WCPP_ROOT', dirname(__FILE__) );
define( 'WCPP_ASSET_PATH', plugins_url('assets',__FILE__) );

class WCPP_Init{

    /**
     * @var Singleton The reference the *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct() {

        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_styles' ) );
        add_action( 'init', array( $this, 'add_tab_endpoint') );
        add_filter( 'query_vars', array( $this, 'tabs_query_vars' ), 0 );
        add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_item' ) );
        add_action( 'woocommerce_account_cpp-purchased-products_endpoint', array( $this, 'purchased_product_item_content' ) );
        $this->includes();
    }

    public function includes() {
        require_once dirname(__FILE__).'/vote.php';
    }

    public function add_tab_endpoint() {
        add_rewrite_endpoint( 'cpp-purchased-products', EP_ROOT | EP_PAGES );
        flush_rewrite_rules();
    }
    public function tabs_query_vars( $vars ) {
        $vars[] = 'cpp-purchased-products';
        return $vars;
    }

    public function add_menu_item( $items ) {
        $items['cpp-purchased-products'] = 'Purchased Products';
        return $items;
    }

    public function purchased_product_item_content() {
        /*pri(wc_get_order_statuses());*/
        $customer_orders = new WP_Query( array(
            'numberposts' => -1,
            'meta_key'    => '_customer_user',
            'meta_value'  => get_current_user_id(),
            'post_type'   => wc_get_order_types(),
            'post_status' => 'wc-completed'//array_keys( wc_get_order_statuses() ),
        ) );

        if( $customer_orders->have_posts() ) {
            ?>
            <div class="bs-container">
                <div class="container-fluid">
                    <div class="row">
                        <?php
                        while( $customer_orders->have_posts() ) {
                            $customer_orders->the_post();

                            $order        = wc_get_order();
                            if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                                $order_id     = $order->id; // get the order ID )or may be "order->ID")
                            } else {
                                // its 3.00
                                $order_id = $order->get_id();
                            }

                            // getting all products items for each order
                            $items = $order->get_items();

                            foreach ( $items as $k => $item ) {
                                global $product;
                                $product = new WC_Product( $item['product_id'] );
                                //wc_get_template_part( 'content', 'product' );
                                ?>
                                <div class="col-sm-3">
                                    <?php if( !get_the_post_thumbnail( $item['product_id']) ) {
                                        echo '<img src="'.wc_placeholder_img_src().'" >';
                                    } else {
                                        echo get_the_post_thumbnail( $item['product_id']);
                                    };?>
                                    <a href="<?php echo get_the_permalink($item['product_id']);?>"><?php echo get_the_title($item['product_id']); ?></a>
                                </div>
                                <?php
                            }
                            //pri($items);
                        }
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </div>

<?php
        }
    }

    public function wp_enqueue_scripts_styles() {
        if( is_account_page() ) {
            wp_enqueue_style( 'wcpp-bs', WCPP_ASSET_PATH.'/css/wrapper-bs.css' );
        }
    }

}

WCPP_Init::get_instance();


