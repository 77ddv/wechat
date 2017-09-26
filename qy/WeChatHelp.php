<?php
namespace qy;

/**
 *  帮助类
 *
 */
class WeChatHelp
{
    /**
     * [getAccessToken 获取企业微信access_token]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T15:12:41+0800
     * @param    [type]                   $appid     [description]
     * @param    [type]                   $appsecret [description]
     * @return   [type]                              [description]
     */
    public static function getAccessToken($appid, $appsecret)
    {
        $data = self::cache($appid, $appsecret, 'access_token');
        if (empty($data) || $data['expire_time'] < $_SERVER['REQUEST_TIME']) {
            $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$appid}&corpsecret={$appsecret}";
            $res = self::Get($url);
            $access_token = $res['access_token'];
            if ($access_token) {
                self::cache($appid, $appsecret, 'access_token', $access_token);
            }
        } else {
            $access_token = $data['data'];
        }
        return $access_token;
    }
    /**
     * [getCurrentUrl 获取当前url]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T15:12:33+0800
     * @return   [type]                   [description]
     */
    public static function getCurrentUrl()
    {
        $url = 'http://';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $url = 'https://';
        }
        $url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        /*if ($_SERVER['SERVER_PORT'] != '80') {
            $url .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }*/
        // 兼容后面的参数组装
        if (stripos($url, '?') === false) {
            $url .= '?t=' . time();
        }
        return $url;
    }
    /**
     * [createNonceStr 获取随机字符串 默认16位]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T15:08:59+0800
     * @param    integer                  $length [字符串长度]
     * @return   [type]                           [description]
     */
    public static function createNonceStr($length = 16)
    {
        $str     = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max     = strlen($str_pol) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
    /**
     * [Get get请求]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T15:09:18+0800
     * @param    [type]                   $url    [description]
     * @param    boolean                  $decode [是否json_decode 默认返回array false返回json字符串]
     */
    public static function Get($url, $decode = true)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_VERBOSE, 0);
        curl_setopt($oCurl, CURLOPT_HEADER, 0); //不要头

        $sContent = curl_exec($oCurl);
        // $curl_info = curl_getinfo($oCurl);
        // $aStatus = curl_getinfo($oCurl);
        // $sContent = execCURL($oCurl);
        curl_close($oCurl);
        if ($decode) {
            return json_decode($sContent, true);
        } else {
            return $sContent;
        }
    }
    /**
     * [httpPost post请求]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-05T09:03:03+0800
     * @param    string                       $url          [请求地址]
     * @param    array                        $param        [请求参数 json字符串]
     * @param    boolean                      $decode       [是否json_decode 默认返回array false返回json字符串]
     * @param    boolean                      $post_file    [是否携带文件]
     * @return   array|string                               [description]
     */
    public static function Post($url, $param, $decode = true, $post_file = false)
    {
        $oCurl = curl_init();

        if (stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curlFile = true;
        } else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }

        if ($post_file) {
            if ($is_curlFile) {
                foreach ($param as $key => $val) {
                    if (isset($val["tmp_name"])) {
                        $param[$key] = new \CURLFile(realpath($val["tmp_name"]), $val["type"], $val["name"]);
                    } else if (substr($val, 0, 1) == '@') {
                        $val = substr($val, 1);
                        $fi = new \finfo(FILEINFO_MIME_TYPE);
                        $mime_type = $fi->file($val);
                        $filename = basename($val); 
                        $param[$key] = new \CURLFile($val,$mime_type,$filename);
                    } else {
                        $param[$key] = $val;
                    }
                }
            }
            $strPOST = $param;
        } else {
            $strPOST = json_encode($param);
        }
        // dump($strPOST);exit;
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1); // 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        curl_setopt($oCurl, CURLOPT_VERBOSE, 0); //不会输出所有的信息，写入到STDERR，或在CURLOPT_STDERR中指定的文件。
        curl_setopt($oCurl, CURLOPT_HEADER, 0); //不要头

        $sContent = curl_exec($oCurl);
        // $aStatus  = curl_getinfo($oCurl);

        // $sContent = execCURL($oCurl);
        curl_close($oCurl);
        if ($decode) {
            return json_decode($sContent, true);
        } else {
            return $sContent;
        }
    }

    /**
     * [cache 设置/获取缓存数据 文件方式]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T15:10:50+0800
     * @param    [type]                   $appid     [description]
     * @param    [type]                   $appsecret [description]
     * @param    [type]                   $key       [description]
     * @param    string                   $data      [description]
     * @return   [type]                              [description]
     */
    public static function cache($appid, $appsecret, $key, $data = '')
    {
        $w        = ['appid' => $appid, 'appsecret' => $appsecret, 'key' => $key];
        $filename = './' . md5($appid . $appsecret . $key) . '.php';
        if ($data) {
            $arr = ['appid' => $appid,
                'appsecret'     => $appsecret,
                'key'           => $key,
                'data'          => $data,
                'expire_time'   => $_SERVER['REQUEST_TIME'] + 7000,
                'updated_time'  => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
            ];
            $content = json_encode($arr, true);
            $fp      = fopen($filename, "w");
            fwrite($fp, "<?php exit();?>" . $content);
            fclose($fp);
        } else {
            if (file_exists($filename)) {
                return json_decode(trim(substr(file_get_contents($filename), 15)),true);
            } else {
                return json_decode('{"expire_time":0}', true);
            }
        }
    }
    /**
     * [cache1 设置/获取缓存数据 数据库方式 tp5]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T15:11:17+0800
     * @param    [type]                   $appid     [description]
     * @param    [type]                   $appsecret [description]
     * @param    [type]                   $key       [description]
     * @param    string                   $data      [description]
     * @return   [type]                              [description]
     */
    public static function cache1($appid, $appsecret, $key, $data = '')
    {
        $w  = ['appid' => $appid, 'appsecret' => $appsecret, 'key' => $key];
        $db = db('tickets');
        if ($data) {
            $arr = ['data' => $data, 'expire_time' => $_SERVER['REQUEST_TIME'] + 7000, 'updated_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])];
            if ($db->where($w)->count()) {
                $db->where($w)->update($arr);
            } else {
                $arr['num'] = 1;
                $db->insert(array_merge($w, $arr));
            }
        } else {
            return $db->field('data,expire_time')->where($w)->find();
        }
    }
}
