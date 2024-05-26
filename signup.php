<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="assets/css/signup.css">
    <style>
        .error {
            color: red;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="cont_principal">
    <div class="cont_centrar">
        <div class="cont_login">
            <form action="signup_process.php" method="POST" novalidate>
                <div class="cont_tabs_login">
                    <ul class='ul_tabs'>
                        <li><a href="login.php?tab=login" onclick="sign_in(event)">SIGN IN</a>
                            <span class="linea_bajo_nom"></span>
                        </li>
                        <li class="active"><a href="signup.php?tab=signup" onclick="sign_up(event)">SIGN UP</a><span class="linea_bajo_nom"></span></li>
                    </ul>
                </div>
                <div class="cont_text_inputs">
                    <input type="text" class="input_form_sign" placeholder="USERNAME" name="username" required />
                    <input type="email" class="input_form_sign d_block active_inp" placeholder="EMAIL" name="email" required />
                    <input type="password" class="input_form_sign d_block active_inp" placeholder="PASSWORD" name="password" required />
                    <input type="password" class="input_form_sign" placeholder="CONFIRM PASSWORD" name="confirm_password" required />
                    
                </div>
                <div class="cont_btn">
                    <button class="btn_sign">SIGN UP</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="assets/js/signup.js"></script>
</body>
</html>
