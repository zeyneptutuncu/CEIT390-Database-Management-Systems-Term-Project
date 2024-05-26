<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
    
<body>
<div class="cont_principal">
    <div class="cont_centrar">
        <div class="cont_login">
            <form id="loginForm">
                <div class="cont_tabs_login">
                    <ul class='ul_tabs'>
                        <li class="active"><a href="login.php?tab=login" onclick="sign_in(event)">SIGN IN</a>
                            <span class="linea_bajo_nom"></span>
                        </li>
                        <li><a href="signup.php?tab=signup" onclick="sign_up(event)">SIGN UP</a><span class="linea_bajo_nom"></span></li>
                    </ul>
                </div>
                <div class="cont_text_inputs">
                    <input type="email" class="input_form_sign d_block active_inp" placeholder="EMAIL" name="email" required />
                    <input type="password" class="input_form_sign d_block active_inp" placeholder="PASSWORD" name="password" required />
                    
                </div>
                <div class="cont_btn">
                    <button type="submit" class="btn_sign">SIGN IN</button>
                </div>
                <div class="error_message" id="error_message" style="display: none;"></div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#loginForm").on("submit", function(event) {
            event.preventDefault();

            $.ajax({
                type: "POST",
                url: "login_process.php",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        window.location.href = "dashboard.php";
                    } else {
                        $("#error_message").text(response.message).show();
                    }
                }
            });
        });
    });
</script>
</body>
</html>
