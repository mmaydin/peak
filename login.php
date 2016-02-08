<?php include 'require.php'; ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Peak Games Test Login Page</title>
        <meta charset="UTF-8">

        <script src="http://code.jquery.com/jquery-1.12.0.min.js"></script>
    </head>
    <body>
    <script>
        function statusChangeCallback(response) {
            if (response.status === 'connected') {
                successLogin();
            } else if (response.status === 'not_authorized') {
                document.getElementById('status').innerHTML = 'Lütfen Facebook hesabınızla giriş yapınız';
            } else {
                document.getElementById('status').innerHTML = 'Lütfen Facebook hesabınızla giriş yapınız';
            }
        }

        function checkLoginState() {
            FB.getLoginStatus(function(response) {
                statusChangeCallback(response);
            });
        }

        window.fbAsyncInit = function() {
            FB.init({
                appId      : '<?php echo FB_APP_ID; ?>',
                cookie     : true,
                xfbml      : true,
                version    : 'v2.0'
            });

            FB.getLoginStatus(function(response) {
                statusChangeCallback(response);
            });
        };

        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = '//connect.facebook.net/en_US/sdk.js';
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        function successLogin() {
            FB.api('/me', function(response) {
                FB.api(
                    '/' + response.id + '/picture',
                    function (pictureResponse) {
                        if (pictureResponse && !pictureResponse.error) {
                            response.photo = pictureResponse.data.url; 
                        }

                        $.ajax({
                            url: 'post.php?mode=login',
                            data: response,
                            dataType: 'json',
                            success: function (data) {
                                if (data > 0) {
                                    window.location = 'index.php';
                                }
                            }
                        });
                    }
                );
            });
        }
    </script>

    <fb:login-button scope="public_profile,email,user_friends" onlogin="checkLoginState();">
    </fb:login-button>

    </body>
</html>
