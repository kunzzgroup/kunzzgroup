<?php
class InputClean {
    public static function clean($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);
                $data[self::clean($key)] = self::clean($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
    
    public static function get($item) {
        if (isset($_POST[$item])) {
            return self::clean($_POST[$item]);
        } else if (isset($_GET[$item])) {
            return self::clean($_GET[$item]);
        }
        return '';
    }
}
