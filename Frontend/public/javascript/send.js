$("#form_input").submit(function(){
  var sender = $("#sender").val();
  var message = $("#msg").val();
  $.ajax({
    url: 'public/php/send.php',
    data: { sender: sender, message: message },
    success: function(data){
        // successfull POST request
        $("#feedback").html(data);

        $("#feedback").fadeIn("slow", function(){
              $("#feedback").fadeOut(5000);
        });

        $("#msg").val(' ');
    }
  });
  return false;
});
