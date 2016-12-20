<?php

/**
 * Created by PhpStorm.
 * User: eakhmetov
 * Date: 06.12.16
 * Time: 14:54
 */
class CarddavSogo
{
    const HOST_172 = '192.168.0.172';
    const USER_172 = 'projecto';
    const PASSWORD_172 = 'pro3dav5';
    const BD_172 = 'projectobook';

    const HOST_SOGO = '127.0.0.1';
    const USER_SOGO = 'root';
    const PASSWORD_SOGO = 'ecocomp';
    const BD_SOGO = 'sogo2';
    const TABLE_QUICK_SOGO = 'sogoadmin00239079b10_quick';
    const TABLE_MAIN_SOGO = 'sogoadmin00239079b10';

    const TABLE_QUICK_SOGO_DISSMISED = 'sogouser00322098e1d_quick';
    const TABLE_MAIN_SOGO_DISSMISED = 'sogouser00322098e1d';

    const DECRET_GROUP = 'dd43039b-8003-4665-9ffb-28bfe500206b';
    const DISMISSED_GROUP = '14b6476c-745d-4a71-99de-63ba675ef476';

    const CELL_PHONES = 1;
    const WORK_PHONES = 2;
    const HOME_PHONES = 3;
    const WORK_EMAILS = 1;
    const PRIVATE_EMAILS = 2;
    const WORK_ADDRESSES = 1;
    const HOME_ADDRESSES = 2;
    const PHOTO_PATH = '/home/eakhmetov/sogo/carddav_sogo.dev/photo/';

    public $corpData = array (['name' => 'ПЭК, рекламный номер', 'phone' => '+7 (843) 273-77-22', 'note' => 'работает только на входящие', 'logo' => 'pek.png', 'uid' => 'bf229a3a-97d9-40f2-ad61-128a1b9bf6b4'],
        ['name' => ' Диспетчерская служба ПЭК', 'phone' => '+7 (843) 273-77-12', 'note' => 'работает только на входящие', 'logo' => 'pek.png', 'uid' => 'a9d02bc6-8d39-461c-ea65-06d0de727234'],
        ['name' => 'ПЭК', 'phone' => '+7 (843) 567-29-04', 'note' => 'для исходящих', 'logo' => 'pek.png', 'uid' => '21c40f05-c148-4742-a8b8-1555c710edca'],
        ['name' => 'ТеплоЭнергоСервис', 'phone' => '+7 (843) 567-29-03', 'note' => '', 'logo' => '', 'uid' => 'f6c85499-33c1-4072-d3f6-ceaee76b890b'],
        ['name' => 'Управляющая компания ЭКО', 'phone' => '+7 (843) 567-29-09', 'note' => '', 'logo' => '', 'uid' => '60f9033d-dc1e-4fc9-a848-bada7ebc55b5'],
        ['name' => 'НТЦ ЭКОПРО', 'phone' => '+7 (843) 567-29-11', 'note' => '', 'logo' => '', 'uid' => '7625ac81-8551-402d-dcb7-667449001a63'],
        ['name' => 'Орловский полигон', 'phone' => '+7 (843) 204-35-33', 'note' => '', 'logo' => '', 'uid' => 'cdd18b69-82a0-4f7a-cbb6-eb8776fbf24d'],
        ['name' => 'ПЭК Йошкар-Ола', 'phone' => array('+7 (8362) 55-15-07', '+7 (8362) 45-06-07'), 'note' => '', 'logo' => 'pek.png', 'uid' => '084a710f-6bec-4755-cd04-c5e44b292c9f'],
        ['name' => 'ПЭК Наб. Челны', 'phone' => '+7 (8552) 71-77-22', 'note' => '', 'logo' => 'pek.png', 'uid' => '3986cad5-c422-428c-c78f-41d935fb322f'],
    );



    function __construct()
    {

         $this->importDb();

    }

