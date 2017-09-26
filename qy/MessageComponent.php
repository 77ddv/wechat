<?php
namespace qy;

use \qy\WeChatHelp;

class MessageComponent
{
    public static $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=";
    /**
     * [sendTextToUser 发送文本消息]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T14:31:25+0800
     * @param    [type]                   $access_token [description]
     * @param    integer                  $agentid      [应用id]
     * @param    string                   $content      [消息内容 不超过2048字节 换行用\n]
     * @param    string                   $touser       [成员ID列表@all 为全部 多人用|分隔最多1000个 @all会忽略部门标签]
     * @param    string                   $topart       [部门 多个用|分隔最多100个]
     * @param    string                   $totag        [标签 多个用|分隔最多100个]
     * @param    integer                  $safe         [是否保密 0不保密 1保密]
     * @return   [type]                                 [description]
     */
    public static function sendText($access_token, $agentid = 1, $content = '', $toOption = [], $safe = 0)
    {
        $data = [
            'agentid' => $agentid,
            'text'    => ['content' => $content],
            'msgtype' => 'text',
            'safe'    => $safe,
        ];
        isset($toOption['touser']) && $data['touser'] = $toOption['touser'];
        isset($toOption['topart']) && $data['topart'] = $toOption['topart'];
        isset($toOption['totag']) && $data['totag']   = $toOption['totag'];

        $url = self::$url . $access_token;
        return WeChatHelp::Post($url, $data);
    }

    /**
     * [sendTextToUser 发送图片消息]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T14:31:25+0800
     * @param    [type]                   $access_token [description]
     * @param    string                   $msgtype      [发送文件类型 image voice file]
     * @param    integer                  $agentid      [应用id]
     * @param    string                   $media_id     [图片对应的mediaid]
     * @param    string                   $touser       [成员ID列表@all 为全部 多人用|分隔最多1000个 @all会忽略部门标签]
     * @param    string                   $topart       [部门 多个用|分隔最多100个]
     * @param    string                   $totag        [标签 多个用|分隔最多100个]
     * @param    integer                  $safe         [是否保密 0不保密 1保密]
     * @param    string                   $title        [视频标题]  仅视频消息有效
     * @param    string                   $description  [视频描述]  仅视频消息有效
     * @return   [type]                                 [description]
     */
    public static function sendMedia($access_token, $agentid = 1, $mediaOption = [], $toOption = [], $safe = 0)
    {
        $data = [
            'agentid'                   => $agentid,
            "{$mediaOption['msgtype']}" => ['media_id' => $mediaOption['media_id']],
            'msgtype'                   => $mediaOption['msgtype'],
            'safe'                      => $safe,
        ];
        isset($toOption['touser']) && $data['touser'] = $toOption['touser'];
        isset($toOption['topart']) && $data['topart'] = $toOption['topart'];
        isset($toOption['totag']) && $data['totag']   = $toOption['totag'];
        if ($mediaOption['msgtype'] == 'video') {
            $data['video']['title']       = $mediaOption['title'];
            $data['video']['description'] = $mediaOption['description'];
        }
        $url = self::$url . $access_token;
        return WeChatHelp::Post($url, $data);
    }

    /**
     * [sendTextToUser 发送卡片消息]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T14:31:25+0800
     * @param    [type]                   $access_token [description]
     * @param    integer                  $agentid      [应用id]
     * @param    string                   $title        [标题128字符]
     * @param    string                   $description  [描述 512字符]
     * @param    string                   $url          [消息内容 不超过2048字节 换行用\n]
     * @param    string                   $touser       [成员ID列表@all 为全部 多人用|分隔最多1000个 @all会忽略部门标签]
     * @param    string                   $topart       [部门 多个用|分隔最多100个]
     * @param    string                   $totag        [标签 多个用|分隔最多100个]
     * @param    integer                  $safe         [是否保密 0不保密 1保密]
     * @return   [type]                                 [description]
     * 支持div标签 目前内置了3种文字颜色：灰色(gray)、高亮(highlight)、默认黑色(normal)
     * 以class方式引用即可 换行使用<br>
     */
    public static function sendCard($access_token, $agentid = 1, $cardOption = [], $toOption = [], $safe = 0)
    {
        // $title='',$description='',$url='',$btntext = '详情'
        $data = [
            'agentid'  => $agentid,
            'textcard' => [
                'title'       => $cardOption['title'],
                'description' => $cardOption['description'],
                'url'         => $cardOption['url'],
                'btntxt'      => $cardOption['btntxt'],
            ],
            'msgtype'  => 'textcard',
            'safe'     => $safe,
        ];
        isset($toOption['touser']) && $data['touser'] = $toOption['touser'];
        isset($toOption['topart']) && $data['topart'] = $toOption['topart'];
        isset($toOption['totag']) && $data['totag']   = $toOption['totag'];
        $url                                          = self::$url . $access_token;
        return WeChatHelp::Post($url, $data);
    }

    /**
     * [sendNews 发送图文消息]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T14:57:17+0800
     * @param    [type]                   $access_token [description]
     * @param    integer                  $agentid      [应用id]
     * @param    array                    $mpnes        [图文消息]
     * @param    string                   $touser       [成员ID列表@all 为全部 多人用|分隔最多1000个 @all会忽略部门标签]
     * @param    string                   $topart       [部门 多个用|分隔最多100个]
     * @param    string                   $totag        [标签 多个用|分隔最多100个]
     * @param    integer                  $safe         [是否保密 0不保密 1保密]
     * @return   [type]                                 [description]
     * @example
     * $mpnews = [
     *         'title'=>$title,
     *       'digest'=>$digest,
     *        'content'=>$content,
     *         'content_source_url'=>$content_source_url,
     *         'author'=>$author,
     *         'thumb_media_id'=>$thumb_media_id,
     *     ];
     */
    public static function sendmpNews($access_token, $agentid = 1, $mpnews = [], $toOption = [], $safe = 0)
    {
        /*$mpnews = ['articles' =>
        [
        [
        'title'              => '标题',
        'digest'             => '描述',
        'content'            => '内容吗',
        'content_source_url' => 'https://dongdavid.com',
        'author'             => 'Dong David',
        'thumb_media_id'     => $media_id,
        ]
        ]
        ];*/
        $data = [
            'agentid' => $agentid,
            'mpnews'  => $mpnews,
            'msgtype' => 'mpnews',
            'safe'    => $safe,
        ];
        isset($toOption['touser']) && $data['touser'] = $toOption['touser'];
        isset($toOption['topart']) && $data['topart'] = $toOption['topart'];
        isset($toOption['totag']) && $data['totag']   = $toOption['totag'];
        $url                                          = self::$url . $access_token;
        return WeChatHelp::Post($url, $data);

    }
    public static function sendNews($access_token, $agentid = 1, $news = [], $toOption = [], $safe = 0)
    {
        /*$news = ['articles' =>
            [
                [
                    'title'       => '标题',
                    'description' => '描述',
                    'picurl'      => 'https://static.dongdavid.com/images/a.png',
                    'url'         => 'https://dongdavid.com',
                ],
            ],
        ];*/
        $data = [
            'agentid' => $agentid,
            'news'    => $news,
            'msgtype' => 'news',
            'safe'    => $safe,
        ];
        isset($toOption['touser']) && $data['touser'] = $toOption['touser'];
        isset($toOption['topart']) && $data['topart'] = $toOption['topart'];
        isset($toOption['totag']) && $data['totag']   = $toOption['totag'];
        $url                                          = self::$url . $access_token;
        return WeChatHelp::Post($url, $data);

    }
}
