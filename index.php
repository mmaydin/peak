<?php

include 'require.php';

if (!(isset($_SESSION['user_id']) && !empty($_SESSION['user_id']))) {
    header('location:login.php');
    exit;
}

$user = User::findById($_SESSION['user_id']);

$topUsers = Statistics::getTopList();

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Peak Games Test Page</title>
        <meta charset="UTF-8">

        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

        <script src="http://code.jquery.com/jquery-1.12.0.min.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

        <script>
            $(function() {
                $("#tab-menu").tabs();
            });
        </script>
    </head>
    <body>

        <div id="tab-menu">
            <ul>
                <li><a href="#menu-friends">FRIENDS</a></li>
                <li><a href="#menu-top25">TOP 25</a></li>
            </ul>
            <div id="menu-friends">
                <table>
                    <?php $i = 1; ?>
                    <?php foreach ($user->getFriends() as $friend): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td>
                                <?php if ($friend->getPhoto() != ''): ?>
                                    <img src="<?php echo $friend->getPhoto(); ?>" alt="<?php echo $friend->getFirstName() . ' ' . $friend->getLastName(); ?>" title="<?php echo $friend->getFirstName() . ' ' . $friend->getLastName(); ?>">
                                <?php else: ?>
                                    <img src="img/nophoto.png" alt="<?php echo $friend->getFirstName() . ' ' . $friend->getLastName(); ?>" title="<?php echo $friend->getFirstName() . ' ' . $friend->getLastName(); ?>">
                                <?php endif; ?>
                            </td>
                            <td><?php echo $friend->getFirstName() . ' ' . $friend->getLastName(); ?></td>
                            <td><?php echo $friend->getWallet()->getCoin(); ?></td>
                            <td><?php echo $friend->getWallet()->getGold(); ?></td>
                            <td>
                                <?php if ($friend->getId() != $user->getId() && !$user->alreadySendGift($friend->getId())): ?>
                                    <a href="#" onclick="chooseGift(<?php echo $friend->getId(); ?>, 'coin');">Send Coins</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                
                                <?php if ($friend->getId() != $user->getId() && !$user->alreadySendGift($friend->getId())): ?>
                                    <a href="#" onclick="chooseGift(<?php echo $friend->getId(); ?>, 'gold');">Send Gold</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="post.php?mode=delete_friend&friend_id=<?php echo $friend->getId(); ?>">Delete Friend</a>
                            </td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                </table>
            </div>
            <div id="menu-top25">
                <table>
                    <?php $i = 1; ?>
                    <?php foreach ($topUsers as $topUser): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td>
                                <?php if ($topUser->getPhoto() != ''): ?>
                                    <img src="<?php echo $topUser->getPhoto(); ?>" alt="<?php echo $topUser->getFirstName() . ' ' . $topUser->getLastName(); ?>" title="<?php echo $topUser->getFirstName() . ' ' . $topUser->getLastName(); ?>">
                                <?php else: ?>
                                    <img src="img/nophoto.png" alt="<?php echo $topUser->getFirstName() . ' ' . $topUser->getLastName(); ?>" title="<?php echo $topUser->getFirstName() . ' ' . $topUser->getLastName(); ?>">
                                <?php endif; ?>
                            </td>
                            <td><?php echo $topUser->getFirstName() . ' ' . $topUser->getLastName(); ?></td>
                            <td><?php echo $topUser->getWallet()->getCoin(); ?></td>
                            <td><?php echo $topUser->getWallet()->getGold(); ?></td>
                            <td>
                                <?php if ($topUser->getId() != $user->getId() && !$user->alreadySendGift($topUser->getId())): ?>
                                    <a href="#" onclick="chooseGift(<?php echo $topUser->getId(); ?>, 'coin');">Send Coins</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                
                                <?php if ($topUser->getId() != $user->getId() && !$user->alreadySendGift($topUser->getId())): ?>
                                    <a href="#" onclick="chooseGift(<?php echo $topUser->getId(); ?>, 'gold');">Send Gold</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($topUser->getId() != $user->getId() && !$user->isFriend($topUser->getId())): ?>
                                    <a href="post.php?mode=add_friend&friend_id=<?php echo $topUser->getId(); ?>">Add Friend</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        NOTE: 1 gold = 10 coin


        <div id="gift-form" title="Send Gift" style="display: none;">
            <form>
                <fieldset>
                    <input type="hidden" name="friend_id">
                    <input type="radio" name="gift_type" value="coin"> Coin
                    <input type="radio" name="gift_type" value="gold"> Gold
                    <br>
                    <label for="name">Amount</label>
                    <input type="text" name="amount" value="0" class="text ui-widget-content ui-corner-all">
             
                    <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
                </fieldset>
            </form>
        </div>

        <script>

            var currentGold = <?php echo $user->getWallet()->getGold(); ?>,
            currentCoin = <?php echo $user->getWallet()->getCoin(); ?>,
            giftForm, form,
            amount = $('input[name="amount"]'),
            allFields = $([]).add(amount),
            tips = $('.validateTips');

            function chooseGift(friendId, giftType) {
                $('input[name="friend_id"]').val(friendId);
                $('input[name="gift_type"][value="coin"]').attr('checked', 'checked');
                sendApplicationRequest();
                giftForm.dialog('open');
            }

            function sendGift() {
                var giftType = $('input[name="gift_type"]:checked').val(),
                amount = $('input[name="amount"]').val();

                if ((giftType == 'gold' && currentGold >= amount) || (giftType == 'coin' && currentCoin >= amount)) {

                    var response = {};
                    response.friend_id = $('input[name="friend_id"]').val();
                    response.gift_type = giftType;
                    response.amount = amount;
                    $.ajax({
                        url: 'post.php?mode=send_gift',
                        data: response,
                        dataType: 'json',
                        success: function (data) {
                            if (data > 0) {
                                giftForm.dialog('close');

                                window.location = 'index.php';
                            }
                        }
                    });
                }

            }

            function sendApplicationRequest() {
                FB.ui({                    
                    method: 'apprequests',
                    title: 'Invite friends to join you',
                    message: 'Come play with me.'
                },
                function (res) {
                    if (res && res.request) {
                        $.ajax({
                            url: 'post.php?mode=send_request_bonus',
                            data: {'count': res.to.length},
                            dataType: 'json',
                            success: function (data) {
                            }
                        });
                    }
                });
                return false; 
            }

            window.fbAsyncInit = function() {
                FB.init({
                    appId      : '<?php echo FB_APP_ID; ?>',
                    cookie     : true,
                    xfbml      : true,
                    version    : 'v2.2'
                });

                FB.getLoginStatus(function(response) {
                    if (response.status !== 'connected') {
                        window.location = 'login.php';
                    }
                }); 
            }; 

            giftForm = $('#gift-form').dialog({
                autoOpen: false,
                height: 250,
                width: 350,
                modal: true,
                buttons: {
                    'Send': sendGift,
                    'Cancel': function() {
                        giftForm.dialog('close');
                    }
                },
                close: function() {
                    allFields.removeClass('ui-state-error');
                }
            });

            $(document).ready(function() {
                $('input[name="amount"]').keydown(function (e) {
                    // Allow: backspace, delete, tab, escape, enter and .
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                         // Allow: Ctrl+A, Command+A
                        (e.keyCode == 65 && ( e.ctrlKey === true || e.metaKey === true ) ) || 
                         // Allow: home, end, left, right, down, up
                        (e.keyCode >= 35 && e.keyCode <= 40)) {
                             // let it happen, don't do anything
                             return;
                    }
                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });
            });

            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = '//connect.facebook.net/en_US/sdk.js';
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
    </body>
</html>
