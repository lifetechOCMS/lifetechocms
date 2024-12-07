/*
// JQuery to add class container and change display from hidden to show
$(function(){
  $("#lifetech_boot_section_about").addClass("container");
  $("#lifetech_boot_section_about").show();
})*/

/*
// JQuery to make clicked link active
$(document).ready(function() {
  //alert("ready");
  $(".nav-link").click(function(e) {
      e.preventDefault();
      //alert('clicked');
      $('.nav-link').removeClass('active');
      $(this).addClass('active');
  });

});
*/

 document.onreadystatechange = function () {
  var state = document.readyState
  if (state == 'interactive') {
       document.getElementById('contents').style.visibility="hidden";
  } else if (state == 'complete') {
      setTimeout(function(){
         document.getElementById('interactive');
         document.getElementById('preloader').style.visibility="hidden";
         document.getElementById('contents').style.visibility="visible";
      },1000);
  }
  else{
    alert("good")
  }
}