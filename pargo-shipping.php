<?php
/**
 * Plugin Name: Pargo Shipping
 * Plugin URI: http://www.pargo.co.za
 * Description: Pargo is a convenient logistics solution that lets you collect and return parcels at Pargo parcel points throughout the country when it suits you best.
 * Version: 2.0.3
 * Author: Pargo
 * Author URI: http://www.pargo.co.za
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * GitHub Plugin URI: https://github.com/hachondapargo/pargo-wordpress
 */

define("PARGOPLUGINVERSION", "2.0.3");

//Prevent direct access to plugin 
if ( ! defined( 'WPINC' ) ) {
    die;
}

if (!session_id()) {
    session_start();
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {


/* Version 1.2.1 Admin Menu
    
/** Step 1 (admin menu). */
add_action( 'admin_menu', 'pargo_plugin_menu' );
/** Step 2 */
function pargo_plugin_menu() {
  add_menu_page(
   "Pargo Plugin Options",
  "Pargo Shipping",
  "manage_options",
  "pargo-shipping",
  "pargo_plugin_options",
  "dashicons-cart"
  );

add_submenu_page(
            "pargo-shipping", //Required. Slug of top level menu item
            "Pargo - Getting Started", //Required. Text to be displayed in title.
            "Getting Started", //Required. Text to be displayed in menu.
            "manage_options", //Required. The required capability of users.
            "pargo-shipping", //Required. A unique identifier to the sub menu item.
            "pargo_plugin_options", //Optional. This callback outputs the content of the page associated with this menu item.
            "" //Optional. The URL of the menu item icon
        );

  add_submenu_page(
            "pargo-shipping", //Required. Slug of top level menu item
            "Pargo - Usage Tracking Opt-in Options", //Required. Text to be displayed in title.
            null, //Required. Text to be displayed in menu.
            "manage_options", //Required. The required capability of users.
            "pargo-shipping-optin", //Required. A unique identifier to the sub menu item.
            "pargo_shipping_optin_page", //Optional. This callback outputs the content of the page associated with this menu item.
            "" //Optional. The URL of the menu item icon
        );
}
/** Step 3. */
function pargo_plugin_options() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }

include 'pargo-admin.php';

}// end pargo_plugin_options

//the optin page

function pargo_shipping_optin_page() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }

return include('pargo-admin-tracking-optin.php');

} //end function pargo_shipping_optin_page()

/** Step 3 (admin menu). */
add_action( 'admin_menu', 'pargo_plugin_menu' );


//call function on form submit

if (isset($_POST['pargoconfirmoptintracking'])) {

if(isset($_POST['consent']) && $_POST['consent'] == 'no'){
  global $wpdb;
       $request  = json_encode(['consent' => $_POST['consent']]);
        $table = $wpdb->prefix.'postmeta';

        $data = $wpdb->get_row("SELECT * FROM  $table WHERE  meta_key = 'pargo_settings'");

        if (count((array)$data->meta_key) == 0) {
            $wpdb->insert($table, array("meta_key"=> 'pargo_settings',"meta_value" => $request));
        } else {
            //$wpdb->update($table, array("meta_key"=> 'pargo_settings',"meta_value" => $request));
            $wpdb->query($wpdb->prepare("UPDATE $table SET meta_value=%s WHERE meta_key='pargo_settings'",$request));
        }
     header('Location: admin.php?page=pargo-shipping');
    }
    
    else{

  global $wpdb;
       $request  = json_encode(['consent' => $_POST['consent']]);
       $table = $wpdb->prefix.'postmeta';

       $data = $wpdb->get_row("SELECT * FROM  $table WHERE  meta_key = 'pargo_settings'");
     
       if (count((array)$data->meta_key) == 0) {
           $wpdb->insert($table, array("meta_key"=> 'pargo_settings',"meta_value" => $request));
       } else {
        $wpdb->query($wpdb->prepare("UPDATE $table SET meta_value= %s WHERE meta_key='pargo_settings'",$request));
        // $wpdb->update($table, array("meta_key"=> 'pargo_settings',"meta_value" => $request));
       }
    
     header('Location: admin.php?page=pargo-shipping');
}

}

