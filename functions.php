<?php

function check_param($data) {
     return htmlspecialchars(strip_tags(trim($data)));
}
