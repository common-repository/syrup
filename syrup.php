<?php
/*
	Plugin Name: EF2F_Syrup_Plugin
	Description: Administration and execution
	Author: eface2face Spain
	Version: 1.0.0
*/
class EF2F_Syrup_Plugin {
    public function __construct() {
        // SET ADMIN ISSUES
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'syrup_plugin_settings_page' ) );
        // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'syrup_setup_sections' ) );
    	add_action( 'admin_init', array( $this, 'syrup_setup_fields' ) );

        // LOAD SYRUP
        add_action('the_content', array( $this, 'wp_loadSyrup' ) );
        add_filter('script_loader_tag', array( $this, 'add_async_attribute' ) , 10, 2);
    }

    public function wp_loadSyrup(){
        wp_register_script('syrupjs', 'https://tastesyrup.com/syrup.js', false, '3');

            wp_enqueue_script('syrupjs');
    }

    public function add_async_attribute($tag, $handle) {

        if ( $handle != 'syrupjs' ){
            return $tag;
        }

        array( $this, 'getSelectValue()' );

        $token = get_option('syrup_token_field');
        $autorun = get_option('syrup_autorun_field')[0];
        $position = get_option('syrup_position_field');
        $lefttext = get_option('syrup_lefttext_field');
        $righttext = get_option('syrup_righttext_field');
        $showicon = get_option('syrup_showicon_field')[0];
        $userinfo = get_option('syrup_userinfo_field')[0];
        $showpin = get_option('syrup_showpin_field')[0];

        return str_replace( ' src', ' async="async" data-id="syrup" data-token="'.$token.'" data-autorun="'.$autorun.'" data-button-position="'.$position.'" data-button-lefttext="'.$lefttext.'" data-button-righttext="'.$righttext.'" data-button-showicon="'.$showicon.'" data-request-userinfo="'.$userinfo.'" data-show-pin="'.$showpin.'" src', $tag );
    }

    public function syrup_plugin_settings_page() {
    	// Add the menu item and page
    	$page_title = 'Syrup Settings Page';
    	$menu_title = 'Syrup';
    	$capability = 'manage_options';
    	$slug = 'syrup_plugin';
    	$callback = array( $this, 'syrup_plugin_settings_page_content' );
    	$icon = 'dashicons-testimonial';
    	$position = 100;
    	add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
    }
    public function syrup_plugin_settings_page_content() {?>

    	<div class="wrap">
    		<h2>Syrup settings</h2><?php
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                  $this->admin_notice();
            } ?>
    		<form method="POST" action="options.php" >
                <?php
                    settings_fields( 'syrup_plugin' );
                    do_settings_sections( 'syrup_plugin' );
                    submit_button();
                ?>
    		</form>
    	</div> <?php
    }

    public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div><?php
    }
    public function syrup_setup_sections() {
        add_settings_section( 'syrup_first_section', '', false, 'syrup_plugin' );
    }

    public function syrup_setup_fields() {
        $fields = array(
        	array(
        		'uid' => 'syrup_token_field',
        		'label' => 'Token',
        		'section' => 'syrup_first_section',
        		'type' => 'text',
        		'placeholder' => 'Please, insert syrup token here'
        	),
            array(
                'uid' => 'syrup_autorun_field',
                'label' => 'Autorun',
                'section' => 'syrup_first_section',
                'type' => 'select',
                'options' => array(
                    'true' => 'True',
                    'false' => 'False',
                ),
                'default' => array()
            ),
            array(
                'uid' => 'syrup_position_field',
                'label' => 'Position',
                'section' => 'syrup_first_section',
                'type' => 'select',
                'options' => array(
                    'right-center' => 'Right-center',
                    'right-bottom' => 'Right-bottom',
                ),
                'default' => array()
            ),
            array(
                'uid' => 'syrup_lefttext_field',
                'label' => 'Left text',
                'section' => 'syrup_first_section',
                'type' => 'text',
                'placeholder' => 'your left text'
            ),
            array(
                'uid' => 'syrup_righttext_field',
                'label' => 'Right text',
                'section' => 'syrup_first_section',
                'type' => 'text',
                'placeholder' => 'your right text'
            ),
            array(
                'uid' => 'syrup_showicon_field',
                'label' => 'Show icon',
                'section' => 'syrup_first_section',
                'type' => 'select',
                'options' => array(
                    'true' => 'True',
                    'false' => 'False',
                ),
                'default' => array()
            ),
            array(
                'uid' => 'syrup_userinfo_field',
                'label' => 'User info',
                'section' => 'syrup_first_section',
                'type' => 'select',
                'options' => array(
                    'true' => 'True',
                    'false' => 'False',
                ),
                'default' => array()
            ),
            array(
                'uid' => 'syrup_showpin_field',
                'label' => 'Show pin',
                'section' => 'syrup_first_section',
                'type' => 'select',
                'options' => array(
                    'true' => 'True',
                    'false' => 'False',
                ),
                'default' => array()
            )
        );
    	foreach( $fields as $field ){
        	add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'syrup_plugin', $field['section'], $field );
            register_setting( 'syrup_plugin', $field['uid'] );
    	}
    }



    public function field_callback( $arguments ) {
        $value = get_option( $arguments['uid'] );
        if( ! $value ) {
            $value = $arguments['default'];
        }
        switch( $arguments['type'] ){
            case 'text':
            case 'password':
            case 'number':
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
                break;
            case 'select':
            case 'multiselect':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $attributes = '';
                    $options_markup = '';
                    foreach( $arguments['options'] as $key => $label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
                    }
                    if( $arguments['type'] === 'multiselect' ){
                        $attributes = ' multiple="multiple" ';
                    }
                    printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
                }
                break;
            case 'radio':
            case 'checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        $options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value[ array_search( $key, $value, true ) ], $key, false ), $label, $iterator );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
        }
    }
}
new EF2F_Syrup_Plugin();