/** Int Code **/

    register_activation_hook(__FILE__, 'pargo_plugin_activate');
    add_action('admin_init', 'pargo_plugin_redirect');


    function pargo_plugin_activate()
    {
        global $wpdb;

        $redirect = null;
        $table = $wpdb->prefix . 'postmeta';
        //decode data if we want the data
        $data = $wpdb->get_row("SELECT * FROM  $table WHERE  meta_key = 'pargo_settings'");
        if (count($data) == 0) {
            $redirect = 'admin.php?page=pargo-shipping-optin';
        } else {
            $redirect = 'admin.php?page=pargo-shipping';
        }
        add_option('redirect', $redirect);
    }


    function pargo_plugin_redirect() {

        if(get_option('redirect')){
            wp_redirect(get_option('redirect'));
        }

        delete_option('redirect');

    }

    //Save Tracking data


/*
    public function savePargoOptinData($data){
        global $wpdb;
        $request  = json_encode($data);
        $table = $wpdb->prefix.'postmeta';
        $wpdb->insert($table, array("meta_value" => $request));
    }

*/  


/** end Int code **/

//Upgrade

function wp_upe_upgrade_completed( $upgrader_object, $options ) {
    // The path to our plugin's main file
    $our_plugin = plugin_basename( __FILE__ );
    // If an update has taken place and the updated type is plugins and the plugins element exists
    if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
     // Iterate through the plugins being updated and check if ours is there
     foreach( $options['plugins'] as $plugin ) {
      if( $plugin == $our_plugin ) {
       // Set a transient to record that our plugin has just been updated
       //set_transient( 'wp_upe_updated', 1 );

       global $wpdb;
       $redirect = null;
       $table = $wpdb->prefix . 'postmeta';
       //decode data if we want the data
       $data = $wpdb->get_row("SELECT * FROM  $table WHERE  meta_key = 'pargo_settings'");
       if (count($data) == 0) {
           $redirect = 'admin.php?page=pargo-shipping-optin';
       } else {
           $redirect = 'admin.php?page=pargo-shipping';
       }
       add_option('redirect', $redirect);


      } //end if pargo updated
     }
    }
   }
   add_action( 'upgrader_process_complete', 'wp_upe_upgrade_completed', 10, 2 );

//Deactivate

