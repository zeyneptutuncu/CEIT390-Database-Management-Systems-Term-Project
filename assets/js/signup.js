function sign_up() {
    var inputs = document.querySelectorAll('.input_form_sign');
    document.querySelectorAll('.ul_tabs > li')[0].className = ""; 
    document.querySelectorAll('.ul_tabs > li')[1].className = "active"; 

    for (var i = 0; i < inputs.length; i++) {
        if (i == 2) {
            // Skip the password input
        } else {  
            document.querySelectorAll('.input_form_sign')[i].className = "input_form_sign d_block";
        }
    } 

    setTimeout(function () {
        for (var d = 0; d < inputs.length; d++) {
            document.querySelectorAll('.input_form_sign')[d].className = "input_form_sign d_block active_inp";  
        }
    }, 100);

    document.querySelector('.link_forgot_pass').style.opacity = "0";
    document.querySelector('.link_forgot_pass').style.top = "-5px";
    document.querySelector('.btn_sign').innerHTML = "SIGN UP";    

    setTimeout(function () {
        document.querySelector('.terms_and_cons').style.opacity = "1";
        document.querySelector('.terms_and_cons').style.top = "5px";
    }, 500);

    setTimeout(function () {
        document.querySelector('.link_forgot_pass').className = "link_forgot_pass d_none";
        document.querySelector('.terms_and_cons').className = "terms_and_cons d_block";
    }, 450);
}

function sign_in() {
    var inputs = document.querySelectorAll('.input_form_sign');
    document.querySelectorAll('.ul_tabs > li')[0].className = "active"; 
    document.querySelectorAll('.ul_tabs > li')[1].className = ""; 

    for (var i = 0; i < inputs.length; i++) {
        switch (i) {
            case 1:
                console.log(inputs[i].name);
                break;
            case 2:
                console.log(inputs[i].name);
            default:
                document.querySelectorAll('.input_form_sign')[i].className = "input_form_sign d_block";
        }
    } 

    setTimeout(function () {
        for (var d = 0; d < inputs.length; d++) {
            switch (d) {
                case 1:
                    console.log(inputs[d].name);
                    break;
                case 2:
                    console.log(inputs[d].name);
                default:
                    document.querySelectorAll('.input_form_sign')[d].className = "input_form_sign d_block";  
                    document.querySelectorAll('.input_form_sign')[2].className = "input_form_sign d_block active_inp";  
            }
        }
    }, 100);

    document.querySelector('.terms_and_cons').style.opacity = "0";
    document.querySelector('.terms_and_cons').style.top = "-5px";

    setTimeout(function () {
        document.querySelector('.terms_and_cons').className = "terms_and_cons d_none"; 
        document.querySelector('.link_forgot_pass').className = "link_forgot_pass d_block";
    }, 500);

    setTimeout(function () {
        document.querySelector('.link_forgot_pass').style.opacity = "1";
        document.querySelector('.link_forgot_pass').style.top = "5px";
        for (var d = 0; d < inputs.length; d++) {
            switch (d) {
                case 1:
                    console.log(inputs[d].name);
                    break;
                case 2:
                    console.log(inputs[d].name);
                default:
                    document.querySelectorAll('.input_form_sign')[d].className = "input_form_sign";  
            }
        }
    }, 1500);

    document.querySelector('.btn_sign').innerHTML = "SIGN IN";    
}

window.onload = function () {
    document.querySelector('.cont_centrar').className = "cont_centrar cent_active";

    // Check if the tab parameter is set in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab === 'signup') {
        sign_up();
    } else {
        sign_in();
    }
}

document.querySelector('form').addEventListener('submit', function (e) {
    e.preventDefault(); // Formun varsayılan gönderimini durdurur

    const formData = new FormData(this);

    fetch('signup_process.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'error') {
                const errorField = document.querySelector(`[name="${data.field}"]`);
                errorField.setCustomValidity(data.message);
                errorField.reportValidity();
            } else if (data.status === 'success') {
                window.location.href = 'login.php';
            }
        })
        .catch(error => console.error('Error:', error));
});
