<?php 

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
  
  //Prepare data
  date_default_timezone_set('Africa/Johannesburg');
  $optindate = date('Y/m/d h:i:s', time());
  $current_user = wp_get_current_user();

?>

<div id="fs_connect" class="wrap">
    <div class="fs-visual">
      <div class="fs-plugin-icon">
  <img src="<?php echo plugins_url( 'images/pargo-optin-logo.png', __FILE__ ); ?>" width="80" height="80"/>
</div>      
    </div>

    <div class="fs-content">
            <p>Hey <?php echo $current_user->display_name ?>,<br>Please help us improve <b>Pargo Shipping</b>! If you opt in, some data about your usage of <b>Pargo Shipping</b> will be sent to <a href="https://pargo.co.za" target="_blank" tabindex="1">Pargo.co.za</a>. If you skip this, that&#039;s okay! <b>Pargo Shipping</b> will still work just fine.</p>
          </div>

    <div class="fs-actions">

      <form id="pargooptinform" method="post" action="" >
              <button id="pargooptinno" type="submit" class="button button-secondary" tabindex="2">Skip</button>
              <input id="consent" type="hidden" name="consent" value="">
              <input type="hidden" name="pargoconfirmoptintracking" value="pargoconfirmoptintracking">    
              <button id="pargooptinyes" type="submit" class="button button-primary" tabindex="1" name="pargoconfirmoptintracking">Allow &amp; Continue</button>
      </form>
          </div>        
              <div class="fs-permissions">
                        <a class="fs-trigger" href="#" tabindex="1">What permissions are being granted?</a>
                <ul>
                <li id="fs-permission-profile" class="fs-permission fs-profile">
                  <i class="dashicons dashicons-admin-users"></i>
                  <div>
                    <span>Website</span>
                    <p>Name and email address</p>
                  </div>
                </li>
                <li id="fs-permission-site"
                    class="fs-permission fs-site">
                  <i class="dashicons dashicons-admin-settings"></i>
                  <div>
                    <span>Brief Site Overview</span>
                    <p>Site URL, WP version, Pargo Plugin Version, Active theme</p>
                  </div>
                </li>             
                <li id="fs-permission-events"
                    class="fs-permission fs-events">
                  <i class="dashicons dashicons-admin-plugins"></i>
                  <div>
                    <span>Current Plugin Events</span>
                    <p>Activation, deactivation and uninstall</p>
                  </div>
                </li>
                </ul>
        </div>
              <div class="fs-terms">
      <a href="https://pargo.co.za/privacy-policy/" target="_blank" tabindex="1">Privacy Policy</a>
      &nbsp;&nbsp;-&nbsp;&nbsp;
      <a href="https://pargo.co.za/terms-and-conditions/" target="_blank" tabindex="1">Terms of Service</a>
    </div>
  </div>
<script>
    jQuery(document).ready(function($){
    var form = jQuery('#pargooptinform');
    var consent = jQuery('#consent');

    jQuery( "#pargooptinno" ).click(function(e) {
              document.getElementById("consent").setAttribute('value','no');
               form.submit();
              window.location.href = "<?php echo get_bloginfo('url'); ?>/wp-admin/admin.php?page=pargo-shipping";
    });

    jQuery( "#pargooptinyes" ).click(function(e) {
              document.getElementById("consent").setAttribute('value','yes');
    //send data via ajax
    var SendInfo={
        "user_name": "<?php echo $current_user->user_login; ?>",
        "user_email": "<?php echo $current_user->user_email; ?>",
        "module_version": "<?php echo PARGOPLUGINVERSION; ?>",
        "site_name": "<?php echo get_bloginfo('name'); ?>",
        "site_url": "<?php echo get_bloginfo('url'); ?>",
        "site_ip": "<?php echo getRealIpAddr(); ?>",
        "platform_name": "WordPress",
        "platform_version": "<?php echo get_bloginfo('version'); ?>",
        "active_theme": "<?php echo wp_get_theme(); ?>",
        "install_date": "<?php echo $optindate; ?>",
        "uninstall_date": null
      };

    $.ajax({
        url: "http://pargodw.pargosandbox.co.za/api/analytic",
        type: "POST",
        crossDomain: true,
        contentType: "application/json",
        data: JSON.stringify(SendInfo),
        dataType: "json",
        async: false,
        beforeSend: function (xhr) {
         xhr.setRequestHeader('Authorization', 'Bearer <?php echo $access_Token; ?>');
        },
        success:function(result){
            //alert(JSON.stringify(result));
            window.location.href = "<?php echo get_bloginfo('url'); ?>/wp-admin/admin.php?page=pargo-shipping";
        },
        error:function(xhr,status,error){
            //alert(status);
        }
    });
          form.submit();
          //$('form#pargooptinform').submit();
          window.location = "<?php echo get_bloginfo('url'); ?>/wp-admin/admin.php?page=pargo-shipping";
         // document.getElementById("pargooptinform").submit()
    });
   // return false;   
    });

  </script>  
  <?php
    function getRealIpAddr(){
          if (!empty($_SERVER['HTTP_CLIENT_IP'])){  
            //check ip from share internet
            $ip=$_SERVER['HTTP_CLIENT_IP'];
          }
          elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   { 
            //to check ip is pass from proxy
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
          }
          else{ $ip=$_SERVER['REMOTE_ADDR']; }
          return $ip;
        }
?>