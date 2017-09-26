<?php
namespace qy;

use qy\ServerHelper;

/**
 * error code 说明.
 * <ul>
 *    <li>-40001: 签名验证错误</li>
 *    <li>-40002: xml解析失败</li>
 *    <li>-40003: sha加密生成签名失败</li>
 *    <li>-40004: encodingAesKey 非法</li>
 *    <li>-40005: corpid 校验错误</li>
 *    <li>-40006: aes 加密失败</li>
 *    <li>-40007: aes 解密失败</li>
 *    <li>-40008: 解密后得到的buffer非法</li>
 *    <li>-40009: base64加密失败</li>
 *    <li>-40010: base64解密失败</li>
 *    <li>-40011: 生成xml失败</li>
 * </ul>
 */

/**
 * 传入了encodingAesKey之后马上进行base64_decode 同时将encodingAesKey的长度判断由43改为32
 */
class ServerComponent
{
    public $helper;
    private $corpid;
    public function __construct($corpid, $token, $encodingAesKey)
    {
        $this->corpid = $corpid;
        $this->helper = ServerHelper::getInstance($corpid, $token, $encodingAesKey);

    }
    public function setLog()
    {
        $log = "\r\n".date('Y-m-d H:i:s',time())."\r\n";
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $log .= 'Real Client ip is '.getenv("HTTP_X_FORWARDED_FOR")."\r\n";
        }else{
            $log .= 'Client ip is '.$_SERVER["REMOTE_ADDR"]."\r\n";
        }
        $log .="POST:"."\r\n";
        $log .= 'receive data :'."\r\n";
        foreach ($_POST as $k => $v) {
            $log .= "\t".$k.'  :'."\t".$v."\r\n";
        }
        $log .="GET:"."\r\n";
        foreach ($_GET as $k => $v) {
            $log .= "\t".$k.'  :'."\t".$v."\r\n";
        }
        $log .="XML:"."\r\n";
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $postObj = simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'], 'SimpleXMLElement', LIBXML_NOCDATA);   
            $log .= "\t".$GLOBALS['HTTP_RAW_POST_DATA']."\r\n";         
            foreach ($postObj as $key => $value) {
                $log .= "\t".$key.':'."\t".$value."\r\n";
            }
        }
        error_log($log,3,'./access.log');
    }
    public function validUrl()
    {
        $sMsgSignature = $_GET['msg_signature'];
        $sTimeStamp    = $_GET['timestamp'];
        $sNonce        = $_GET['nonce'];
        $sEchoStr      = $_GET['echostr'];
        $sReplyEchoStr = '';
        $r             = $this->helper->VerifyURL($sMsgSignature, $sTimeStamp, $sNonce, $sEchoStr, $sReplyEchoStr);
        if ($r) {
            print('faild:  '.$r);
        }else{
            echo $sReplyEchoStr;
        }
        
        exit;
    }
    public function autoReponse()
    {
        libxml_disable_entity_loader(true);
        $msgObj = $this->receiveMsg();
        switch ($msgObj->MsgType) {
            case 'text':
                $this->sendText($msgObj->FromUserName,$msgObj->Content);
                break;
            case 'image':
                $this->sendImage($msgObj->FromUserName,$msgObj->MediaId);
                break;
            case 'voice':
                $this->sendVoice($msgObj->FromUserName,$msgObj->MediaId);
                break;
            case 'video':
                $this->sendVideo($msgObj->FromUserName,$msgObj->MediaId,$msgObj->Title,$msgObj->Description);
                break;
            default:
                $this->sendText($msgObj->FromUserName,'你的留言我看见了，但是我就是不回复你！');

                // $picurl = 'https://static.dongdavid.com/images/a.png';
                // $url = 'https://dongdavid.com/';
                // $this->sendNews($msgObj->FromUserName,'标题','描述',$picurl,$url);
                break;
        }
        
    }
    public function receiveMsg()
    {
        $sReqMsgSig    = $_GET["msg_signature"];
        $sReqTimeStamp = $_GET["timestamp"];
        $sReqNonce     = $_GET["nonce"];
        $sReqData      = file_get_contents("php://input");
        // $sReqMsgSig    = '';
        // $sReqTimeStamp = ;
        // $sReqNonce     = ;
        // $sReqData      = ';
        $sMsg          = ""; // 解析之后的明文
        $errCode       = $this->helper->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);
		$msgObj = simplexml_load_string($sMsg,null,LIBXML_NOCDATA);
        if (!$errCode) {
            return $msgObj;
        }
        return false;
        
        // $xml = new DOMDocument();
        // $xml->loadXML($sMsg);
        // $event = $xml->getElementsByTagName('MsgType')->item(0)->nodeValue;
        // if ($event == 'event') {
        //     return $this->getEvent($xml);
        // } else {
        //     return $this->getMsg($xml);
        // }

    }
    public function sendText($to, $text)
    {

        $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content></xml>";
        $r = sprintf($textTpl, $this->corpid, $to, $_SERVER['REQUEST_TIME'], 'text', $text);
        $this->responseData($r);
    }
    public function sendImage($to, $media_id)
    {
        $textTpl = "<xml>
               <ToUserName><![CDATA[%s]]></ToUserName>
               <FromUserName><![CDATA[%s]]></FromUserName>
               <CreateTime>%s</CreateTime>
               <MsgType><![CDATA[%s]]></MsgType>
               <Image>
                   <MediaId><![CDATA[%s]]></MediaId>
               </Image>
            </xml>";
        $r = sprintf($textTpl, $this->corpid, $to, $_SERVER['REQUEST_TIME'], 'image', $media_id);
        $this->responseData($r);
    }
    public function sendVoice($to, $media_id)
    {
        $textTpl = "<xml>
               <ToUserName><![CDATA[%s]]></ToUserName>
               <FromUserName><![CDATA[%s]]></FromUserName>
               <CreateTime>%s</CreateTime>
               <MsgType><![CDATA[%s]]></MsgType>
               <Voice>
                   <MediaId><![CDATA[%s]]></MediaId>
               </Voice>
            </xml>";
        $r = sprintf($textTpl, $this->corpid, $to, $_SERVER['REQUEST_TIME'], 'voice', $media_id);
        $this->responseData($r);
    }
    public function sendVideo($to, $media_id,$title,$description)
    {
        $textTpl = "<xml>
               <ToUserName><![CDATA[%s]]></ToUserName>
               <FromUserName><![CDATA[%s]]></FromUserName>
               <CreateTime>%s</CreateTime>
               <MsgType><![CDATA[%s]]></MsgType>
               <Video>
                   <MediaId><![CDATA[%s]]></MediaId>
                   <Title><![CDATA[%s]]></Title>
                   <Description><![CDATA[%s]]></Description>
               </Video>
            </xml>";
        $r = sprintf($textTpl, $this->corpid, $to, $_SERVER['REQUEST_TIME'], 'video', $media_id,$title,$description);
        $this->responseData($r);
    }
    /**
     * [sendNews 发送图文消息]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T17:23:34+0800
     * @param    [type]                   $to          [接收人 userid]
     * @param    [type]                   $title       [标题]
     * @param    [type]                   $description [描述]
     * @param    [type]                   $picurl      [封面图片]
     * @param    [type]                   $url         [跳转链接]
     * @return   [type]                                [description]
     */
    public function sendNews($to,$title,$description,$picurl,$url)
    {
        $textTpl = "<xml>
               <ToUserName><![CDATA[%s]]></ToUserName>
               <FromUserName><![CDATA[%s]]></FromUserName>
               <CreateTime>%s</CreateTime>
               <MsgType><![CDATA[%s]]></MsgType>
               <ArticleCount>%s</ArticleCount>
               <Articles>
                   <item>
                       <Title><![CDATA[%s]]></Title> 
                       <Description><![CDATA[%s]]></Description>
                       <PicUrl><![CDATA[picurl]]></%s>
                       <Url><![CDATA[%s]]></Url>
                   </item>
                   <item>
                       <Title><![CDATA[%s]]></Title> 
                       <Description><![CDATA[%s]]></Description>
                       <PicUrl><![CDATA[picurl]]></%s>
                       <Url><![CDATA[%s]]></Url>
                   </item>
                   
               </Articles>
            </xml>";
        $r = sprintf($textTpl, $this->corpid, $to, $_SERVER['REQUEST_TIME'], 'news', 2,$title,$description,$picurl,$url,$title,$description,$picurl,$url);
        $this->responseData($r);
    }
    /**
     * [responseData 将数据加密并输出]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T17:12:58+0800
     * @param    [type]                   $r [未加密的数据]
     * @return   [type]                      [description]
     */
    public function responseData($r)
    {
        // dump($r);
        $msg    = '';
        if (!$this->helper->EncryptMsg($r, $msg)) {
            echo $msg;
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }
    /*public function getEvent($xml)
    {
        $arr = [
            'type'    => $xml->getElementsByTagName('MsgType')->item(0)->nodeValue, //固定为event
            'from'    => $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue,
            'time'    => $xml->getElementsByTagName('CreateTime')->item(0)->nodeValue,
            'to'      => $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue,
            'event'   => $xml->getElementsByTagName('Event')->item(0)->nodeValue,
            'agentid' => $xml->getElementsByTagName('AgentID')->item(0)->nodeValue,
        ];
        //   'eventkey'=>$xml->getElementsByTagName('EventKey')->item(0)->nodeValue,
        switch ($arr['event']) {
            case 'LOCATION':
                $arr['lat'] = $xml->getElementsByTagName('Latitude')->item(0)->nodeValue;
                $arr['lon'] = $xml->getElementsByTagName('Longitude')->item(0)->nodeValue;
                // 精度
                $arr['precision'] = $xml->getElementsByTagName('Precision')->item(0)->nodeValue;
                break;
            case 'subscribe':
                break;
            case 'unsubscribe':
                break;
            case 'enter_agent':
                break;
            // 异步任务结果
            case 'batch_job_result':
                //64字符
                $arr['jobid'] = $xml->getElementsByTagName('JobId')->item(0)->nodeValue;
                // 返回码
                $arr['code'] = $xml->getElementsByTagName('ErrCode')->item(0)->nodeValue;
                // 返回消息
                $arr['msg'] = $xml->getElementsByTagName('ErrMsg')->item(0)->nodeValue;
                // 操作类型 sync_user(增量更新成员)、 replace_user(全量覆盖成员）、invite_user(邀请成员关注）、replace_party(全量覆盖部门)
                $arr['jobtype'] = $xml->getElementsByTagName('JobType')->item(0)->nodeValue;

                break;
            // 通讯录变更 用户修改个人信息
            case 'change_contact':
                // 固定为 update_user
                $arr['changetype'] = $xml->getElementsByTagName('ChangeType')->item(0)->nodeValue;
                $arr['userid']     = $xml->getElementsByTagName('UserID')->item(0)->nodeValue;
                $arr['name']       = $xml->getElementsByTagName('Name')->item(0)->nodeValue;
                $arr['mobile']     = $xml->getElementsByTagName('Mobile')->item(0)->nodeValue;
                $arr['email']      = $xml->getElementsByTagName('Gender')->item(0)->nodeValue;
                break;
            // 菜单事件
            case 'click':
                // 预设的值
                $arr['eventkey'] = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
                break;
            case 'view':
                // 跳转的链接
                $arr['eventkey'] = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
                break;
            case 'scancode_push':
                //
                $arr['eventkey'] = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
                // 扫码信息
                $arr['scaninfo'] = $xml->getElementsByTagName('ScanCodeInfo')->item(0)->nodeValue;
                // 扫码类型 qrcode
                $arr['scantype'] = $xml->getElementsByTagName('ScanType')->item(0)->nodeValue;
                // 扫码结果
                $arr['scanresult'] = $xml->getElementsByTagName('ScanResult ')->item(0)->nodeValue;
                break;
            case 'scancode_waitmsg':
                //
                $arr['eventkey'] = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
                // 扫码信息
                $arr['scaninfo'] = $xml->getElementsByTagName('ScanCodeInfo')->item(0)->nodeValue;
                // 扫码类型 qrcode
                $arr['scantype'] = $xml->getElementsByTagName('ScanType')->item(0)->nodeValue;
                // 扫码结果
                $arr['scanresult'] = $xml->getElementsByTagName('ScanResult ')->item(0)->nodeValue;
                break;
            case 'pic_sysphoto':
                //
                $arr['eventkey'] = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
                // 图片信息
                $arr['scaninfo'] = $xml->getElementsByTagName('ScanCodeInfo')->item(0)->nodeValue;
                // 图片数量
                $arr['count'] = $xml->getElementsByTagName('Count')->item(0)->nodeValue;
                // 图片列表
                $arr['piclist'] = $xml->getElementsByTagName('PicList ')->item(0)->nodeValue;
                // 图片的MD5
                $arr['picmd5'] = $xml->getElementsByTagName('PicMd5Sum ')->item(0)->nodeValue;
                break;
            case 'pic_photo_or_album':
                // 跳转的链接
                $arr['eventkey'] = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
                // 图片信息
                $arr['scaninfo'] = $xml->getElementsByTagName('ScanCodeInfo')->item(0)->nodeValue;
                // 图片数量
                $arr['count'] = $xml->getElementsByTagName('Count')->item(0)->nodeValue;
                // 图片列表
                $arr['piclist'] = $xml->getElementsByTagName('PicList ')->item(0)->nodeValue;
                // 图片的MD5
                $arr['picmd5'] = $xml->getElementsByTagName('PicMd5Sum ')->item(0)->nodeValue;
                break;
            case 'pic_weixin':
                //
                $arr['eventkey'] = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
                // 图片信息
                $arr['scaninfo'] = $xml->getElementsByTagName('ScanCodeInfo')->item(0)->nodeValue;
                // 图片数量
                $arr['count'] = $xml->getElementsByTagName('Count')->item(0)->nodeValue;
                // 图片列表
                $arr['piclist'] = $xml->getElementsByTagName('PicList ')->item(0)->nodeValue;
                // 图片的MD5
                $arr['picmd5'] = $xml->getElementsByTagName('PicMd5Sum ')->item(0)->nodeValue;
                break;
            case 'pic_weixin':
                //
                $arr['eventkey'] = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;
                $tmp             = $xml->getElementsByTagName('SendLocationInfo')->item(0);
                // 纬度
                $arr['location_x'] = $tmp->getElementsByTagName('Location_X')->item(0)->nodeValue;
                // 经度
                $arr['location_y'] = $tmp->getElementsByTagName('Location_Y')->item(0)->nodeValue;
                // 精度 越高越准确
                $arr['scale'] = $tmp->getElementsByTagName('Scale')->item(0)->nodeValue;
                // 地理位置字符串信息
                $arr['label'] = $tmp->getElementsByTagName('Label')->item(0)->nodeValue;
                // poi
                $arr['poiname'] = $tmp->getElementsByTagName('Poiname')->item(0)->nodeValue;

                break;
            default:
                # code...
                break;
        }
        return $arr;
    }
    public function getMsg($xml)
    {
        $arr = [
            'type'    => $xml->getElementsByTagName('MsgType')->item(0)->nodeValue,
            'from'    => $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue,
            'time'    => $xml->getElementsByTagName('CreateTime')->item(0)->nodeValue,
            'to'      => $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue,
            'msgid'   => $xml->getElementsByTagName('MsgId')->item(0)->nodeValue, //64位整形
            'agentid' => $xml->getElementsByTagName('AgentID')->item(0)->nodeValue,
        ];
        switch ($arr['type']) {
            case 'text':
                $arr['content'] = $xml->getElementsByTagName('Content')->item(0)->nodeValue;
                break;
            case 'image':
                $arr['picurl']  = $xml->getElementsByTagName('PicUrl')->item(0)->nodeValue;
                $arr['mediaid'] = $xml->getElementsByTagName('MediaId')->item(0)->nodeValue;
                break;
            case 'voice':
                //语音格式，如amr，speex等
                $arr['Format']  = $xml->getElementsByTagName('Format')->item(0)->nodeValue;
                $arr['mediaid'] = $xml->getElementsByTagName('MediaId')->item(0)->nodeValue;
                break;
            case 'video':
                //视频消息缩略图媒体id
                $arr['thumbmediaid'] = $xml->getElementsByTagName('ThumbMediaId')->item(0)->nodeValue;
                $arr['mediaid']      = $xml->getElementsByTagName('MediaId')->item(0)->nodeValue;
                break;
            case 'location':
                //位置信息
                $arr['label'] = $xml->getElementsByTagName('Label')->item(0)->nodeValue;
                //精度
                $arr['scale'] = $xml->getElementsByTagName('Scale')->item(0)->nodeValue;
                //地理位置 纬度
                $arr['location_x'] = $xml->getElementsByTagName('Location_X')->item(0)->nodeValue;
                //地理位置 精度
                $arr['location_y'] = $xml->getElementsByTagName('Location_Y')->item(0)->nodeValue;
                break;
            case 'link':
                $arr['title']       = $xml->getElementsByTagName('Title')->item(0)->nodeValue;
                $arr['description'] = $xml->getElementsByTagName('Description')->item(0)->nodeValue;
                $arr['picurl']      = $xml->getElementsByTagName('PicUrl')->item(0)->nodeValue;
                break;
            default:

                break;
        }
        return $arr;
    }*/
}
