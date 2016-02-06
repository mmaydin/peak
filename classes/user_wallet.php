<?php

class UserWallet
{
    private $user_id;
    private $coin;
    private $gold;

    public function __construct() {
    }

    public function fillByArray($wallet) {
        $this->user_id = $wallet['user_id'];
        $this->coin = $wallet['coin'];
        $this->gold = $wallet['gold'];
    }

    public function fillByFields($userId, $coin, $gold) {
        $this->user_id = $userId;
        $this->coin = $coin;
        $this->gold = $gold;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function setUserId($userId) {
        $this->user_id = $userId;

        return $this;
    }

    public function getCoin() {
        return $this->coin;
    }

    public function setCoin($coin) {
        $this->coin = $coin;

        return $this;
    }

    public function getGold() {
        return $this->gold;
    }

    public function setGold($gold) {
        $this->gold = $gold;

        return $this;
    }
}
