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

function areaFields()
{
    return [
        'province_id',
        'district_id',
        'subdistrict_id',
        'village_id',
    ];
}


/* function uploadFile($model, $field, $uploadedFile, $name = null)
{
    $filename          = $model->id .'.'. $uploadedFile->extension;
    $directory         = Yii::getAlias(Yii::$app->params['fileStorage'].$model->tableName().'/'.$field);
    $directory_resized = Yii::getAlias(Yii::$app->params['fileStorage_resized'].$model->tableName().'/'.$field);

    if (!file_exists($directory)) mkdir($directory, 0777, true);
    if (!file_exists($directory_resized)) mkdir($directory_resized, 0777, true);

    if ($uploadedFile->saveAs($directory.'/'.$filename)) {
        if ($uploadedFile->extension == 'jpg' || $uploadedFile->extension == 'JPG' || $uploadedFile->extension == 'jpeg' || $uploadedFile->extension == 'JPEG' || $uploadedFile->extension == 'png' || $uploadedFile->extension == 'PNG') {
            // yii\imagine\Image::getImagine()->load(file_get_contents($directory.'/'.$filename))->save($directory_resized.'/'.$filename, ['quality' => 10]);
            yii\imagine\Image::getImagine()
            ->open($directory.'/'.$filename)
            ->thumbnail(new Imagine\Image\Box(600, 600))
            ->save($directory_resized.'/'.$filename, ['quality' => 90]);
        }
        $model->$field = $filename;
        if (isset($model->uploaded_name)) $model->uploaded_name   = $uploadedFile->name;
        if ($name && isset($model->uploaded_name)) $model->uploaded_name = $name;
        if ($model->save()) return true;
        else dd($model->errors);
    }
    return false;
} */

function downloadFile($model, $field, $filename = null, $resized = true, $forceDownload = false, $product_id = 1)
{
    if ($product_id === 2) {
        if ($model->$field) {
            $filepath  = Yii::getAlias(Yii::$app->params['fileStorage_resized'] .'../../cpns.appskep.id/uploads_resized/'. $model->tableName().'/'.$field.'/'.$model->$field);
            if (!$resized || !file_exists($filepath)) $filepath  = Yii::getAlias(Yii::$app->params['fileStorage'] .'../../cpns.appskep.id/uploads/'. $model->tableName().'/'.$field.'/'.$model->$field);
            $array     = explode('.', $model->$field);
            $extension = end($array);
            $filename  = ($filename ?? ($model->name ?? $model->$field)) . '.' . $extension;
            if (file_exists($filepath)) {
                if ($forceDownload) return Yii::$app->response->sendFile($filepath, $filename);
                return Yii::$app->response->sendFile($filepath, $filename, ['inline' => true]);
            }
        }
    } else {
        if ($model->$field) {
            $filepath  = Yii::getAlias(Yii::$app->params['fileStorage_resized'] . $model->tableName().'/'.$field.'/'.$model->$field);
            if (!$resized || !file_exists($filepath)) $filepath  = Yii::getAlias(Yii::$app->params['fileStorage'] . $model->tableName().'/'.$field.'/'.$model->$field);
            $array     = explode('.', $model->$field);
            $extension = end($array);
            $filename  = ($filename ?? ($model->name ?? $model->$field)) . '.' . $extension;
            if (file_exists($filepath)) {
                if ($forceDownload) return Yii::$app->response->sendFile($filepath, $filename);
                return Yii::$app->response->sendFile($filepath, $filename, ['inline' => true]);
            }
        }
    }
    return Yii::$app->response->redirect(Yii::$app->request->referrer);
}



function uploadFileV2($model, $field, $uploadedFile)
{
    $filepath = Yii::$app->params['uploadRoot'].$model->tableName().'/'.$field.'/'.$model->id .'.'. $uploadedFile->extension;
    if (Yii::$app->awsS3->put($filepath, file_get_contents($uploadedFile->tempName))) {
        $model->$field = $uploadedFile->name;
        if ($model->save()) return true;
        else Yii::$app->session->addFlash('error', \yii\helpers\Json::encode($model->errors));
    }
    return false;
}

function downloadFileV2($model, $field, $filename = null, $resized = true, $forceDownload = false, $product_id = 1)
{
    if ($model->$field) {
        $extension  = pathinfo($model->$field, PATHINFO_EXTENSION);
        $filepath   = Yii::$app->params['uploadRoot'].$model->tableName().'/'.$field.'/'.$model->id.'.'.$extension;
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

function deleteFileV2($filename)
{
    return Yii::$app->awsS3->delete($filename);
}

function downloadFilePresence($model, $field, $filename = null)
{
    if ($model->$field) {
        $extension  = pathinfo($model->$field, PATHINFO_EXTENSION);
        $filepath   = $model->tableName().'/'.$field.'/'.$model->$field;
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

function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {
    for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
        for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
            $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
        return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
    }