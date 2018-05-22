/** PARGO 2.0.1 Wordpress Plugin Javascript File **/

jQuery(document).ready(function($){

    function openPargoModal() {

    //get value of map token hidden input
    var pargoMapToken = $('#pargomerchantusermaptoken').val();
    
    $( ".pargo-cart" ).append($( "<div id='pargo_map'><div id='pargo_map_center'><div id='pargo_map_inner'><div class='pargo_map_close'>x</div><iframe id='thePargoPageFrameID' src='https://map.pargo.co.za/?token=" +pargoMapToken+ "' width='100%' height='100%' name='thePargoPageFrame' ></iframe></div></div></div>" ));
    $('#pargo_map').fadeIn(300);
    
    $('.pargo_map_close').on('click',function(){
      $('#pargo_map').fadeOut(300);
    });
    
    }; 


  var pargo_button = "#select_pargo_location_button";
  $(pargo_button).click(function(){
  openPargoModal();
  });

  var body = $('body');
  body.on('click', '#select_pargo_location_button', function () {
        openPargoModal();
  });

  if (window.addEventListener) {
    window.addEventListener("message", selectPargoPoint, false);
  } else {
      window.attachEvent("onmessage", selectPargoPoint);

  }

  /** After Pargo Pickup Point is Selected **/

  function selectPargoPoint(item){
    var saveData = $.ajax({
      type: 'POST',
      url: ajax_object.ajaxurl,
      data: {pargoshipping:item.data},
      success: function(resultData) {
        if(item.data.photo !=""){
      $("#pick-up-point-img").attr("src", item.data.photo);
      $("#pargoStoreName").text(item.data.storeName);
      $("#pargoStoreAddress").text(item.data.address1);
      $("#pargoBusinessHours").text(item.data.businessHours);
      //button text after pickup point selection
      var pargoButtonCaptionAfter = $('#pargobuttoncaptionafter').val();
      $("#select_pargo_location_button").html(pargoButtonCaptionAfter);
   }
       //close the map
       $( "#pargo_map" ).hide( "slow", function() {});

   }


  });

  saveData.error(function() { alert("Something went wrong"); });
  //console.log(item.data);
  }

  

  // Click checkout - make sure point is selected
  body.on('click', 'a.checkout-button.button.alt.wc-forward, #place_order', function(e){
        
    if ( ($('input[value="wp_pargo"]').is(':checked')) || ($('input[value="wp_pargo"]').is(':hidden')) ){
            if ($('#pargoStoreName').is(':empty')){
                e.preventDefault();
        $('#pargo-not-selected').addClass('show-warn');
                
            };
        }
    });


  $('#pargo-not-selected').on('click',function(){
    $(this).removeClass('show-warn');
  });

  //end click checkout

  

});