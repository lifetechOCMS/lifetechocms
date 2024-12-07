let modal = document.getElementById("myModal");

// Get the button that opens the modal
let btn = document.getElementsByClassName("editModal")[0];
// let btn = $('.editModal');

// Get the <span> element that closes the modal
let span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal
// btn.onclick = function(e) {


// }

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
} 