<?php
    date_default_timezone_set('Europe/Budapest');

    const CORE = "core/";
    const CONTROLLERS = "controllers/";
    const VIEWS = "views/";
    const MODELS = "models/";
    const API_CONTROLLERS = CONTROLLERS . "api/";

    // --- ADATBÁZIS KONSTANSOK (Troxan DB) ---
    const DB_HOST = "localhost";
    const DB_USER = "troxan_user"; // Ez az új felhasználó, akit létrehoztál a XAMPP-ben!
    const DB_PASS = "TroxanServer123"; // XAMPP-ban alapból üres a jelszó
    const DB_NAME = "troxan_db"; // Az új adatbázisod neve!
    const DB_CHARSET = "utf8mb4"; // Hogy a speciális karakterek és emojik is jók legyenek
    const TROXAN_DEBUG_GAME_STATS = true;

    if (!function_exists('troxan_format_db_datetime')) {
        function troxan_format_db_datetime($value, $format = 'Y-m-d H:i', $emptyValue = 'Never')
        {
            if (empty($value)) {
                return $emptyValue;
            }

            try {
                $date = new DateTime($value, new DateTimeZone('UTC'));
                $date->setTimezone(new DateTimeZone('Europe/Budapest'));
                return $date->format($format);
            } catch (Throwable $e) {
                $timestamp = strtotime($value);
                return $timestamp ? date($format, $timestamp) : $emptyValue;
            }
        }
    }

    if (!function_exists('troxan_get_stat_value')) {
        function troxan_get_stat_value($stats, array $keys, $default = 0)
        {
            if (!is_array($stats)) {
                return $default;
            }

            foreach ($keys as $key) {
                if (!array_key_exists($key, $stats)) {
                    continue;
                }

                $value = $stats[$key];
                if ($value === null) {
                    continue;
                }

                if (is_string($value) && trim($value) === '') {
                    continue;
                }

                return $value;
            }

            return $default;
        }
    }

    if (!function_exists('troxan_get_stat_int')) {
        function troxan_get_stat_int($stats, array $keys, $default = 0)
        {
            return (int) troxan_get_stat_value($stats, $keys, $default);
        }
    }

    if (!function_exists('troxan_get_stat_score')) {
        function troxan_get_stat_score($stats)
        {
            return troxan_get_stat_int($stats, ['score', 'Experience points'], 0);
        }
    }

    if (!function_exists('troxan_get_stat_playtime')) {
        function troxan_get_stat_playtime($stats)
        {
            return (string) troxan_get_stat_value($stats, ['time_played', 'Total playtime', 'play_time', 'playtime'], '0h 0m');
        }
    }

    if (!function_exists('troxan_compare_leaderboard_rows')) {
        function troxan_compare_leaderboard_rows(array $a, array $b)
        {
            $scoreCompare = ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
            if ($scoreCompare !== 0) {
                return $scoreCompare;
            }

            return strcasecmp((string) ($a['username'] ?? ''), (string) ($b['username'] ?? ''));
        }
    }
?>