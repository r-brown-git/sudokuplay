<?php
class DateTimeHelper extends AppHelper {

    static private $_ruMonShort = array(
        '00' => 'нул',
        '01' => 'янв',
        '02' => 'фев',
        '03' => 'мар',
        '04' => 'апр',
        '05' => 'мая',
        '06' => 'июн',
        '07' => 'июл',
        '08' => 'авг',
        '09' => 'сен',
        '10' => 'окт',
        '11' => 'ноя',
        '12' => 'дек',
    );

    static private $_ruMon = array(
        '00' => 'нул',
        '01' => 'январь',
        '02' => 'февраль',
        '03' => 'март',
        '04' => 'апрель',
        '05' => 'май',
        '06' => 'июнь',
        '07' => 'июль',
        '08' => 'август',
        '09' => 'сентябрь',
        '10' => 'октябрь',
        '11' => 'ноябрь',
        '12' => 'декабрь',
    );

    static private $_ruMonRp = array(
        '00' => 'нул',
        '01' => 'января',
        '02' => 'февраля',
        '03' => 'марта',
        '04' => 'апреля',
        '05' => 'мая',
        '06' => 'июня',
        '07' => 'июля',
        '08' => 'августа',
        '09' => 'сентября',
        '10' => 'октября',
        '11' => 'ноября',
        '12' => 'декабря',
    );

    public function ruDatePluralize($count, $unit) {
        $mod = $count % 10;
        $div = floor($count / 10);
        $units = array(
            'y' => array('лет', 'года', 'год'),
            'm' => array('месяцев', 'месяца', 'месяц'),
            'd' => array('дней', 'дня', 'день'),
        );
        if ($div == 1) {
            $result = $units[$unit][0];
        } else if ($mod == 0 || $mod >= 5 && $mod <= 9) {
            $result = $units[$unit][0];
        } else if ($mod == 1) {
            $result = $units[$unit][2];
        } else if ($mod >= 2 && $mod <=4) {
            $result = $units[$unit][1];
        }
        return $count . ' ' . $result;
    }

    // 1 июн 2013 в 20:38:02;
    public function formatGameList($date) {
        $ts = strtotime($date);
        if (!$ts) {
            return '';
        }
        $result = date('j', $ts) . ' ' . self::$_ruMonShort[date('m', $ts)];
        if (date('Y') != date('Y', $ts)) {
            $result .= ' '.date('Y', $ts);
        }
        $result .= ' в '.date('H:i:s', $ts);
        return $result;
    }

    public function formatBirthday($date) {
        $ts = strtotime($date);
        if (!$ts) {
            return '';
        }
        $result = date('j', $ts) . ' ' . self::$_ruMonRp[date('m', $ts)] . ' ' . date('Y', $ts);
        return $result;
    }

    public function formatTimeFrom($date) {
        $ts = strtotime($date);
        if (!$ts) {
            return '';
        }
        $now = new DateTime();
        $from = new DateTime($date);
        $interval = $now->diff($from);
        $result = '';
        if ($interval->y > 1) {
            $result .= $this->ruDatePluralize($interval->y, 'y') . ', ';
        }
        if ($interval->m > 1) {
            $result .= $this->ruDatePluralize($interval->m, 'm') . ', ';
        }
        if ($interval->d > 1) {
            $result .= $this->ruDatePluralize($interval->d, 'd');
        }

        return $result;
    }
}