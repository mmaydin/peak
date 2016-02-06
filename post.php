<?php

include 'require.php';

if (isset($_REQUEST['mode']) && !empty($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
    if ($mode == 'login') {
        $userId = 0;

        if (!isset($_SESSION['user_id'])) {
            $fbId = check_param($_REQUEST['id']);
            $firstName = check_param($_REQUEST['first_name']);
            $lastName = check_param($_REQUEST['last_name']);
            $email = check_param($_REQUEST['email']);
            $photo = check_param($_REQUEST['photo']);

            $user = User::findByFBId($fbId);
            if ($user == null) {
                $user = new User();
                $user->setFbId($fbId);
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setEmail($email);
            }
            $user->setPhoto($photo);
            $user->setActive(true);

            if ($user->save() && $user->getId() > 0) {
                $userId = $user->getId();
                $_SESSION['user_id'] = $user->getId();
            }
        } else {
            $userId = $_SESSION['user_id'];

            $user = User::findById($userId);
            if ($user != null) {
                $user->setActive(true);
                $user->save();
            }
        }

        echo $userId;
        exit;
    } else if ($mode == 'add_friend') {
        $userId = $_SESSION['user_id'];
        if (!empty($userId)) {
            $user = User::findById($userId);
            $friendId = check_param($_REQUEST['friend_id']);
            if (!empty($friendId)) {
                $friend = User::findById($friendId);
                if ($friend) {
                    $user->addFriend($friendId);

                    $friend->addFriend($userId);
                }
            }
        }


        header('location:index.php');
        exit;
    } else if ($mode == 'delete_friend') {
        $userId = $_SESSION['user_id'];
        if (!empty($userId)) {
            $user = User::findById($userId);
            $friendId = check_param($_REQUEST['friend_id']);
            if (!empty($friendId)) {
                $user->deleteFriend($friendId);
            }
        }


        header('location:index.php');
        exit;
    } else if ($mode == 'send_gift') {
        $result = 0;
        $userId = $_SESSION['user_id'];
        if (!empty($userId)) {
            $user = User::findById($userId);
            $friendId = check_param($_REQUEST['friend_id']);
            $giftType = check_param($_REQUEST['gift_type']);
            $amount = check_param($_REQUEST['amount']);
            if (!empty($friendId)) {
                $result = $user->sendGift($friendId, $giftType, $amount);
            }
        }

        echo $result;
        exit;
    } else if ($mode == 'send_request_bonus') {
        $result = 0;
        $userId = $_SESSION['user_id'];
        if (!empty($userId)) {
            $user = User::findById($userId);
            $count = check_param($_REQUEST['count']);
            if (!empty($count)) {
                $result = $user->addGift($count);
            }
        }

        echo $result;
        exit;
    }
}
