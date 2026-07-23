<?php

namespace App\Lib;

class WPPasswordHasher {
    private $itoa64;
    private $iteration_count_log2;
    private $random_state;

    public function __construct($iteration_count_log2 = 8) {
        $this->itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31) {
            $iteration_count_log2 = 8;
        }
        $this->iteration_count_log2 = $iteration_count_log2;
        $this->random_state = microtime() . uniqid(mt_rand(), true);
    }

    private function get_random_bytes($count) {
        $output = '';
        if (function_exists('random_bytes')) {
            return random_bytes($count);
        } elseif (@is_readable('/dev/urandom')) {
            $output = file_get_contents('/dev/urandom', false, null, 0, $count);
        }
        if (strlen($output) < $count) {
            for ($i = 0; $i < $count; $i += 16) {
                $this->random_state = md5(microtime() . $this->random_state);
                $output .= md5($this->random_state, true);
            }
            $output = substr($output, 0, $count);
        }
        return $output;
    }

    private function encode64($input, $count) {
        $output = '';
        $i = 0;
        do {
            $value = ord($input[$i++]);
            $output .= $this->itoa64[$value & 0x3f];
            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }
            $output .= $this->itoa64[($value >> 6) & 0x3f];
            if ($i++ >= $count) break;
            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }
            $output .= $this->itoa64[($value >> 12) & 0x3f];
            if ($i++ >= $count) break;
            $output .= $this->itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);
        return $output;
    }

    private function gensalt_private($input) {
        $output = '$P$';
        $output .= $this->itoa64[min($this->iteration_count_log2 + 5, 30)];
        $output .= $this->encode64($input, 6);
        return $output;
    }

    private function crypt_private($password, $setting) {
        $output = '*0';
        if (substr($setting, 0, 2) === $output) {
            $output = '*1';
        }

        $id = substr($setting, 0, 3);
        if ($id !== '$P$' && $id !== '$H$') {
            return $output;
        }

        $count_log2 = strpos($this->itoa64, $setting[3]);
        if ($count_log2 < 7 || $count_log2 > 30) {
            return $output;
        }

        $count = 1 << $count_log2;
        $salt = substr($setting, 4, 8);
        if (strlen($salt) !== 8) {
            return $output;
        }

        $hash = md5($salt . $password, true);
        do {
            $hash = md5($hash . $password, true);
        } while (--$count);

        $output = substr($setting, 0, 12);
        $output .= $this->encode64($hash, 16);

        return $output;
    }

    public function hashPassword($password) {
        $random = $this->get_random_bytes(6);
        $hash = $this->crypt_private($password, $this->gensalt_private($random));
        return (strlen($hash) === 34) ? $hash : '*';
    }

    public function checkPassword($password, $stored_hash) {
        $hash = $this->crypt_private($password, $stored_hash);
        return hash_equals($stored_hash, $hash);
    }
}
