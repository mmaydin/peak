<?php

class User
{
    private $id;
    private $fb_id;
    private $first_name;
    private $last_name;
    private $email;
    private $photo = '';
    private $wallet;
    private $gifts;
    private $send_gift_user_ids;
    private $friends;
    private $active = false;

    public function __construct() {
    }

    public function fillByArray($user) {
        $this->id = $user['id'];
        $this->fb_id = $user['fb_id'];
        $this->first_name = $user['first_name'];
        $this->last_name = $user['last_name'];
        $this->email = $user['email'];
        $this->photo = $user['photo'];
        $this->active = $user['active'];
    }

    public function fillByFields($id, $fbId, $firstName, $lastName, $email, $photo, $active = false) {
        $this->id = $id;
        $this->fb_id = $fbId;
        $this->first_name = $firstName;
        $this->last_name = $lastName;
        $this->email = $email;
        $this->photo = $photo;
        $this->active = $active;
    }

    public function getId() {
        return $this->id;
    }

    public function getFbId() {
        return $this->fb_id;
    }

    public function setFbId($fbId) {
        $this->fb_id = $fbId;

        return $this;
    }

    public function getFirstName() {
        return $this->first_name;
    }

    public function setFirstName($firstName) {
        $this->first_name = $firstName;

        return $this;
    }

    public function getLastName() {
        return $this->last_name;
    }

