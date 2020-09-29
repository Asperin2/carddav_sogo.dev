<?php

/**
 * Created by PhpStorm.
 * User: eakhmetov
 * Date: 06.12.16
 * Time: 15:34
 */
class CarddavImages {

    function __construct($config) {
        $this->cardImages($config);
    }

    public function cardImages($config) {
        $mysqli = new mysqli($config['source_db_host'], $config['source_db_user'], $config['source_db_password'], $config['source_db_name']);
        if ($result = $mysqli->query("SELECT ui.uid, ad.id, FROM_BASE64(ui.image) AS img FROM user_image ui JOIN addressbook ad ON ad.uid = ui.uid")) {
            while ($data = $result->fetch_assoc()) {
                $im  = imagecreatefromstring($data['img']);
                $koff = imagesx($im)/$config['photo_width'];
                $height = (int) imagesy($im)/$koff;
                $thumb = imagecreatetruecolor($config['photo_width'], $height);
                imagecopyresized($thumb, $im, 0, 0, 0, 0, $config['photo_width'], $height, imagesx($im), imagesy($im));
                if (imagejpeg($thumb, $config['photo_path'].$data['uid'].".jpg")) {

                }
                imagedestroy($thumb);
            }
        }
    }
}