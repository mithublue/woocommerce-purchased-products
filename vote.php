<?php

class wcpp_vote {

    public function __construct() {
        add_action( 'load-plugins.php', array( __CLASS__, 'vote_init' ) );
        add_action( 'wp_ajax_wcpp_vote',  array( __CLASS__, 'vote' ) );
    }

    public static function vote_init() {
        /*$wcpp_votes = get_option( 'wcpp_vote' );
        !is_array($wcpp_votes) ? $wcpp_votes = array() : '';*/

        //if ( in_array( $vote, array( 'yes', 'tweet' , 'facebook', 'suggest', 'no' ) ) || !$timein ) return;
        add_action( 'admin_notices', array( __CLASS__, 'message' ) );
        add_action( 'admin_head',      array( __CLASS__, 'register' ) );
        add_action( 'admin_footer',    array( __CLASS__, 'enqueue' ) );
    }

    public static function register() {
        wp_register_style( 'wcpp-vote', plugins_url('/assets/css/vote.css',__FILE__), false );
        wp_register_script( 'wcpp-vote', plugins_url('/assets/js/vote.js',__FILE__), array( 'jquery' ), false, true );
    }

    public static function enqueue() {
        wp_enqueue_style( 'wcpp-vote' );
        wp_enqueue_script( 'wcpp-vote' );
    }

    public static function message() {
        $timein = time() > ( get_option( 'wcpp_later' ) );
        if ( !$timein ) return;

        $wcpp_votes = get_option( 'wcpp_vote' );
        !is_array($wcpp_votes) ? $wcpp_votes = array() : '';

        if( isset( $wcpp_votes['no'] ) ){
            return;
        } else {

            $btn_str = '';
            if( !isset( $wcpp_votes['yes'] ) ) {
                $btn_str .= '<a href="'.admin_url( 'admin-ajax.php' ).'?action=wcpp_vote&amp;vote=yes" class="wcpp-vote-action wcpp-vote-button button button-small button-primary" data-action="https://wordpress.org/support/plugin/woo-purchased-products/reviews/?rate=5#new-post">'.__( 'Rate us', 'wcpp' ).'</a>';
            }
            if( !isset( $wcpp_votes['tweet'] ) ) {
                $btn_str .= '<a href="'.admin_url( 'admin-ajax.php' ).'?action=wcpp_vote&amp;vote=tweet" class="wcpp-vote-action wcpp-vote-button button button-small" data-action="http://twitter.com/share?url=http://bit.ly/2nK1A96;text='.urlencode( __( 'WooCommerce Purchased Products - must have WordPress plugin for WooCommerce #WooCommercePurchasedProducts', 'wcpp' ) ).'">'.__( 'Tweet', 'wcpp' ).'</a>';
            }
            if( !isset( $wcpp_votes['facebook'] ) ) {
                $btn_str .= '<a href="'.admin_url( 'admin-ajax.php' ).'?action=wcpp_vote&amp;vote=facebook" class="wcpp-vote-action wcpp-vote-button button button-small" data-action="http://facebook.com/sharer?u=http://bit.ly/2nK1A96;&amp;text='.urlencode( __( 'WooCommerce Purchased Products - must have WordPress plugin for WooCommerce #WooCommercePurchasedProducts', 'wcpp' ) ).'">'.__( 'Share on facebook', 'wcpp' ).'</a>';
            }
            if( !isset( $wcpp_votes['no'] ) ) {
                $btn_str .= '<a href="'.admin_url( 'admin-ajax.php' ).'?action=wcpp_vote&amp;vote=no" class="wcpp-vote-action wcpp-vote-button wcpp-cancel-button button button-small">'.__( 'No, thanks', 'wcpp' ).'</a>';
            }

            $btn_str .= '<a href="'.admin_url( 'admin-ajax.php' ).'?action=wcpp_vote&amp;vote=suggest" class="wcpp-vote-action wcpp-vote-button sugget-button button button-small" data-action="http://cybercraftit.com/contact/">'.__( 'Suggest us', 'wcpp' ).'</a>';

            if( !isset( $wcpp_votes['later'] ) ) {
                $btn_str .= '<a href="'.admin_url( 'admin-ajax.php' ).'?action=wcpp_vote&amp;vote=later" class="wcpp-vote-action wcpp-vote-button button button-small">'.__( 'Remind me later', 'wcpp' ).'</a>';
            }

        }



        if( !empty( $btn_str ) ) :
        ?>
        <div class="wcpp-vote">
            <div class="wcpp-vote-wrap">
                <div class="wcpp-vote-gravatar">
                    <a href="http://cybercraftit.com/" target="_blank"><img src="http://2.gravatar.com/avatar/b81a0fdd8fafcb4148aa8c5b41e56431?s=64&d=mm&r=g" alt="<?php _e( 'Mithu A Quayium', 'wcpp' ); ?>" width="50" height="50"></a>
                </div>
                <div class="wcpp-vote-message">
                    <p><?php _e( '<h3>We Need Your Support</h3>Thanks for using <strong>Woo Purchased Products</strong>.<br>If you find this plugin useful, please rate us, share and tweet to let 
others know about it, and help us improving it by your valuable suggestion .<br><b>Thank you!</b>', 'wcpp' ); ?></p>
                    <p>
                        <?php echo $btn_str; ?>
                    </p>
                </div>
                <div class="wcpp-vote-clear"></div>
            </div>
        </div>
<?php
endif;
    }

    public static function vote() {
        $vote = sanitize_key( $_GET['vote'] );

        if ( !is_user_logged_in() || !in_array( $vote, array( 'yes', 'tweet' , 'facebook', 'no', 'suggest', 'later'  ) ) ) die( 'error' );

        $wcpp_votes = get_option( 'wcpp_vote' );
        !is_array($wcpp_votes)?$wcpp_votes = array() : '';
        $wcpp_votes[$vote] = $vote;
        update_option( 'wcpp_vote', $wcpp_votes );

        if ( $vote === 'later' ) update_option( 'wcpp_later', time() + 60*60*24*3 );
        die( 'OK: ' . $vote );
    }
}

new wcpp_vote();