    public function importDb()
    {
        $dissmised = array();
        $in_decret = array();
        date_default_timezone_set("Europe/Moscow");
        $mysqli2 = new mysqli(self::HOST_SOGO, self::USER_SOGO, self::PASSWORD_SOGO, self::BD_SOGO);
        $mysqli2->query("SET NAMES utf8mb");
        $mysqli = new mysqli(self::HOST_172, self::USER_172, SELF::PASSWORD_172, SELF::BD_172);
        if ($result = $mysqli->query("SELECT * FROM addressbook ad INNER JOIN user_add_info ui ON ad.uid = ui.uid")) {
            while ($data = $result->fetch_assoc()) {
                $uri = $data['uid'].".vcf";
                $result2 = $mysqli2->query("SELECT c_creationdate FROM ".self::TABLE_MAIN_SOGO." WHERE c_name='".$uri."'");
                if (empty($result2->fetch_array())) {
                //новый пользователь
                   $card = $this->makeVcard($data);
                   if ($this->userActive($data)) {
                       $mysqli2->query("INSERT INTO ".self::TABLE_MAIN_SOGO." (`c_name` ,`c_content`,`c_creationdate`, `c_lastmodified`, `c_version`)
                    VALUES ('".$uri."', '" . $card . "', '" . time() . "', '" . time() . "', '0')") OR die(mysqli_error($mysqli2));

                       $mysqli2->query("INSERT INTO ".self::TABLE_QUICK_SOGO." (c_name ,c_givenname, c_cn, c_sn, c_o, c_ou, c_component) 
                    VALUES ('".$uri."', '".$data['firstname']."', '".$data['lastname'] . " " . $data['firstname'] . " " . $data['firstname2']."',
                     '".$data['lastname']."', '".$data['organization']."', '".$data['jobtitle']."', 'vcard')") OR die(mysqli_error($mysqli2));
                   }
                } else {
                 //пользователь существует
                    $card = $this->makeVcard($data);
                    if ($this->userActive($data)) {
                        $mysqli2->query("UPDATE ".self::TABLE_MAIN_SOGO." SET `c_content` = '".$card."', `c_lastmodified` = ".time().", `c_version` = `c_version` + 1, `c_deleted` = null WHERE `c_name` = '". $uri. "'") OR die(mysqli_error($mysqli2));

                        $mysqli2->query("REPLACE INTO ".self::TABLE_QUICK_SOGO." (c_name ,c_givenname, c_cn, c_sn, c_o, c_ou, c_component) 
                    VALUES ('".$uri."', '".$data['firstname']."', '".$data['lastname'] . " " . $data['firstname'] . " " . $data['firstname2']."',
                     '".$data['lastname']."', '".$data['organization']."', '".$data['jobtitle']."', 'vcard')") OR die(mysqli_error($mysqli2));

                    } else {
                        $mysqli2->query("UPDATE ".self::TABLE_MAIN_SOGO." SET `c_content` = '".$card."', `c_lastmodified` = ".time().", `c_version` = `c_version` + 1, `c_deleted` = 1 WHERE `c_name` = '". $uri. "'") OR die(mysqli_error($mysqli2));
                        $mysqli2->query("DELETE FROM ".self::TABLE_QUICK_SOGO." WHERE `c_name` = '". $uri. "'") OR die(mysqli_error($mysqli2));
                    }
                }



                $result3 = $mysqli2->query("SELECT c_creationdate FROM ".self::TABLE_MAIN_SOGO_DISSMISED." WHERE c_name='".$uri."'");
                if (empty($result3->fetch_array())) {
                    $card = $this->makeVcard($data);
                    if (!$this->userActive($data)) {
                        $mysqli2->query("INSERT INTO ".self::TABLE_MAIN_SOGO_DISSMISED." (`c_name` ,`c_content`,`c_creationdate`, `c_lastmodified`, `c_version`)
                    VALUES ('".$uri."', '" . $card . "', '" . time() . "', '" . time() . "', '0')") OR die(mysqli_error($mysqli2));

                        $mysqli2->query("INSERT INTO ".self::TABLE_QUICK_SOGO_DISSMISED." (c_name ,c_givenname, c_cn, c_sn, c_o, c_ou, c_component) 
                    VALUES ('".$uri."', '".$data['firstname']."', '".$data['lastname'] . " " . $data['firstname'] . " " . $data['firstname2']."',
                     '".$data['lastname']."', '".$data['organization']."', '".$data['jobtitle']."', 'vcard')") OR die(mysqli_error($mysqli2));
                    }
                    if ($this->inDecret($data)) {
                        $in_decret[] = $data['uid'];
                    }
                    if ($this->dissmised($data)) {
                        $dissmised[] = $data['uid'];
                    }
                } else {
                    $card = $this->makeVcard($data);
                    if (!$this->userActive($data)) {
                        $mysqli2->query("UPDATE ".self::TABLE_MAIN_SOGO_DISSMISED." SET `c_content` = '".$card."', `c_lastmodified` = ".time().", `c_version` = `c_version` + 1, `c_deleted` = null WHERE `c_name` = '". $uri. "'") OR die(mysqli_error($mysqli2));

                        $mysqli2->query("REPLACE INTO ".self::TABLE_QUICK_SOGO_DISSMISED." (c_name ,c_givenname, c_cn, c_sn, c_o, c_ou, c_component) 
                    VALUES ('".$uri."', '".$data['firstname']."', '".$data['lastname'] . " " . $data['firstname'] . " " . $data['firstname2']."',
                     '".$data['lastname']."', '".$data['organization']."', '".$data['jobtitle']."', 'vcard')") OR die(mysqli_error($mysqli2));
                        if ($this->inDecret($data)) {
                            $in_decret[] = $data['uid'];
                        }
                        if ($this->dissmised($data)) {
                            $dissmised[] = $data['uid'];
                        }
                    } else {
                        $mysqli2->query("UPDATE ".self::TABLE_MAIN_SOGO_DISSMISED." SET `c_content` = '".$card."', `c_lastmodified` = ".time().", `c_version` = `c_version` + 1, `c_deleted` = 1 WHERE `c_name` = '". $uri. "'") OR die(mysqli_error($mysqli2));
                        $mysqli2->query("DELETE FROM ".self::TABLE_QUICK_SOGO_DISSMISED." WHERE `c_name` = '". $uri. "'") OR die(mysqli_error($mysqli2));
                    }

                }



               //@todo переписать корп выгрузку
                // $this->addCorpNumbers(1);
                $this->makeGroups($dissmised, $in_decret);
                $this->delFakedGroups();
            }
        }
        echo "ok";
    }

    /**
     * Парсим телефоны
     * @param $phones_data
     * @param $type
     * @return string
     */
    private function getPhones($phones_data, $type)
    {
        $phones = '';
        switch ($type) {
            case self::CELL_PHONES:
                $phones_title = "\nTEL;TYPE=CELL:";
                break;
            case self::WORK_PHONES:
                $phones_title = "\nTEL;TYPE=WORK:";
                break;
            case self::HOME_PHONES:
                $phones_title = "\nTEL;TYPE=HOME:";
                break;

            default:
                $phones_title = '';
                break;
        }
        $phones_array = explode('|', $phones_data);
        foreach ($phones_array as $phone) {
            if (!preg_match('/(.*273\-77\-22$)|(.*567\-29\-09$)/', $phone)) {
                $phones .= $phones_title . $phone;
            }
        }
        return $phones;
    }

    /**
     * Парсим почтовики
     * @param $emails_data
     * @param $type
     * @return string
     */
    private function getEmails($emails_data, $type)
    {
        $emails = '';
        switch ($type) {
            case self::WORK_EMAILS:
                $emails = "\nEMAIL;TYPE=WORK:";
                break;
            case self::PRIVATE_EMAILS:
                $emails = "\nEMAIL;TYPE=HOME:";
                break;

            default:
                $emails = '';
                break;
        }
        $str = str_replace('|', ';', $emails_data);
        $emails .= $str;
        return $emails;
    }

    /**
     * Парсим адреса
     * @param $addresses_data
     * @param $type
     * @return string
     */
    private function getAddresses($addresses_data, $type)
    {
        $addr = '';
        switch ($type) {
            case self::WORK_ADDRESSES:
                $addr = "\nADR;TYPE=WORK:";
                break;
            case self::HOME_ADDRESSES:
                $addr = "\nADR;TYPE=HOME:";
                break;

            default:
                $addr = '';
                break;
        }
        $str = str_replace('|', ';', $addresses_data);
        $addr .= $str;
        return $addr;
    }

    /**
     * Проверяем активный ли пользователь (не уволен ли или не в декрете)
     * @param $data
     * @return bool
     */
    private function userActive($data) {
        $decret = FALSE;
        if (!empty($data['decret_start']) AND !empty($data['decret_end'])) {
            $start = date_timestamp_get(date_create_from_format('Y-m-d', $data['decret_start']));
            $end = date_timestamp_get(date_create_from_format('Y-m-d', $data['decret_end']));
            if (time() > $start AND time() < $end) {
                $decret = TRUE;
            }
        }

        if ((empty($data['dismissal_date']) OR '0000-00-00' == $data['dismissal_date']) AND !$decret) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * В декрете ли пользователь
     * @param $data
     * @return bool
     */
    private function inDecret($data) {
        $decret = FALSE;
        if (!empty($data['decret_start']) AND !empty($data['decret_end'])) {
            $start = date_timestamp_get(date_create_from_format('Y-m-d', $data['decret_start']));
            $end = date_timestamp_get(date_create_from_format('Y-m-d', $data['decret_end']));
            if (time() > $start AND time() < $end) {
                $decret = TRUE;
            }
        }
        return $decret;
    }

    /**
     * Уволен или нет пользователь
     * @param $data
     * @return bool
     */
    private function dissmised($data) {
        if ((empty($data['dismissal_date']) OR '0000-00-00' == $data['dismissal_date'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Готовим карточку
     * @param $data
     * @return string
     */
    private function makeVcard($data) {
        $vcard = "BEGIN:VCARD\nVERSION:3.0";
        $vcard .= "\nUID:" . $data['uid'];
        $vcard .= "\nPRODID:-//Apple Inc.//Mac OS X 10.10.5//EN \nREV:" . date("Y-m-d\TH:i:sP");
        $vcard .= "\nN:" . $data['lastname'] . "\;" . $data['firstname'] . "\;" . $data['firstname2'] . "\;\;";
        $vcard .= "\nFN:" . $data['lastname'] . " " . $data['firstname'] . " " . $data['firstname2'];
        $vcard .= "\nORG:" . $data['organization'];
        $vcard .= "\nTITLE:" . $data['jobtitle'] . " (" . $data['department'] . ")";
        $vcard .= "\nNOTE:" . $data['jobtitle'] . " (" . $data['department'] . ")";
        if (!empty($data['work_phones']) AND !$data['dismissal_date']) {
            $vcard .= $this->getPhones($data['work_phones'], self::WORK_PHONES);
        }
        if (!empty($data['cell_phones'])) {
            $vcard .= $this->getPhones($data['cell_phones'], self::CELL_PHONES);
        }
        if (!empty($data['private_phones'])) {
            $vcard .= $this->getPhones($data['private_phones'], self::HOME_PHONES);
        }

        if (!empty($data['work_emails']) AND !$data['dismissal_date']) {
            $vcard .= $this->getEmails($data['work_emails'], self::WORK_EMAILS);
        }

        if (!empty($data['private_emails'])) {
            $vcard .= $this->getEmails($data['private_emails'], self::PRIVATE_EMAILS);
        }

        if (!empty($data['work_addresses'])) {
            $vcard .= $this->getAddresses($data['work_addresses'], self::WORK_ADDRESSES);
        }

        if (!empty($data['home_addresses'])) {
            $vcard.= $this->getAddresses($data['home_addresses'], self::HOME_ADDRESSES);
        }

        $vcard .= "\nBDAY:" . $data['birthdate'];
        $photo = base64_encode(@file_get_contents(self::PHOTO_PATH . $data['uid'] . '.jpg'));
        $vcard .= "\nPHOTO;ENCODING=b;TYPE=JPEG:" . $photo;
        $vcard .= "\nEND:VCARD";
        return $vcard;
    }

    //@todo perepisat
    private function addCorpNumbers($book) {
        foreach ($this->corpData as $data) {
            $gen_uid = $data['uid'];
            $vcard = "BEGIN:VCARD\nVERSION:4.0";
            $vcard .= "\nUID:" . $gen_uid;
            $vcard .= "\nPRODID:-//Apple Inc.//Mac OS X 10.10.5//EN \nREV:" . date("Y-m-d\TH:i:sP");
            $vcard .= "\nN:" . $data['name'] . "\;\;\;\;";
            $vcard .= "\nFN:" . $data['name'];
            $vcard .= "\nNOTE:" . $data['note'];
            if (is_array($data['phone'])) {
                foreach ($data['phone'] as $cphone) {
                    $vcard .= "\\nTEL;TYPE=WORK:" . $cphone;
                }
            } else {
                $vcard .= "\\nTEL;TYPE=WORK:" . $data['phone'];
            }
            if (!empty($data['logo'])) {
                $photo = base64_encode(file_get_contents('/home/eakhmetov/carddav/photo/' . $data['logo']));
                $vcard .= "\nPHOTO:data:image/png;base64," . $photo;
            }
            $vcard .= "\nEND:VCARD";

            $mysqli2 = new mysqli($this->host_carddav, $this->db_user_carddav, $this->db_pass_carddav, $this->db_name_carddav);
            $mysqli2->query("SET NAMES utf8");
            $uri = $gen_uid . ".vcf";
            $mysqli2->query("DELETE FROM oc_cards WHERE uri = '" . $uri . "' AND addressbookid = 1");
            $mysqli2->query("INSERT INTO oc_cards (`addressbookid`  ,`carddata`,`uri` ,`lastmodified`) 
                VALUES (".$book.", '" . $vcard . "', '" . $uri . "', " . time() . ")");
            $mysqli2->close();
        }
    }

    /**
     * Делаем группы
     * @param $dissmised
     * @param $in_decret
     */
    private function makeGroups($dissmised, $in_decret) {
        $vcard = "BEGIN:VCARD\nVERSION:3.0";
        $vcard .= "\nUID:" . self::DISMISSED_GROUP;
        $vcard .= "\nPRODID:-//Apple Inc.//Mac OS X 10.10.5//EN \nREV:" . date("Y-m-d\TH:i:sP");
        $vcard .= "\nN:Уволенные";
        $vcard .= "\nFN:Уволенные";
        $vcard .= "\nX-ADDRESSBOOKSERVER-KIND:group";
        if(!empty($dissmised)) {
            foreach ($dissmised as $user_uid) {
                $vcard .= "\nX-ADDRESSBOOKSERVER-MEMBER:urn:uuid:".$user_uid;
            }
        }
        $vcard .= "\nEND:VCARD";

        $mysqli2 = new mysqli(self::HOST_SOGO, self::USER_SOGO, self::PASSWORD_SOGO, self::BD_SOGO);
        $mysqli2->query("SET NAMES utf8mb");

        $mysqli2->query("UPDATE ".self::TABLE_MAIN_SOGO_DISSMISED." SET `c_content` = '".$vcard."', `c_lastmodified` = ".time().", `c_version` = `c_version` + 1, `c_deleted` = null 
        WHERE `c_name` = '". self::DISMISSED_GROUP. ".vcf'") OR die(mysqli_error($mysqli2));

        $mysqli2->query("REPLACE INTO ".self::TABLE_QUICK_SOGO_DISSMISED." (c_name ,c_givenname, c_cn, c_component) 
                    VALUES ('".self::DISMISSED_GROUP.".vcf', 'Уволенные', 'Уволенные', 'vcard')") OR die(mysqli_error($mysqli2));

        $vcard = "BEGIN:VCARD\nVERSION:3.0";
        $vcard .= "\nUID:" . self::DECRET_GROUP;
        $vcard .= "\nPRODID:-//Apple Inc.//Mac OS X 10.10.5//EN \nREV:" . date("Y-m-d\TH:i:sP");
        $vcard .= "\nN:Декрет";
        $vcard .= "\nFN:Декрет";
        $vcard .= "\nX-ADDRESSBOOKSERVER-KIND:group";
        if(!empty($in_decret)) {
            foreach ($in_decret as $user_uid) {
                $vcard .= "\nX-ADDRESSBOOKSERVER-MEMBER:urn:uuid:".$user_uid;
            }
        }
        $vcard .= "\nEND:VCARD";

        $mysqli2 = new mysqli(self::HOST_SOGO, self::USER_SOGO, self::PASSWORD_SOGO, self::BD_SOGO);
        $mysqli2->query("SET NAMES utf8mb");

        $mysqli2->query("UPDATE ".self::TABLE_MAIN_SOGO_DISSMISED." SET `c_content` = '".$vcard."', `c_lastmodified` = ".time().", `c_version` = `c_version` + 1, `c_deleted` = null 
        WHERE `c_name` = '". self::DECRET_GROUP. ".vcf'") OR die(mysqli_error($mysqli2));

        $mysqli2->query("REPLACE INTO ".self::TABLE_QUICK_SOGO_DISSMISED." (c_name ,c_givenname, c_cn, c_component) 
                    VALUES ('".self::DECRET_GROUP.".vcf', 'Декрет', 'Декрет', 'vcard')") OR die(mysqli_error($mysqli2));

    }

    /**
     * Удаляем случайно созданные группы
     */
    private function delFakedGroups()
    {
        $need_groups = [self::DISMISSED_GROUP . '.vcf', self::DECRET_GROUP . '.vcf'];
        echo "SELECT sm.c_name FROM " . self::TABLE_MAIN_SOGO_DISSMISED . " sm JOIN " . self::TABLE_QUICK_SOGO_DISSMISED . " sq ON sq.c_name = sm.c_name 
         WHERE sq.c_sn = NULL AND sm.c_name NOT IN (" . implode(',', $need_groups) . ")";
        $mysqli2 = new mysqli(self::HOST_SOGO, self::USER_SOGO, self::PASSWORD_SOGO, self::BD_SOGO);
        $mysqli2->query("SET NAMES utf8mb");
        $result4 = $mysqli2->query("SELECT sm.c_name FROM " . self::TABLE_MAIN_SOGO_DISSMISED . " sm JOIN " . self::TABLE_QUICK_SOGO_DISSMISED . " sq ON sq.c_name = sm.c_name 
         WHERE sq.c_sn = NULL AND sm.c_name NOT IN (" . implode(',', $need_groups) . ")") OR die(mysqli_error($mysqli2));
        if (!empty($result4->fetch_array())) {
            while ($data = $result4->fetch_assoc()) {
                $mysqli2->query("UPDATE " . self::TABLE_MAIN_SOGO_DISSMISED . " SET `c_lastmodified` = " . time() . ", `c_version` = `c_version` + 1, `c_deleted` = 1 WHERE `c_name` = '" . $data['c_name'] . "'") OR die(mysqli_error($mysqli2));
                $mysqli2->query("DELETE FROM " . self::TABLE_QUICK_SOGO_DISSMISED . " WHERE `c_name` = '" . $data['c_name'] . "'") OR die(mysqli_error($mysqli2));
            }
        }

        $result5 = $mysqli2->query("SELECT sm.c_name FROM " . self::TABLE_MAIN_SOGO . " sm JOIN " . self::TABLE_QUICK_SOGO . " sq ON sq.c_name = sm.c_name 
         WHERE sq.s_cn = NULL") OR die(mysqli_error($mysqli2));
        if (!empty($result4->fetch_array())) {
            while ($data = $result5->fetch_assoc()) {
                $mysqli2->query("UPDATE " . self::TABLE_MAIN_SOGO . " SET `c_lastmodified` = " . time() . ", `c_version` = `c_version` + 1, `c_deleted` = 1 WHERE `c_name` = '" . $data['c_name'] . "'") OR die(mysqli_error($mysqli2));
                $mysqli2->query("DELETE FROM " . self::TABLE_QUICK_SOGO . " WHERE `c_name` = '" . $data['c_name'] . "'") OR die(mysqli_error($mysqli2));
            }
        }
    }
}