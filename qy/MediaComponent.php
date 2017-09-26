<?php
namespace qy;

use \qy\WeChatHelp;

class MediaComponent
{
    /**
     * [downloadMedia 下载素材]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T14:58:08+0800
     * @param    [type]                   $access_token [description]
     * @param    [type]                   $media_id     [素材id]
     * @return   [type]                                 [description]
     */
    public static function downloadMedia($access_token, $media_id, $savePath = './')
    {
        $url   = "https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$media_id}";
        $oCurl = curl_init();
        if (stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_VERBOSE, 0);
        curl_setopt($oCurl, CURLOPT_HEADER, 1);
        // curl_setopt($oCurl,CURLOPT_);
        $sContent = curl_exec($oCurl);
        // $sContent = self::execCURL($oCurl);
        $header_size = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
        // 响应头
        $header      = substr($sContent, 0, $header_size);
        // 文件内容
        $fileData    = substr($sContent, $header_size);
        curl_close($oCurl);

        preg_match('/filename="([\S]*?)"/', $header, $res);
        $filePath = $savePath . $media_id . $res[1];
        if (file_put_contents($filePath, $fileData)) {
            return $filePath;
        } else {
            return false;
        }

    }
    /**
     * [uploadMedia 上传临时素材]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T11:33:55+0800
     * @param    [type]                   $access_token [description]
     * @param    [type]                    $type         [素材类型]
     *           voice image video file
     * @param    [type]                   $media        [文件路径]
     * @return   [type]                                 [description]
     */
    public static function uploadMedia($access_token, $type, $media)
    {
        $media = '@' . $media;
        $url   = "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$type}";
        return WeChatHelp::Post($url, ['media' => $media], true, true);
    }

}
