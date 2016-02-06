<?php

class Statistics
{
    public static function getTopList() {
        $connection = Connection::get();

        $sql = '
            SELECT
                u.*,
                SUM(IF(ug.type = "gold" AND ug.expire_date > NOW(), ug.amount - ug.used, 0)) AS gold,
                SUM(IF(ug.type = "coin" AND ug.expire_date > NOW(), ug.amount - ug.used, 0)) AS coin
            FROM users u
            LEFT JOIN user_gifts ug
                ON u.id = ug.user_id
            GROUP BY ug.user_id
            ORDER BY (
                (SUM(IF(ug.type = "gold" AND ug.expire_date > NOW(), ug.amount - ug.used, 0)) * 10) +
                SUM(IF(ug.type = "coin" AND ug.expire_date > NOW(), ug.amount - ug.used, 0))
            ) DESC
        ';

        $STH = $connection->prepare($sql);

        $STH->execute();

        $users = array();
        if ($STH->rowCount() > 0) {
            while($row = $STH->fetch(PDO::FETCH_ASSOC)) {

                $user = new User();
                $user->fillByArray($row);

                $userWallet = new UserWallet();
                $userWallet->setUserId($user->getId());
                $userWallet->setGold($row['gold']);
                $userWallet->setCoin($row['coin']);

                $user->setWallet($userWallet);

                $users[] = $user;
            }
        }

        return $users;
    }
}
