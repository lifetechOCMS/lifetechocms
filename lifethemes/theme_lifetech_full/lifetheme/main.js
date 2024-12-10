 
 // =============  Data Table - (Start) ================= //

$(document).ready(function () {

    var table = $('#example , #example2').DataTable({

        buttons: ['copy', 'csv', 'excel', 'pdf', 'print']

    });


    table.buttons().container()
        .appendTo('#example_wrapper .col-md-6:eq(0)');

});

 
 
window.addEventListener("scroll", function () {
    var navbar = document.querySelector(".navbar");

    if (window.scrollY > 200) {
        navbar.classList.add("fixed-top");
    } else {
        navbar.classList.remove("fixed-top");
    }
});
 