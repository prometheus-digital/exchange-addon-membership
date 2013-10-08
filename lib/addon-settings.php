<?php
/**
 * Exchange will build your add-on's settings page for you and link to it from our add-on
 * screen. You are free to link from it elsewhere as well if you'd like... or to not use our API
 * at all. This file has all the functions related to registering the page, printing the form, and saving
 * the options. This includes the wizard settings. Additionally, we use the Exchange storage API to 
 * save / retreive options. Add-ons are not required to do this.
*/

/**
 * This is the function registered in the options array when it_exchange_register_addon 
 * was called for recurring payments
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
 * Default settings for recurring payments
 *
 * @since 1.0.0
 *
 * @param array $values
 * @return array
*/
function it_exchange_membership_addon_default_settings( $values ) {
    $defaults = array(
        'membership-restricted-content-message'   => __( 'This content is for members only. Become a member now to get access to this and other awesome members-only content.', 'LION' ),
        'membership-dripped-content-message'   => __( 'This content will be available in %d days.', 'LION' ),
	);
    $values = ITUtility::merge_defaults( $values, $defaults );
    return $values;
}
add_filter( 'it_storage_get_defaults_exchange_addon_membership', 'it_exchange_membership_addon_default_settings' );

/**
 * Class for Recurring Payments
 * @since 1.0.0
*/
class IT_Exchange_Membership_Add_On {

    /**
     * @var boolean $_is_admin true or false
     * @since 1.0.0
    */
    var $_is_admin;

    /**
     * @var string $_current_page Current $_GET['page'] value
     * @since 1.0.0
    */
    var $_current_page;

    /**
     * @var string $_current_add_on Current $_GET['add-on-settings'] value
     * @since 1.0.0
    */
    var $_current_add_on;

    /**
     * @var string $status_message will be displayed if not empty
     * @since 1.0.0
    */
    var $status_message;

    /**
     * @var string $error_message will be displayed if not empty
     * @since 1.0.0
    */
    var $error_message;

    /**
     * Class constructor
     *
     * Sets up the class.
     * @since 1.0.0
     * @return void
    */
    function IT_Exchange_Membership_Add_On() {
        $this->_is_admin       = is_admin();
        $this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
        $this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];

        if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'membership' == $this->_current_add_on ) {
            add_action( 'it_exchange_save_add_on_settings_membership', array( $this, 'save_settings' ) );
            do_action( 'it_exchange_save_add_on_settings_membership' );
        }
    }

    /**
     * Prints settings page
     *
     * @since 1.0.0
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
                <?php do_action( 'it_exchange_membership__settings_form_top' ); ?>
                <?php $this->get_membership_form_table( $form, $form_values ); ?>
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

    function get_membership_form_table( $form, $settings = array() ) {

		global $wp_version;

        $general_settings = it_exchange_get_option( 'settings_general' );

        if ( !empty( $settings ) )
            foreach ( $settings as $key => $var )
                $form->set_option( $key, $var );

        if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] ) : ?>
            <h3><?php _e( 'Membership', 'LION' ); ?></h3>
        <?php endif; ?>
        <div class="it-exchange-addon-settings it-exchange-membership-addon-settings">
            <p>
                <label for="membership-restricted-content-message"><?php _e( 'Restricted Content Message', 'LION' ); ?> <span class="tip" title="<?php _e( 'This message will display when a non-member attempts to access content that has been restricted.', 'LION' ); ?>">i</span></label>
                <?php
                if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
                    echo wp_editor( $settings['membership-restricted-content-message'], 'membership-restricted-content-message', array( 'textarea_name' => 'it-exchange-add-on-membership-restricted-content-message', 'textarea_rows' => 10, 'textarea_cols' => 30, 'editor_class' => 'large-text' ) );
					
					//We do this for some ITForm trickery... just to add recurring-payments-cancel-body to the used inputs field
					$form->get_text_area( 'membership-restricted-content-message', array( 'rows' => 10, 'cols' => 30, 'class' => 'large-text' ) );
                } else {
                    $form->add_text_area( 'membership-restricted-content-message', array( 'rows' => 10, 'cols' => 30, 'class' => 'large-text' ) );
				}
				?>
            </p>
            <p>
                <label for="membership-dripped-content-message"><?php _e( 'Delayed Content Message', 'LION' ); ?> <span class="tip" title="<?php _e( 'This message will appear when a member attempts to access content that has been delayed.', 'LION' ); ?>">i</span></label>
                <?php
                if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
                    echo wp_editor( $settings['membership-dripped-content-message'], 'membership-dripped-content-message', array( 'textarea_name' => 'it-exchange-add-on-membership-dripped-content-message', 'textarea_rows' => 10, 'textarea_cols' => 30, 'editor_class' => 'large-text' ) );
					
					//We do this for some ITForm trickery... just to add recurring-payments-cancel-body to the used inputs field
					$form->get_text_area( 'membership-dripped-content-message', array( 'rows' => 10, 'cols' => 30, 'class' => 'large-text' ) );
                } else {
                    $form->add_text_area( 'membership-dripped-content-message', array( 'rows' => 10, 'cols' => 30, 'class' => 'large-text' ) );
				}
				?>
            </p>
            <p class="description">
            <?php 
            _e( 'Use %d to represent the number of days until the delayed content will be available.', 'LION' ); 
            ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save settings
     *
     * @since 1.0.0
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

    /**
     * Validates for values
     *
     * Returns string of errors if anything is invalid
     *
     * @since 1.0.0
     * @return void
    */
    public function get_form_errors( $values ) {

        $errors = array();
        if ( empty( $values['membership-restricted-content-message'] ) )
            $errors[] = __( 'Please include a restricted content message.', 'LION' );
        if ( empty( $values['membership-dripped-content-message'] ) )
            $errors[] = __( 'Please include a delayed content message.', 'LION' );

        return $errors;
    }

}