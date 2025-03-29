<?php

use DeviceDetector\DeviceDetector;

/**
 * Debug function
 * d($var);
 */
function d($var)
{
    echo '<pre>';
    yii\helpers\VarDumper::dump($var, 10, true);
    echo '</pre>';
}

/**
 * stringify ActiveRecord errors
 * stringifyModelErrors($errors);
 */
function stringifyModelErrors($errors) {
    $array = [];
    foreach ($errors as $error) {
        foreach ($error as $errorItem) {
            $array[] = $errorItem;
        }
    }
    return implode(' ', $array);
}

/**
 * Parses a template argument to the specified value
 * Template variables are defined using double curly brackets: {{ [a-zA-Z] }}
 * Returns the query back once the instances has been replaced
 * @param string $string
 * @param string $find
 * @param string $replace
 * @return string
 * @throws \Exception
 */
function findReplace($string, $find, $replace)
{
    if (preg_match("/[a-zA-Z\_]+/", $find)) {
        return (string) preg_replace("/\{\{(\s+)?($find)(\s+)?\}\}/", $replace, $string);
    } else {
        throw new \Exception("Find statement must match regex pattern: /[a-zA-Z]+/");
    }
}

function validateDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function months() {
    $months = [];
    for ($i = 1; $i <= 12 ; $i++) { 
        $months[str_pad($i, 2, '0', STR_PAD_LEFT)] = date('F', mktime(0, 0, 0, $i, 10));
    }
    return $months;
}

function monthsRoman($month)
{
    $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return $romans[date($month)-1];
}

function parsePhone($phone)
{
    if ($phone) {
        if (substr($phone,0,1) == '+')  $phone = $phone;
        if (substr($phone,0,2) == '62') $phone = '+'.$phone;
        if (substr($phone,0,1) >= '1')  $phone = '+62'.$phone;
        if (substr($phone,0,1) == '0')  $phone = '+62'.ltrim($phone, '0');
    }
    return $phone;
}

function downloadFilePresence($model, $field, $filename = null)
{
    if ($model->$field) {
        $extension  = pathinfo($model->$field, PATHINFO_EXTENSION);
        // $filepath   = $model->tableName().'/'.$field.'/'.$model->$field;
        $filepath   = 'smkn1lintau/presence/'.$model->$field;
        $filename   = $filename ? $filename.'.'. $extension : $model->$field;
        $fileExists = Yii::$app->awsS3->has($filepath);

        if ($fileExists) {
            $content = Yii::$app->awsS3->read($filepath);
            return Yii::$app->response->sendContentAsFile($content, $filename, [
                'inline'   => true, 
                'mimeType' => Yii::$app->awsS3->getMimetype($filepath),
            ]);
        }
    }
    return false;
}

function parseUserAgent($http_user_agent, $html = false)
{
    try {
        $return = [];

        $dd = new DeviceDetector($http_user_agent);
        $dd->parse();

        if ($dd->isBot()) {
            $botInfo = $dd->getBot();
            $return[]  = $botInfo;
        } else {
            $device     = $dd->getDeviceName();
            $brand      = $dd->getBrandName();
            $model      = $dd->getModel();
            $osInfo     = $dd->getOs();
            $clientInfo = $dd->getClient();
            
            //device
            if ($brand) $return[] = $brand;
            if ($model) $return[] = $model;
            if ($brand || $model) $return[] = ';';

            //os
            if ($osInfo['name']) $return[] = $osInfo['name'];
            if ($osInfo['version']) $return[] = $osInfo['version'];
            if ($osInfo['platform']) $return[] = $osInfo['platform'];
            if ($osInfo['name'] || $osInfo['version'] || $osInfo['platform']) $return[] = ';';

            // browser, feed reader, media player, ...
            if ($clientInfo['name']) $return[] = $clientInfo['name'];
            if ($clientInfo['version']) $return[] = $clientInfo['version'];

            $label_type = '';
            if ($device == 'desktop') $label_type = '-info';
            if ($device == 'tablet') $label_type = '-primary';
            if ($device == 'phablet') $label_type = '-primary';
            if ($device == 'smartphone') $label_type = '-success';
            if ($device) $return[] = '<span class="label label-inline label-light'.$label_type.'">'.$device.'</span>';
        }
        return str_replace(' ;', ';', implode(' ', $return)).($html ? '<br><small>'.$http_user_agent.'</small>' : '');
    } catch (\Throwable $th) {
        
    }
    return $http_user_agent;
}
