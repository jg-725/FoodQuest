<!DOCTYPE html>
<html>
<head>
    <title>Login Response</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="response"></div>
    <script>
        function checkLoginStatus() {
            $.ajax({
                url: "checkResponseQueue.php",
                type: "GET",
                dataType: "json",
                success: function (data) {
                    if (data.status === "success") {
                        $("#response").html("Login successful!");
                    } else if (data.status === "failure") {
                        $("#response").html("Login failed!");
                    } else {
                        setTimeout(checkLoginStatus, 1000); // Retry after 1 second
                    }
                },
                error: function () {
                    setTimeout(checkLoginStatus, 1000); // Retry after 1 second
                }
            });
        }

        // Start checking login status when the page loads
        $(document).ready(function () {
            checkLoginStatus();
        });
    </script>
</body>
</html>