    public function setLastName($lastName) {
        $this->last_name = $lastName;

        return $this;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    public function getPhoto() {
        return $this->photo;
    }

    public function setPhoto($photo) {
        $this->photo = $photo;

        return $this;
    }

    public function isActive() {
        return $this->active;
    }

    public function setActive($active) {
        $this->active = $active;

        return $this;
    }

    public function getWallet() {
        if ($this->wallet == null) {
            $connection = Connection::get();

            $sql = 'SELECT
                        *,
                        ug.user_id,
                        ug.type,
                        (ug.amount - ug.used) AS total 
                    FROM user_gifts ug
                    LEFT JOIN users u
                        ON u.id = ug.user_id
                    WHERE
                        ug.user_id = :user_id AND
                        ug.expire_date >= NOW() AND
                        ug.amount - ug.used > 0
                    ';

            $STH = $connection->prepare($sql);
            $STH->bindValue('user_id', $this->id);
            $STH->execute();

            $gold = 0;
            $coin = 0;
            if ($STH->rowCount() > 0) {
                while($row = $STH->fetch(PDO::FETCH_ASSOC)) {
                    if ($row['type'] == 'gold') {
                        $gold += $row['total'];
                    } else if ($row['type'] == 'coin') {
                        $coin += $row['total'];
                    }
                }

            }

            $this->wallet = new UserWallet();
            $this->wallet->setUserId($this->id);
            $this->wallet->setCoin($coin);
            $this->wallet->setGold($gold);
        }

        return $this->wallet;
    }

    public function setWallet(UserWallet $wallet) {
        $this->wallet = $wallet;

        return $this;
    }

    public function getFriends() {
        if ($this->friends == null) {
            $connection = Connection::get();

            $sql = 'SELECT
                        * 
                    FROM user_friends uf
                    LEFT JOIN users u
                        ON u.id = uf.friend_id
                    WHERE uf.user_id = :user_id';

            $STH = $connection->prepare($sql);
            $STH->bindValue('user_id', $this->id);
            $STH->execute();

            $this->friends = array();
            if ($STH->rowCount() > 0) {
                while($row = $STH->fetch(PDO::FETCH_ASSOC)) {

                    $user = new User();
                    $user->fillByArray($row);

                    $this->friends[] = $user;
                }
            }
        }

        return $this->friends;
    }

    public function addFriend($friendId) {
        $connection = Connection::get();

        $sql = 'INSERT INTO user_friends
                            ( user_id, friend_id)
                    VALUES ( :user_id, :friend_id)';

        $STH = $connection->prepare($sql);
        $STH->bindParam(':user_id', $this->id);
        $STH->bindParam(':friend_id', $friendId);

        $this->friends = null;

        return $STH->execute();
    }

    public function deleteFriend($friendId) {
        $connection = Connection::get();

        $sql = 'DELETE FROM user_friends WHERE user_id = :user_id AND friend_id = :friend_id';

        $STH = $connection->prepare($sql);
        $STH->bindParam(':user_id', $this->id);
        $STH->bindParam(':friend_id', $friendId);

        $this->friends = null;

        return $STH->execute();
    }

    public function isFriend($friendId) {
        if (is_array($this->friends)) {
            foreach ($this->friends as $friend) {
                if ($friend->getId() == $friendId) {
                    return true;
                }
            }
        }

        return false;
    }

    public function alreadySendGift($friendId) {
        if ($this->send_gift_user_ids == null) {
            $connection = Connection::get();
            $today = date('Y-m-d');
            $sql = 'SELECT user_id FROM user_gifts WHERE sender_id = :sender_id AND DATE_FORMAT(created_date, "%Y-%m-%d") = :today';

            $STH = $connection->prepare($sql);
            $STH->bindParam(':sender_id', $this->id);
            $STH->bindParam(':today', $today);
            $STH->execute();

            $this->send_gift_user_ids = array();
            while($row = $STH->fetch(PDO::FETCH_ASSOC)) {
                $this->send_gift_user_ids[] = $row['user_id'];
            }
        }

        return in_array($friendId, $this->send_gift_user_ids);
    }

    public function addGift($count) {
        $connection = Connection::get();
        $expireDate = date('Y-m-d H:i:s', strtotime('+1 week'));
            
        $sql = 'INSERT INTO user_gifts
                            (user_id, expire_date, type, amount, used)
                        VALUES(:user_id, :expire_date, :type, :amount, 0)';

        for ($i = 0; $i < $count; $i++) {
            $giftType = 'coin'; 
            $giftAmount = 1000; 
            $STH = $connection->prepare($sql);
            $STH->bindParam(':user_id', $this->id);
            $STH->bindParam(':type', $giftType);
            $STH->bindParam(':amount', $giftAmount);
            $STH->bindParam(':expire_date', $expireDate);
            $STH->execute();
           
            $giftType = 'gold'; 
            $giftAmount = 100; 
            $STH = $connection->prepare($sql);
            $STH->bindParam(':user_id', $this->id);
            $STH->bindParam(':type', $giftType);
            $STH->bindParam(':amount', $giftAmount);
            $STH->bindParam(':expire_date', $expireDate);
            $STH->execute();
        } 
    }

    public function sendGift($friendId, $type, $amount) {
        $connection = Connection::get();

        $result = false;
        $sql = 'SELECT
                    *,
                    amount - used AS total
                FROM user_gifts
                WHERE
                    user_id = :user_id AND
                    type = :type AND
                    expire_date > NOW()
                HAVING total > 0
                ORDER BY expire_date ASC';

        $STH = $connection->prepare($sql);
        $STH->bindParam(':user_id', $this->id);
        $STH->bindParam(':type', $type);

        $STH->execute();

        if ($STH->rowCount() > 0) {
            $used = $amount;
            while($row = $STH->fetch(PDO::FETCH_ASSOC)) {
                if ($row['total'] >= $amount) {

                    $sql = 'UPDATE user_gifts SET used = :used WHERE id = :id';

                    $STHU = $connection->prepare($sql);
                    $STHU->bindParam(':id', $row['id']);
                    $STHU->bindParam(':used', $used);

                    $STHU->execute();
                 
                    $used = 0;   
                    break;
                } else {
                    $sql = 'UPDATE user_gifts SET used = amount WHERE id = :id';

                    $STHU = $connection->prepare($sql);
                    $STHU->bindParam(':id', $row['id']);

                    $STHU->execute();

                    $used -= $row['total'];
                }
            }

            if ($used == 0) {
                $result = true;

                $expireDate = date('Y-m-d H:i:s', strtotime('+1 week'));
            
                $sql = 'INSERT INTO user_gifts
                                    (user_id, sender_id, expire_date, type, amount, used)
                                VALUES(:user_id, :sender_id, :expire_date, :type, :amount, 0)';

                $STHI = $connection->prepare($sql);
                $STHI->bindParam(':user_id', $friendId);
                $STHI->bindParam(':sender_id', $this->id);
                $STHI->bindParam(':type', $type);
                $STHI->bindParam(':amount', $amount);
                $STHI->bindParam(':expire_date', $expireDate);
                $STHI->execute();
                
            }
        }

        return $result;
    }

    public function save() {
        if (!empty($this->id)) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    private function insert() {
        $connection = Connection::get();

        $sql = 'INSERT INTO users
                            ( fb_id, first_name, last_name, email, photo, active)
                    VALUES ( :fb_id, :first_name, :last_name, :email, :photo, :active)';
        $STH = $connection->prepare($sql);
        $STH->bindParam(':fb_id', $this->fb_id);
        $STH->bindParam(':first_name', $this->first_name);
        $STH->bindParam(':last_name', $this->last_name);
        $STH->bindParam(':email', $this->email);
        $STH->bindParam(':photo', $this->photo);
        $STH->bindParam(':active', $this->active);

        if ($STH->execute()) {
            $this->id = $connection->lastInsertId();
            $this->addGift(1);

            return $this->id;
        } else {
            return false;
        }
    }

    private function update() {
        $connection = Connection::get();

        $sql = 'UPDATE users SET
                    first_name = :first_name, 
                    last_name = :last_name, 
                    email = :email, 
                    photo = :photo, 
                    active = :active
                WHERE id = :id';

        $STH = $connection->prepare($sql);
        $STH->bindParam(':id', $this->id);
        $STH->bindParam(':first_name', $this->first_name);
        $STH->bindParam(':last_name', $this->last_name);
        $STH->bindParam(':email', $this->email);
        $STH->bindParam(':photo', $this->photo);
        $STH->bindParam(':active', $this->active);

        return $STH->execute();
    }

    public static function findById($id) {
        $connection = Connection::get();

        $sql = 'SELECT
                    *
                FROM users
                WHERE id = :id';

        $STH = $connection->prepare($sql);
        $STH->bindValue(':id', $id);
        $STH->execute();

        if ($STH->rowCount() > 0) {
            while($row = $STH->fetch(PDO::FETCH_ASSOC)) {

                $user = new User();
                $user->fillByArray($row);

                return $user;
            }
        }

        return null;
    }

    public static function findByFBId($fbId) {
        $connection = Connection::get();

        $sql = 'SELECT
                    * 
                FROM users
                WHERE fb_id = :fb_id';

        $STH = $connection->prepare($sql);
        $STH->bindValue(':fb_id', $fbId);
        $STH->execute();

        if ($STH->rowCount() > 0) {
            while($row = $STH->fetch(PDO::FETCH_ASSOC)) {

                $user = new User();
                $user->fillByArray($row);

                return $user;
            }
        }

        return null;
    }
}
