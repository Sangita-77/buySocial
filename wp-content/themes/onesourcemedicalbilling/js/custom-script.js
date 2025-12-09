jQuery(function($){
	var nav_offset_top = $('#masthead').height(); 
	function navbarFixed(){
	  if ( $('#masthead').length ){
	      $(window).scroll(function() {
	          var scroll = $(window).scrollTop();   
	          if (scroll >= nav_offset_top ) {
	              $("#masthead").addClass("navbar_fixed");
	          } else {
	              $("#masthead").removeClass("navbar_fixed");
	          }
	      });
	  };
	};
	navbarFixed();
  });



// Get this script ready when the page loads
jQuery(function($){
  //   Create a function
  $(".scrollDown").click(function(event) {
    //       Select the body of the page and scroll down by 650 pixels worth
    $("html, body").animate({ scrollTop: "+=450px" }, 800);
  });
});






















