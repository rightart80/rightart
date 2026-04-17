<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use af\Toolkit;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AfSlider
{
    public function assignParamsForNumericSliders(&$params)
    {
        foreach ($params['numeric_slider_values'] as $first_char => $grouped_values) {
            foreach ($grouped_values as $id_group => $values) {
                $f_key = $first_char . ($id_group ?: '');
                if (isset($params['sliders'][$f_key])) {
                    $slider = $params['sliders'][$f_key] + ['numeric_values' => $values];
                    if (!is_array($slider['numeric_values'])) {
                        $slider['numeric_values'] = array_combine(
                            $params['available_options'][$first_char][$id_group],
                            explode(',', $slider['numeric_values'])
                        );
                    }
                    if ($slider['is_triggered'] = $this->isTriggered($slider)) {
                        foreach ($slider['numeric_values'] as $id => $number) {
                            $possible_range = Toolkit::defineRange($number, false, Toolkit::$sep['all']['range']);
                            if ($possible_range[1] >= $slider['from'] && $possible_range[0] <= $slider['to']) {
                                $params['filters'][$first_char][$id_group][$id] = $id;
                            }
                        }
                        if (empty($params['filters'][$first_char][$id_group])) {
                            // no numeric values within selected range
                            $params['filters'][$first_char][$id_group]['_'] = '_';
                        }
                    }
                    $params['sliders'][$f_key] = $slider;
                }
            }
        }
    }

    public function updateData(&$params, $count_data, $all_matches)
    {
        foreach ($params['sliders'] as $f_key => $slider) {
            $first_char = $f_key[0];
            if (isset($params['ranges'][$f_key]['min'])) { // defined in updRangeMinMax
                $slider = $params['ranges'][$f_key] + $slider + ['upd' => 1];
            } elseif (!empty($slider['numeric_values'])) {
                if (!$params['ajax']) {
                    $id_group = Tools::substr($f_key, 1);
                    foreach (array_keys($slider['numeric_values']) as $id) {
                        if (!isset($all_matches[$first_char][$id])) {
                            unset($slider['numeric_values'][$id]);
                            unset($params['available_options'][$first_char][$id_group][$id]);
                            unset($params['numeric_slider_values'][$first_char][$id_group][$id]);
                        }
                    }
                }
                if (empty($slider['is_triggered'])) {
                    $slider['numeric_values'] = array_intersect_key(
                        $slider['numeric_values'],
                        array_filter($count_data[$first_char])
                    );
                    $slider['upd'] = 1;
                }
                if (!isset($slider['min']) || !empty($slider['upd'])) {
                    $slider = $this->getMinMax($slider['numeric_values']) + $slider;
                }
            }
            if (!empty($slider['hst']) && !empty($count_data[$first_char])) {
                $slider['histogram'] = $this->prepareHistogram($slider, $count_data[$first_char]);
            }
            if ($params['ajax'] && (!empty($slider['upd']) || isset($slider['histogram']))) {
                $ajax_slider_data = array_flip(['upd', 'min', 'max', 'histogram']);
                $params['ajax_data']['sliders'][$f_key] = array_intersect_key($slider, $ajax_slider_data);
            }
            $params['sliders'][$f_key] = $slider;
        }
    }

    public function getMinMax($numeric_values, $custom_range_sep = false)
    {
        $ret = ['min' => 0, 'max' => 0];
        if ($numeric_values) {
            $range_sep = $custom_range_sep ?: Toolkit::$sep['all']['range'];
            $numeric_values = explode($range_sep, implode($range_sep, $numeric_values));
            $ret['min'] = min($numeric_values);
            $ret['max'] = max($numeric_values);
        }

        return $ret;
    }

    public function prepareHistogram($data, $matches, $bin_num = 20)
    {
        if (!isset($data['min']) || !isset($data['max']) || $data['min'] == $data['max']) {
            return [];
        }
        $bins = array_fill_keys(range(0, $bin_num - 1), 0);
        $step = abs($data['max'] - $data['min']) / $bin_num;
        if (!isset($data['numeric_values'])) {
            foreach ($matches as $value => $count) {
                $b_key = $this->getBinKey($value, $data['min'], $step);
                if (isset($bins[$b_key])) {
                    $bins[$b_key] += $count;
                }
            }
        } else {
            foreach ($data['numeric_values'] as $id => $number) {
                if (!empty($matches[$id])) {
                    $r = Toolkit::defineRange($number, false, Toolkit::$sep['all']['range']);
                    $b_key_from = $this->getBinKey($r[0], $data['min'], $step);
                    $b_key_to = $r[1] == $r[0] ? $b_key_from : $this->getBinKey($r[1], $data['min'], $step);
                    foreach (range($b_key_from, $b_key_to) as $b_key) {
                        if (isset($bins[$b_key])) {
                            $bins[$b_key] += $matches[$id];
                        }
                    }
                }
            }
        }
        $max = max($bins);
        if ($max > 0) {
            $min = min(array_filter($bins));
            $log_scale = $min && ($max / $min) > 50; // normalize bin heights if smallest is < 2%
            foreach ($bins as $b_key => $count) {
                if ($log_scale) {
                    $ratio = log($count + 1) / log($max + 1); // +1 to avoid log(0) and log(1)=0 if $count=1
                } else {
                    $ratio = $count / $max;
                }
                $bins[$b_key] = ceil($ratio * 100);
            }
        }

        return $bins;
    }

    public function getBinKey($value, $min_value, $step)
    {
        $b_key = ($value - $min_value) / $step;
        $b_key_int = (int) $b_key;

        return $b_key && $b_key == $b_key_int ? $b_key - 1 : $b_key_int; // edge values go to lower bin
    }

    public function updRangeMinMax(&$data, $value)
    {
        if (!isset($data['max']) || $value > $data['max']) {
            $data['max'] = $value;
        }
        if (!isset($data['min']) || $value < $data['min']) {
            $data['min'] = $value;
        }
    }

    public function isTriggered($slider_data)
    {
        $values = $this->fillValues($slider_data);

        return $values['from'] > $values['min'] || $values['to'] < $values['max'];
    }

    public function fillValues($slider_data)
    {
        $min = isset($slider_data['min']) ? $slider_data['min'] : 0;
        $max = isset($slider_data['max']) ? $slider_data['max'] : 10000000000;
        $from = isset($slider_data['from']) && $slider_data['from'] > $min ? $slider_data['from'] : $min;
        $to = isset($slider_data['to']) && $slider_data['to'] < $max ? $slider_data['to'] : $max;

        return ['min' => $min, 'max' => $max, 'from' => $from, 'to' => $to];
    }

    public function setExtensions(&$filter, $is_modern)
    {
        if ($filter['first_char'] == 'p') {
            $this->context = Context::getContext();
            $currency = $this->context->currency;
            if ($is_modern) {
                $this->setCurrencyExtensions($currency);
            }
            $filter['prefix'] = $currency->prefix;
            $filter['suffix'] = $currency->suffix;
        } else {
            $filter['prefix'] = ltrim($filter['slider_prefix'] . ' ');
            $filter['suffix'] = rtrim(' ' . $filter['slider_suffix']);
        }
    }

    public function setCurrencyExtensions(&$currency)
    {
        if (!$currency->prefix && !$currency->suffix) {
            $format = $currency->format;
            if (!$format && method_exists($this->context->controller, 'getContainer')) {
                $format = $this->context->controller->getContainer()->
                    get(Tools::SERVICE_LOCALE_REPOSITORY)->
                    getLocale($this->context->language->getLocale())->
                    getPriceSpecification($currency->iso_code)->toArray()['positivePattern'];
            }
            if (Tools::substr($format, 0, 1) === '¤') {
                $currency->prefix = $currency->sign;
                if (urlencode(Tools::substr($format, 1, 2)) === '%C2%A0') {
                    $currency->prefix .= ' ';
                }
            } else {
                $currency->suffix = $currency->sign;
                if (urlencode(Tools::substr($format, -2, -1)) === '%C2%A0') {
                    $currency->suffix = ' ' . $currency->suffix;
                }
            }
        }
    }
}
