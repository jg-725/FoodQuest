$(document).ready(function(){

	var interval = setInterval(function(){
		$.ajax({
			url: 'public/php/chat.php',
			success: function(data){
				$("#messages").html(data);
			}
		});
	}, 1000);

});
