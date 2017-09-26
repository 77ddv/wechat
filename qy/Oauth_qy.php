<?php
namespace qy;
use \qy\WeChatHelp;
/**
 * Oauth 微信公众号版本
 */
class Oauth_qy
{
    public static function getUserid($appid,$agentid,$appsecret,$isWeb = false)
    {
        header("Content-type: text/html; charset=utf-8");
        $userid = '';
        isset($_SESSION[$appid.'_userid_']) && $userid = $_SESSION[$appid.'_userid_'];

        if (!empty($userid)) {
            // 已授权,无需重复授权
            return $_SESSION[$appid.'_userid_'];
        }
        if (empty($userid) && isset($_GET['code'])) {
            // 获取code之后请求用户的userid
            $data   = self::code_userid($appid, $appsecret, $_GET['code']);
            if ($data['errcode']) {
                // 这里请求userid失败最好写个日志
                // 出错返回false
                return false;
            }
            if (isset($data['UserId'])) {
                $_SESSION[$appid.'_userid_'] = $data['UserId'];
                return $data['UserId'];
            }
            // 非内部人员只能获取到openid
            if (isset($data['OpenId'])) {
                $_SESSION[$appid.'_userid_'] = $data['OpenId'];
                return $data['OpenId'];
            }
        }
        if (empty($userid)) {
            // 创建授权链接
            if ($isWeb) {
                $url = self::createOauthUrlForWeb($appid,$agentid);
            }else{
                $url = self::createOauthUrl($appid,$agentid,'snsapi_base');
            }
            header('Location: '.$url);
            exit;
        }
        return $userid;
    }
    
    public static function createOauthUrlForWeb($appid,$agentid)
    {
        $param['appid']         = $appid;
        $param['redirect_uri']  = WeChatHelp::getCurrentUrl();
        $param['agentid']       = $agentid;
        $param['state']         = 'push';
        $url                    = 'https://open.work.weixin.qq.com/wwopen/sso/qrConnect?' . http_build_query($param) . '#wechat_redirect';
        return $url;
    }

    /**
     * [createOauthUrl 构造授权链接]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-06T13:28:50+0800
     * @param    [type]                   $appid [description]
     * @param    string                   $scope [snsapi_base,snsapi_userinfo snsapi_privateinfo]
     * @return   [type]                          [description]
     */
    public static function createOauthUrl($appid,$agentid = '', $scope = 'snsapi_base')
    {
        $param['appid']         = $appid;
        $param['redirect_uri']  = WeChatHelp::getCurrentUrl();
        $param['response_type'] = 'code';
        $param['scope']         = $scope;
        $param['agentid']       = $agentid;
        $param['state']         = 'push';
        $url                    = 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query($param) . '#wechat_redirect';
        return $url;
    }
    /**
     * [code_userid 利用code获取userid]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-06T13:29:16+0800
     * @param    [type]                   $appid     [description]
     * @param    [type]                   $appsecret [description]
     * @param    [type]                   $code      [description]
     * @return   [type]                              [description]
     */
    public static function code_userid($appid, $appsecret, $code)
    {
        if (empty($code)) {
            return false;
        }
        $access_token = WeChatHelp::getAccessToken($appid,$appsecret);
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token={$access_token}&code={$code}";
        $content = WeChatHelp::Get($url);
        return $content;
    }
    /**
     * [getUserinfoByUserid 通过userid获取用户信息]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-19T16:51:53+0800
     * @param    [type]                   $appid     [description]
     * @param    [type]                   $appsecret [description]
     * @param    [type]                   $userid    [description]
     * @return   [type]                              [description]
     */
    public static function getUserinfoByUserid($appid,$appsecret,$userid)
    {
        if (empty($userid)) {
            return false;
        }
        $access_token = WeChatHelp::getAccessToken($appid,$appsecret);
        $url          = "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=$access_token&userid=$userid";
        return WeChatHelp::Get($url);
    }
    // userid 转 openid
    public static function useridToOpenid($appid,$appsecret,$userid,$agentid)
    {
        $access_token = WeChatHelp::getAccessToken($appid,$appsecret);
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token={$access_token}";
        $param = ['userid'=>$userid,'agentid'=>$agentid];
        return WeChatHelp::Post($url,$param);
    }

    // openid 转 userid
    public static function openidToUserid($appid,$appsecret,$openid)
    {
        $access_token = WeChatHelp::getAccessToken($appid,$appsecret);
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid?access_token={$access_token}";
        $param = ['openid'=>$openid];
        return WeChatHelp::Post($url,$param);
    }
    
}
