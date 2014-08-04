<?php
class GameHelper extends AppHelper {

    /**
     * Задает css-класс каждой ячейке игрового поля.
     *
     * @return array
     */
    public function getClasses() {
        $class = array();
        for ($i=0; $i<81; $i++) {
            $column = $i % 9;
            $class[$i] = '';
            if ($column >= 3 && $column <= 5 XOR $i >= 27 && $i <= 53) {
                $class[$i] .= 'darkquad';
            } else {
                $class[$i] .= 'lightquad';
            }
            if ($column == 2) {
                $class[$i] .= ' right';
            }
            if ($column == 6) {
                $class[$i] .= ' left';
            }
            if ($i >= 18 && $i <= 26) {
                $class[$i] .= ' bottom';
            }
            if ($i >= 54 && $i <= 62) {
                $class[$i] .= ' top';
            }
        }
        return $class;
    }

    /**
     * Получает массив по заданной строке.
     * Убирает первый ноль в значениях.
     *
     * @param   string $string
     * @return  array
     */
    public function getArrayFromString($table = '') {
        $result = array();
        $n = strlen($table);
        if (0 === $n % 2) {
            for ($i=0; $i<$n; $i+=2) {
                $result[] = ($table[$i] != '0' ? $table[$i] : '').$table[$i+1];
            }
        }
        return $result;
    }
}