function pargo_plugin_deactivated(){
    //put de-activation code here.
    
    //Authenticate 
    $handle = curl_init('http://pargodw.pargosandbox.co.za/oauth/token');
    $data = array(
        'grant_type' => 'password',
        'client_id' => '2',
        'client_secret' => 'Jo7Bvlui3E2nIB0BHluNUU8zcoEXKMlOS5d7x4nx',
        'scope' => '*',
        'username' => 'pargodwuser@pargo.co.za',
        'password' => '4CLF39Se3X6shtJB'
    );

    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
    $rawResult = curl_exec($handle);
    $cleanResult = json_decode($rawResult);
    $access_Token = $cleanResult->access_token;
    $site_url = get_bloginfo('url');
    $data = [];
    $data = [
        'site_url' => $site_url,
        'platform_name' => 'WordPress'
    ];
    $data=json_encode($data);
    $url = 'http://pargodw.pargosandbox.co.za/api/on-deactivate';
	$headers = array(
		"Content-Type:application/json",
		"Accept:application/json",
		"Authorization: Bearer " . $access_Token
	);
	$client = curl_init($url);
	curl_setopt($client, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($client, CURLOPT_POSTFIELDS, $data);
	curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
	$rawResultOrder = curl_exec($client);
    $cleanResultOrder = json_decode($rawResultOrder);
    
    //remove meta key

    global $wpdb;

    $table = $wpdb->prefix.'postmeta';
    $data = $wpdb->get_row("SELECT * FROM  $table WHERE  meta_key = 'pargo_settings'");

    if (count((array)$data->meta_key) > 0) {
            $wpdb->delete( $table, array('meta_key' => 'pargo_settings'));
    }

    }//end pargo_plugin_deactivate function

    register_deactivation_hook( __FILE__, 'pargo_plugin_deactivated' );

//End deactivate 

/**custom optin page js css **/

    add_action('admin_enqueue_scripts', 'pargo_optin_reg_css_and_js');

    function pargo_optin_reg_css_and_js($hook)
    {

    $current_screen = get_current_screen();

    if ( strpos($current_screen->base, 'pargo-shipping-optin') === false) {
        return;
    } else {

        wp_enqueue_style('pargo_tracking_optin_css', plugins_url('inc/pargo-tracking-optin.css',__FILE__ ));
        wp_enqueue_script('pargo_tracking_optin_js', plugins_url('inc/pargo-tracking-optin.js',__FILE__ ));

        }
    }

/**end custom optin page css **/    

/**Add CSS and Javascript **/

  add_action( 'wp_enqueue_scripts', 'pargo_enqueued_assets' );

  function pargo_enqueued_assets(){
        wp_enqueue_script( 'jquery' );
        //wp_enqueue_script( 'jquery', array(), '2.0', true );
        wp_enqueue_script( 'pargo_main_script', plugins_url( 'js/main.js', __FILE__ ));
        wp_enqueue_style( 'pargo-main-style', plugins_url( 'css/main.css', __FILE__ ));
        wp_localize_script( 'pargo_main_script', 'ajax_object', array( 'ajaxurl' => plugins_url( 'ajax-pick-up-point.php', __FILE__ )) ) ;
    }
    add_filter( 'woocommerce_checkout_get_value', 'set_shipping_zip', 10, 2 );

function set_shipping_zip() {
    global $woocommerce;
    $state = null;
if (isset($_SESSION['pargo_shipping_address']['province'] )) {
     switch ($_SESSION['pargo_shipping_address']['province']) {
      case 'Western Provice':
        $state = 'WP';
        break;
         case 'Northern Cape':
        $state = 'NC';
        break;
            case 'Eastern Cape':
        $state = 'EC';
        break;
              case 'Gauteng':
        $state = 'GP';
        break;
                    case 'North West':
        $state = 'NW';
        break;
                      case 'Mpumalanga':
        $state = 'MP';
        break;
                      case 'Free State':
        $state = 'FS';
        break;
                        case 'Limpopo':
        $state = 'LP';
        break;
                          case 'KwaZulu-Natal':
        $state = 'KZN';
        break;
      
      default:
        $state = null;
        break;
    }
    }


   
    //set it
    if(isset($_SESSION['pargo_shipping_address']['address1'])){
    $woocommerce->customer->set_shipping_address( $_SESSION['pargo_shipping_address']['address1'] );
    }
    if(isset($_SESSION['pargo_shipping_address']['address2'])){
    $woocommerce->customer->set_shipping_address_2( $_SESSION['pargo_shipping_address']['address2'] );
    }
    if(isset($_SESSION['pargo_shipping_address']['city'])){
      $woocommerce->customer->set_shipping_city( $_SESSION['pargo_shipping_address']['city'] );
    }
    if(isset($_SESSION['pargo_shipping_address']['province'])){
      $woocommerce->customer->set_shipping_state( $_SESSION['pargo_shipping_address']['province'] );
    }
    if(!is_null($state)){
      $woocommerce->customer->set_shipping_state( $state );
    }

    if(isset($_SESSION['pargo_shipping_address']['storeName'])){
      $woocommerce->customer->set_shipping_company( $_SESSION['pargo_shipping_address']['storeName'] .' ('.$_SESSION['pargo_shipping_address']['pargoPointCode'].')' );
    }
    if(isset($_SESSION['pargo_shipping_address']['postalcode'])){
    $woocommerce->customer->set_shipping_postcode( $_SESSION['pargo_shipping_address']['postalcode'] );
    }
    //if(isset($_SESSION['shipping_address']['province'])){
    //$_SESSION['pargoPointCode']
    //}

  }
    
    add_action( 'woocommerce_before_checkout_form', 'set_shipping_zip'  );

    function overrideShippingLogic( $fields ) {
     $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

             $chosen_shipping = $chosen_methods[0];

        if($chosen_shipping == 'wp_pargo'){
            $fields['shipping_state']['required'] = false;
            $fields['shipping_first_name']['required'] = false;
            $fields['shipping_last_name']['required'] = false;
            $fields['shipping_country']['required'] = false;
            $fields['shipping_company']['required'] = false;
            $fields['shipping_city']['required'] = false;
            $fields['shipping_address_1']['required'] = false;
            $fields['shipping_address_2']['required'] = false;
            $fields['shipping_postcode']['required'] = false;
      
        }

        return $fields;
}
add_filter( 'woocommerce_shipping_fields', 'overrideShippingLogic' );

/**End Add CSS and Javascript **/
 
    function pargo_shipping_method() {
    
    if ( ! class_exists( 'Pargo_Shipping_Method' ) ) {

add_action( 'woocommerce_after_checkout_form', 'bbloomer_disable_shipping_local_pickup' );
 
function bbloomer_disable_shipping_local_pickup( $available_gateways ) {
global $woocommerce;
 
// Part 1: Hide shipping based on the static choice @ Cart
// Note: "#customer_details .col-2" strictly depends on your theme
 
$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
$chosen_shipping_no_ajax = $chosen_methods[0];
if ( 0 === strpos( $chosen_shipping_no_ajax, 'wp_pargo' ) ) {
 
?>
<script type="text/javascript">
 
    jQuery('#customer_details .col-2').fadeOut();
 
</script>
<?php
     
} 
 
// Part 2: Hide shipping based on the dynamic choice @ Checkout
// Note: "#customer_details .col-2" strictly depends on your theme
 
?>
<script type="text/javascript">
                jQuery('form.checkout').on('change','input[name^="shipping_method"]',function() {
    var val = jQuery( this ).val();
    if (val.match("^wp_pargo")) {
                jQuery('#customer_details .col-2').fadeOut();
        } else {
        jQuery('#customer_details .col-2').fadeIn();
    }
});
 
</script>
<?php
 
}

            class Pargo_Shipping_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct($instance_id = 0){
                    //$this->instance_id = 0;
                    $this->id                 = 'wp_pargo';
                    $this->method_title       = __( 'Pargo Shipping', 'woocommerce' );
                    $this->method_description = __( 'Shipping Method for Pargo', 'woocommerce' );
                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array(
                        'ZA', //South Africa
                        );
                    //Woocommerce 3 support
                    $this->instance_id = absint( $instance_id );
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Pargo Shipping', 'woocommerce' );

                    $this->supports  = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                    'settings'
                  );

                    $this->init_instance_settings();
                    $this->init();

                }

                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                public function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings();

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                /**
                 * Define settings field for Pargo shipping
                 * @return void 
                 */
                public function init_form_fields(){

                $this->form_fields = array(
                'pargo_enabled' => array(
                  'title'       => __( 'Enable/Disable', 'woocommerce' ),
                  'type'            => 'checkbox',
                  'label'       => __( 'Enable Pargo', 'woocommerce' ),
                  'default'         => 'yes'
                  ),
                'pargo_description' => array(
                  'title'       => __( 'Method Description', 'woocommerce' ),
                  'type'            => 'text',
                  'description'     => __( 'This controls the description next to the Pargo delivery method.', 'woocommerce' ),
                  'default'     => __( 'Not at home? Collect from a local shop when it suits you best', 'woocommerce' ),
                  ),
                'pargo_map_token' => array(
                  'title'       => __( 'Pargo Map Token', 'woocommerce' ),
                  'type'            => 'text',
                  'description'     => __( 'This is you unique map token from Pargo.', 'woocommerce' ),
                  'default'     => __( 'abcdefghijklmnopqrstuvwxyz', 'woocommerce' ),
                  ),
                'pargo_buttoncaption' => array(
                  'title'       => __( 'Pickup Button Caption Before Pickup Point Selection', 'woocommerce' ),
                  'type'            => 'text',
                  'description'     => __( 'Sets the caption of the button that allows users to choose a Pargo pickup point.', 'woocommerce' ),
                  'default'     => __( 'Select a pick up point', 'woocommerce' ),
                  ),
                  'pargo_buttoncaption_after' => array(
                  'title'       => __( 'Pickup Button Caption After Pickup Point Selection', 'woocommerce' ),
                  'type'            => 'text',
                  'description'     => __( 'Sets the caption of the button after a user has selected a Pargo pickup point.', 'woocommerce' ),
                  'default'     => __( 'Re-select a pick up point', 'woocommerce' ),
                  ),
                  'pargo_style_button' => array(
                  'title'       => __( 'Pickup Button Style', 'woocommerce' ),
                  'type'          => 'textarea',
                  'description'       => __( 'Sets the style of the button pressed to select a pickup point.', 'woocommerce' ),
                  'default'         => 'font-size:12px;max-width:250px;border:1px solid #EBEBEB;border-radius:3px;background:#ffeb3b;'
                  ),                        
                'enable_free_shipping' => array(
                  'title'      => __( 'Enable free shipping', 'woocommerce' ),
                  'type'          => 'checkbox',
                  ),
                'free_shipping_amount' => array(
                  'title'       => __( 'Set the minimum amount for free shipping', 'woocommerce' ),
                  'type'          => 'number',
                  ),
                'pargo_style_title' => array(
                  'title'       => __( 'Pargo Point Title Style', 'woocommerce' ),
                  'type'          => 'textarea',
                  'description'       => __( 'Set the style of the selected Pargo Point title.', 'woocommerce' ),
                  'default'         => 'font-size: 16px;font-weight:bold;margin-bottom:0px;margin-top:0px;max-width:250px;'
                  ),
                'pargo_style_desc' => array(
                  'title'       => __( 'Pargo Point Description Style', 'woocommerce' ),
                  'type'          => 'textarea',
                  'description'       => __( 'Set the style of the selected Pargo Point line items.', 'woocommerce' ),
                  'default'         => 'font-size:12px;margin-bottom:0px;margin-top:0px;max-width:250px;'
                  ),
                'pargo_style_image' => array(
                  'title'       => __( 'Pargo Point Image Style', 'woocommerce' ),
                  'type'          => 'textarea',
                  'description'       => __( 'Set the style of the selected Pargo Point image', 'woocommerce' ),
                  'default'         => 'max-width:250px;border:1px solid #EBEBEB;border-radius:2px;'
                  ),
                'weight' => array(
                'title' => __( 'Weight (kg)', 'woocommerce' ),
                'type' => 'number',
                'description' => __( 'Maximum allowed weight per item to use for Pargo delivery', 'woocommerce' ),
                'id' => 'weight',
                'default' => 15
                ),
                'pargo_cost_5' => array(
                  'title'       => __( '5kg Shipping Cost', 'woocommerce' ),
                  'type'            => 'number',
                  'description'     => __( 'This controls the cost of Pargo delivery for 0-5kg items.', 'woocommerce' ),
                  'default'     => __( '75', 'woocommerce' )
                ),
                'pargo_cost_10' => array(
                  'title'       => __( '10kg Shipping Cost', 'woocommerce' ),
                  'type'            => 'number',
                  'description'     => __( 'This controls the cost of Pargo delivery for 5-10kg items.', 'woocommerce' ),
                  'default'     => __( '', 'woocommerce' )
                ),
                'pargo_cost_15' => array(
                  'title'       => __( '15kg Shipping Cost', 'woocommerce' ),
                  'type'            => 'number',
                  'description'     => __( 'This controls the cost of Pargo delivery for 10-15kg items.', 'woocommerce' ),
                  'default'     => __( '', 'woocommerce' )
                ),
                'pargo_cost' => array(
                  'title'       => __( 'No weight Shipping Cost', 'woocommerce' ),
                  'type'            => 'number',
                  'description'     => __( 'This controls the cost of Pargo delivery without product weight settings.', 'woocommerce' ),
                  'default'     => __( '', 'woocommerce' )
                  )
                
                );
          }



         /**
         * calculate_shipping function.
         * WC_Shipping_Method::get_option('pargo_cost_10');
         * @access public
         * @param mixed $package
         * @return void
         */

         public function getPargoSettings(){
              $pargosetting['pargo_description'] = WC_Shipping_Method::get_option('pargo_description');
              $pargosetting['pargo_map_token'] = WC_Shipping_Method::get_option('pargo_map_token');
              $pargosetting['pargo_buttoncaption'] = WC_Shipping_Method::get_option('pargo_buttoncaption');
              $pargosetting['pargo_buttoncaption_after'] = WC_Shipping_Method::get_option('pargo_buttoncaption_after');
              $pargosetting['pargo_style_button'] = WC_Shipping_Method::get_option('pargo_style_button');

              $pargosetting['pargo_style_title'] = WC_Shipping_Method::get_option('pargo_style_title');
              $pargosetting['pargo_style_desc'] = WC_Shipping_Method::get_option('pargo_style_desc');
              $pargosetting['pargo_style_image'] = WC_Shipping_Method::get_option('pargo_style_image');

             return $pargosetting;
         }

        public function calculate_shipping( $package = array() ) {

          $weight = 0;
          $cost = 0;
          $country = $package["destination"]["country"];
 
          foreach ( $package['contents'] as $item_id => $values ) 
          { 
              $_product = $values['data']; 
              if ($_product->get_weight() == '' || $_product->get_weight() == null ) {
                $weight = 0;
              }else{
                $weight = $weight + $_product->get_weight() * $values['quantity']; 
              }
          }

          $weight = wc_get_weight( $weight, 'kg' );


          if ($weight <= 0) {
              $cost = WC_Shipping_Method::get_option('pargo_cost');
          }
          //change of logic from $weight <=1
          elseif(  ($weight > 0) && ($weight <= 5)  ) {
      
              $cost = WC_Shipping_Method::get_option('pargo_cost_5');
       
          } elseif( $weight > 5 && $weight <= 10 ) {
       
              $cost = WC_Shipping_Method::get_option('pargo_cost_10');
       
          } elseif( $weight >10 && $weight <= 15 ) {

              $cost = WC_Shipping_Method::get_option('pargo_cost_15');
       
          } 

          elseif( $weight > 15) {

            //$cost=($this->pargocost15*$weight)/15;            
            
            if( WC()->cart->get_cart_contents_count() > 0){
              $numitems=WC()->cart->get_cart_contents_count();
            } 

            $costfor5=WC_Shipping_Method::get_option('pargo_cost_5');
            $costfor10=WC_Shipping_Method::get_option('pargo_cost_10');
            $costfor15=WC_Shipping_Method::get_option('pargo_cost_15');

            //the calculus
            //global $pargopackages;  
          
            $firstadd=0;
            $secondadd=0;
            //$pargopackages=0;

            $a = (int) ($weight / 5);
            $b = (int) ($weight / 10);
            $c = (int) ($weight / 15);

            //$pargopackages= min($a, $b ,$c);

            //$a is the smallest - 5
            if (($a<=$b) && ($a<=$c)){
            $firstadd = $a * $costfor5;
            $d = $weight % 5;
            }

            //$b is the smallest - 10
            if (($b<=$a) && ($b<=$c)){
            $firstadd = $b * $costfor10;
            $d = $weight % 10;
            }

            //$c is the smallest - 15
            if (($c<=$a) && ($c<=$b)){
            $firstadd = $c * $costfor15;
            $d = $weight % 15;
            }

            //second 

            if (($d <= 5) && ($d > 0)){
            $secondadd=$costfor5; 
            //$pargopackages=$pargopackages+1;
            }

            if (($d > 5) && ($d <= 10) && ($d > 0)){
            $secondadd=$costfor10; 
            //$pargopackages=$pargopackages+1;
            }

            if (($d >10) && ($d <= 15) && ($d >0)){
            $secondadd=$costfor15; 
            //$pargopackages=$pargopackages+1;
            }

            $cost=$firstadd+$secondadd;

            //end calculus
            //return $pargopackages;

          }

          $rate = array(
            'id' => $this->id,
                        //'label' => '<img src="' . plugin_dir_url( __FILE__ ) . 'images/pargo.png" class="pargo-logo">' . $this->title,

            'label' => $this->title . ': ' . WC_Shipping_Method::get_option('pargo_description'),
            'cost' => $cost,
            'calc_tax' => 'per_item'
                       );

                    if(WC_Shipping_Method::get_option('enable_free_shipping') == 'yes') {
                        global $woocommerce;
                        $total_cart_amount = (int) WC()->cart->cart_contents_total;

                        if ($total_cart_amount >= WC_Shipping_Method::get_option('free_shipping_amount')) {
                            $rate['cost'] = 0;
                            //$rate['description'] = 'Free';
                            $rate['label'] = $this->title . ': Free';
                        }

                     }

          // Register the rate
          $this->add_rate( $rate );
          //return (int) $pargopackages;
        } //end cal shiping

                //end calculate shipping function

            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'pargo_shipping_method' );
 
    function add_pargo_shipping_method( $methods ) {
        $methods['wp_pargo'] = 'Pargo_Shipping_Method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_pargo_shipping_method' );

    /**Yellow Button and Display**/

    add_filter( 'woocommerce_cart_shipping_method_full_label', 'wc_pargo_label_change', 10, 2 );

    function wc_pargo_label_change( $label, $method ) {

    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $chosen_shipping = $chosen_methods[0]; 
    
    if ( $chosen_shipping != 'wp_pargo') {
    // if ( $method->method_id != 'pargo') { 
      $label = $label;

      unset($_SESSION['pargo_shipping_address']);
     } 

     else if ( $method->method_id == 'wp_pargo') {

            //unset($_SESSION['shipping_address']);

            //get the backend settings 
            $readyPargoSettings = new Pargo_Shipping_Method();

            $pargoSettings = $readyPargoSettings->getPargoSettings();
            $pargoMerchantUserToken = $pargoSettings['pargo_map_token'];
            $pargoButtonCaptionAfter = $pargoSettings['pargo_buttoncaption_after'];
            $pargo_style_button = $pargoSettings['pargo_style_button'];
            $pargo_style_title = $pargoSettings['pargo_style_title'];
            $pargo_style_desc = $pargoSettings['pargo_style_desc'];
            $pargo_style_image= $pargoSettings['pargo_style_image'];

              //$image = plugins_url( 'images/transparent.png', __FILE__ );
              $image = null;

              $storeName = null;
              $storeAddress = null;
              $businessHours = null;

              if(isset($_SESSION['pargo_shipping_address']['photo'] )){
                  $image = $_SESSION['pargo_shipping_address']['photo'];
              }

               if(isset($_SESSION['pargo_shipping_address']['storeName'] )){
                $storeName = $_SESSION['pargo_shipping_address']['storeName'];
              }

               if(isset($_SESSION['pargo_shipping_address']['address1'] )){
                $storeAddress = $_SESSION['pargo_shipping_address']['address1'];
              }
               if(isset($_SESSION['pargo_shipping_address']['businessHours'] )){
                $businessHours = $_SESSION['pargo_shipping_address']['businessHours'];
              }
            /** Area 51 **/
            $pargo_total_cart_amount = (int) WC()->cart->cart_contents_total;
            /** End Area 51 **/

            //button
            
            $label .= '<div class="pargo-cart"></div>';
            $label .= '<div id ="pargo_selected_pickup_location">';
            $label .= '<img id="pick-up-point-img" src="'. $image .'" style="' . $pargo_style_image . '"></img>';
            $label .= '<p id="pargoStoreName" style="' . $pargo_style_title . '">'. $storeName .'</p>';
            $label .= '<p id="pargoStoreAddress" style="' . $pargo_style_desc . '">'. $storeAddress .'</p>';
            $label .= '<p id="pargoBusinessHours" style="' . $pargo_style_desc . '">'. $businessHours .'</p>';

            $label .= '<button type="button" id="select_pargo_location_button" class="pargo-button" style="' . $pargo_style_button . '">';
            $label .= $pargoSettings['pargo_buttoncaption'];
            $label .= '</button>';
            $label .= '</div>';

            /** HIDDEN FIELDS **/

           $label .= '<input type="hidden" id="pargomerchantusermaptoken" value="' . $pargoMerchantUserToken . '"/>';
           $label .= '<input type="hidden" id="pargobuttoncaptionafter" value="' . $pargoButtonCaptionAfter . '"/>';

        }
            
        return $label;


    }

    /** End --- Yellow Button and Display**/

  add_action('woocommerce_after_checkout_validation', 'pargo_after_checkout_validation');

  function pargo_after_checkout_validation( $posted ) {

    if (isset($_SESSION['pargo_shipping_address'])) {
         //unset($_SESSION['shipping_address']);

    add_action('woocommerce_checkout_update_order_meta',function( $order_id, $posted ) {
    $post = $_SESSION['pargo_shipping_address']['pargoPointCode'];
    $pargo_del_add= "" . $_SESSION['pargo_shipping_address']['storeName'] . ", " . $_SESSION['pargo_shipping_address']['storeName'] . ", " . $_SESSION['pargo_shipping_address']['address1'] . ", " . $_SESSION['pargo_shipping_address']['address2'] . ", " . $_SESSION['pargo_shipping_address']['city'] . ", " . $_SESSION['pargo_shipping_address']['province'] . ", " . $_SESSION['pargo_shipping_address']['postalcode'] . "";
    $order = wc_get_order( $order_id );
    $order->update_meta_data( 'pargo_pc', $post  );
    $order->update_meta_data( 'pargo_delivery_add', $pargo_del_add  );
    $order->save();
    } , 10, 2);
      
    }

  }

    /** End Save Pargo Custom Field **/

    /**
     * Display field value on the order edit page
     */
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'pargo_checkout_field_display_admin_order_meta', 10, 1 );

    function pargo_checkout_field_display_admin_order_meta($order){
    global $woocommerce;
    echo '<p><b>'.__('Pargo Pick Up Address').':</b> ' . get_post_meta( $order->id, 'pargo_delivery_add', true ) . '</p>';
    echo '<p><b>'.__('Pargo Pick Up Point Code').':</b> ' . get_post_meta( $order->id, 'pargo_pc', true ) . '</p>';
    }

    /**End Display custom field value on the order edit page


    /** Pargo Modals **/

    add_action( 'wp_footer', 'pargo_modals' );

    function pargo_modals(){

        echo '<div id="pargo-not-selected" role="dialog" aria-labelledby="mySmallModalLabel">' .
      '<div id="pargo_center">' .
      '<div id="pargo_inner">' .
      '<div class="pargo_blk_title"><center><img src="' . plugin_dir_url( __FILE__ ) . 'images/alert.png" /></center></div>' .
      '<div class="pargo_close">x</div>' .
      '<div class="pargo_content">' .
      '<p>You forgot to select your pick up point!<br />'.
      'Remember, we have pick up locations throughout the country, just pick one!' .
      '</p><img src="' . plugin_dir_url( __FILE__ ) . 'images/click_point.png" />' .
      '</div>' .
      '</div>' .
      '</div>';
      echo '</div>';

    }


    /** End Pargo Modals **/


    //Pargo Weight warning 

    function pargo_validate_order( $posted )   {

    $packages = WC()->shipping->get_packages(); 
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    if( is_array( $chosen_methods ) && in_array( 'wp_pargo', $chosen_methods ) ) {
        foreach ( $packages as $i => $package ) {

            if ( $chosen_methods[ $i ] != "wp_pargo" ) {       
                continue;            
            }
     
            $Pargo_Shipping_Method = new Pargo_Shipping_Method();
            $weightLimit = (int) $Pargo_Shipping_Method->settings['weight'];
            $weight = 0;
     
            foreach ( $package['contents'] as $item_id => $values ) 
            { 
                $_product = $values['data']; 
                $weight = $_product->get_weight(); 
            }
     
            $weight = wc_get_weight( $weight, 'kg' );
            
            if( $weight > $weightLimit ) {
     
                    $message = sprintf( __( 'Sorry, something in your cart of %d kg exceeds the maximum weight of %d kg allowed for %s', 'woocommerce' ), $weight, $weightLimit, $Pargo_Shipping_Method->title );
                         
                    $messageType = "error";
     
                    if( ! wc_has_notice( $message, $messageType ) ) {
                        wc_add_notice( $message, $messageType );
                    }
            }
        }       
    } 
  }//end function Pargo weight warning

  add_action( 'woocommerce_review_order_before_cart_contents', 'pargo_validate_order' , 10 );

  add_action( 'woocommerce_after_checkout_validation', 'pargo_validate_order' , 10 );



  //get customer default shipping address 

        add_action( 'woocommerce_review_order_after_submit', 'pargo_get_customer_default_shipping_details',  10  );
        function pargo_get_customer_default_shipping_details($order_id){
        //global $woocommerce;
            
        }

  //end get customer default shipping address 
  //Clear session Data when order button is pressed or replace shipping address with defualt address
  
  add_action('woocommerce_thankyou', 'pargo_clear_shipping_address_session');

  function pargo_clear_shipping_address_session( $order_id ) {
    //unset($_SESSION['pargo_shipping_address']);
    //re-add.shipping adress here.
    //pargo_validate_order
    //$array = WC_API_Customers::get_customer_billing_address();
  }
  
  add_action( 'woocommerce_order_details_after_order_table', 'pargo_custom_field_display_cust_order_meta', 10, 1 );

  function pargo_custom_field_display_cust_order_meta($order){

    if(isset($_SESSION['pargo_shipping_address']['storeName'] )){
                $storeName = $_SESSION['pargo_shipping_address']['storeName'];
    }
    if(isset($_SESSION['pargo_shipping_address']['address1'] )){
                $storeAddress = $_SESSION['pargo_shipping_address']['address1'];
    }
    if(isset($_SESSION['pargo_shipping_address']['address1'] )){  
    echo '<p><strong>'.__('Pargo Pickup Address').':</strong> ' . $storeName . '. ' . $storeAddress . '</p>';
    //echo '<p><strong>'.__('Pargo Pickup PUP Number').':</strong> ' . get_post_meta( $order->id, 'pargo_pc', true ). '</p>';
    //echo '<p><strong>'.__('Pickup Date').':</strong> ' . get_post_meta( $order->id, 'Pickup Date', true ). '</p>';
    }

    if(isset($_SESSION['pargo_shipping_address'] )){
    unset($_SESSION['pargo_shipping_address']);
    }
  
  }

  //End Clear session data

 }
