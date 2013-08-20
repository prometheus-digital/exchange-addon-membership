<?php
/**
 * Exchange will build your add-on's settings page for you and link to it from our add-on
 * screen. You are free to link from it elsewhere as well if you'd like... or to not use our API
 * at all. This file has all the functions related to registering the page, printing the form, and saving
 * the options. This includes the wizard settings. Additionally, we use the Exchange storage API to 
 * save / retreive options. Add-ons are not required to do this.
*/

/**
 * This is the function registered in the options array when it_exchange_register_addon was called for membership
 *
 * It tells Exchange where to find the settings page
 *
 * @return void
*/
function it_exchange_membership_addon_settings_callback() {
    $IT_Exchange_Membership_Add_On = new IT_Exchange_Membership_Add_On();
    $IT_Exchange_Membership_Add_On->print_settings_page();
}

/**
 * Default settings for membership
 *
 * @since 0.1.0
 *
 * @param array $values
 * @return array
*/
function it_exchange_membership_addon_default_settings( $values ) {
    $defaults = array();
    $values = ITUtility::merge_defaults( $values, $defaults );
    return $values;
}
add_filter( 'it_storage_get_defaults_exchange_addon_membership', 'it_exchange_membership_addon_default_settings' );

/**
 * Class for Membership
 * @since 0.1.0
*/
class IT_Exchange_Membership_Add_On {


    /**
     * @var boolean $_is_admin true or false
     * @since 0.1.0
    */
    var $_is_admin;

    /**
     * @var string $_current_page Current $_GET['page'] value
     * @since 0.1.0
    */
    var $_current_page;

    /**
     * @var string $_current_add_on Current $_GET['add-on-settings'] value
     * @since 0.1.0
    */
    var $_current_add_on;

    /**
     * @var string $status_message will be displayed if not empty
     * @since 0.1.0
    */
    var $status_message;

    /**
     * @var string $error_message will be displayed if not empty
     * @since 0.1.0
    */
    var $error_message;

    /**
     * Class constructor
     *
     * Sets up the class.
     * @since 0.1.0
     * @return void
    */
    function IT_Exchange_Membership_Add_On() {
        $this->_is_admin       = is_admin();
        $this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
        $this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

        if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'membership-product-type' == $this->_current_add_on ) {
            add_action( 'it_exchange_save_add_on_settings_membership', array( $this, 'save_settings' ) );
            do_action( 'it_exchange_save_add_on_settings_membership' );
        }
    }

    /**
     * Prints settings page
     *
     * @since 0.4.5
     * @return void
    */
    function print_settings_page() {
        $settings = it_exchange_get_option( 'addon_membership', true );
        $form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
        $form_options = array(
            'id'      => apply_filters( 'it_exchange_add_on_membership', 'it-exchange-add-on-membership-settings' ),
            'enctype' => apply_filters( 'it_exchange_add_on_membership_settings_form_enctype', false ),
            'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=membership',
        );
        $form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-membership' ) );

        if ( ! empty ( $this->status_message ) )
            ITUtility::show_status_message( $this->status_message );
        if ( ! empty( $this->error_message ) )
            ITUtility::show_error_message( $this->error_message );

        ?>
        <div class="wrap">
            <?php screen_icon( 'it-exchange' ); ?>
            <h2><?php _e( 'Membership Settings', 'LION' ); ?></h2>

            <?php do_action( 'it_exchange_membership_settings_page_top' ); ?>
            <?php do_action( 'it_exchange_addon_settings_page_top' ); ?>
            <?php $form->start_form( $form_options, 'it-exchange-membership-settings' ); ?>
                <?php do_action( 'it_exchange_membership_settings_form_top' ); ?>
                <?php $this->get_membership_settings_form_table( $form, $form_values ); ?>
                <?php do_action( 'it_exchange_membership_settings_form_bottom' ); ?>
                <p class="submit">
                    <?php $form->add_submit( 'submit', array( 'value' => __( 'Save Changes', 'LION' ), 'class' => 'button button-primary button-large' ) ); ?>
                </p>
            <?php $form->end_form(); ?>
            <?php do_action( 'it_exchange_membership_settings_page_bottom' ); ?>
            <?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
        </div>
        <?php
    }

    /**
     * @todo verify video link
     */
    function get_membership_settings_form_table( $form, $settings = array() ) {

        $general_settings = it_exchange_get_option( 'settings_general' );

        if ( !empty( $settings ) )
            foreach ( $settings as $key => $var )
                $form->set_option( $key, $var );

		?>
        
		<h3><?php _e( 'Membership Settings', 'LION' ); ?></h3>
        <div class="it-exchange-addon-settings it-exchange-membership-addon-settings">
            <p>
                <?php _e( 'Video:', 'LION' ); ?>&nbsp;<a href="http://ithemes.com/tutorials/setting-up-membership-in-exchange/" target="_blank"><?php _e( 'Setting Up Membership in Exchange', 'LION' ); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * Save settings
     *
     * @since 0.1.0
     * @return void
    */
    function save_settings() {
        $defaults = it_exchange_get_option( 'addon_membership' );
        $new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

        // Check nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-membership-settings' ) ) {
            $this->error_message = __( 'Error. Please try again', 'LION' );
            return;
        }

        $errors = apply_filters( 'it_exchange_add_on_membership_validate_settings', $this->get_form_errors( $new_values ), $new_values );
        if ( ! $errors && it_exchange_save_option( 'addon_membership', $new_values ) ) {
            ITUtility::show_status_message( __( 'Settings saved.', 'LION' ) );
        } else if ( $errors ) {
            $errors = implode( '<br />', $errors );
            $this->error_message = $errors;
        } else {
            $this->status_message = __( 'Settings not saved.', 'LION' );
        }
    }

    function membership_save_wizard_settings() {
        if ( empty( $_REQUEST['it_exchange_settings-wizard-submitted'] ) )
            return;

        $membership_settings = array();

        // Fields to save
        $fields = array();
        $default_wizard_membership_settings = apply_filters( 'default_wizard_membership_settings', $fields );

        foreach( $default_wizard_membership_settings as $var ) {
            if ( isset( $_REQUEST['it_exchange_settings-' . $var] ) ) {
                $membership_settings[$var] = $_REQUEST['it_exchange_settings-' . $var];
            }
        }

        $settings = wp_parse_args( $membership_settings, it_exchange_get_option( 'addon_membership' ) );

        if ( $error_msg = $this->get_form_errors( $settings ) ) {

            return $error_msg;

        } else {
            it_exchange_save_option( 'addon_membership', $settings );
            $this->status_message = __( 'Settings Saved.', 'LION' );
        }

        return;
    }

    /**
     * Validates for values
     *
     * Returns string of errors if anything is invalid
     *
     * @since 0.1.0
     * @return void
    */
    public function get_form_errors( $values ) {

        $errors = array();

        return $errors;
    }
}