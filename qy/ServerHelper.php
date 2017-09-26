<?php
namespace qy;

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
class ServerHelper
{
    public static $_instance;
    public static $OK                     = 0;
    public static $ValidateSignatureError = '签名验证错误';//-40001;
    public static $ParseXmlError          = 'xml解析失败';//-40002;
    public static $ComputeSignatureError  = 'sha加密生成签名失败';//-40003;
    public static $IllegalAesKey          = 'encodingAesKey 非法';//-40004;
    public static $ValidateCorpidError    = 'corpid 校验错误';//-40005;
    public static $EncryptAESError        = 'aes 加密失败';//-40006;
    public static $DecryptAESError        = 'aes 解密失败';//-40007;
    public static $IllegalBuffer          = '解密后得到的buffer非法';//-40008;
    public static $EncodeBase64Error      = 'base64加密失败';//-40009;
    public static $DecodeBase64Error      = 'base64解密失败';//-40010;
    public static $GenReturnXmlError      = '生成xml失败';//-40011;
    public static $block_size             = 32;
    private $m_sToken;
    private $m_sEncodingAesKey;
    private $m_sCorpid;
    //禁用构造方法 防止直接实例化
    private function __construct($corpid, $token, $encodingAesKey)
    {
        $this->m_sCorpid         = $corpid;
        $this->m_sToken          = $token;
        $this->m_sEncodingAesKey = base64_decode($encodingAesKey.'=');
    }
    /**
     * [getInstance 获取单例]
     * Don't look at me!
     * @Author   DongDavid
     * @DateTime 2017-07-17T15:53:10+0800
     * @param    [type]                   $corpid         [description]
     * @param    [type]                   $token          [description]
     * @param    [type]                   $encodingAesKey [description]
     * @return   [type]                                   [description]
     */
    public static function getInstance($corpid, $token, $encodingAesKey)
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c($corpid,$token,$encodingAesKey);
        }
        return self::$_instance;
    }
    /**
     * [VerifyURL 验证URL]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T13:52:31+0800
     * @param sMsgSignature: 签名串，对应URL参数的msg_signature
     * @param sTimeStamp: 时间戳，对应URL参数的timestamp
     * @param sNonce: 随机串，对应URL参数的nonce
     * @param sEchoStr: 随机串，对应URL参数的echostr
     * @param sReplyEchoStr: 解密之后的echostr，当return返回0时有效
     * @return：成功0，失败返回对应的错误码
     */
    public function VerifyURL($sMsgSignature, $sTimeStamp, $sNonce, $sEchoStr, &$sReplyEchoStr)
    {
        if (strlen($this->m_sEncodingAesKey) != 32) {
            return self::$IllegalAesKey;
        }

        //verify msg_signature
        // $sha1 = new SHA1;
        $array = $this->getSHA1($this->m_sToken, $sTimeStamp, $sNonce, $sEchoStr);
        $ret   = $array[0];

        if ($ret != 0) {
            return $ret;
        }

        $signature = $array[1];
        if ($signature != $sMsgSignature) {
            return self::$ValidateSignatureError;
        }

        $result = $this->decrypt($sEchoStr, $this->m_sCorpid);
        if ($result[0] != 0) {
            return $result[0];
        }
        $sReplyEchoStr = $result[1];
        return self::$OK;
    }


    /**
	 * 将企业微信回复用户的消息加密打包.
	 * <ol>
	 *    <li>对要发送的消息进行AES-CBC加密</li>
	 *    <li>生成安全签名</li>
	 *    <li>将消息密文和安全签名打包成xml格式</li>
	 * </ol>
	 *
	 * @param $replyMsg string 企业微信待回复用户的消息，xml格式的字符串
	 * @param $timeStamp string 时间戳，可以自己生成，也可以用URL参数的timestamp
	 * @param $nonce string 随机串，可以自己生成，也可以用URL参数的nonce
	 * @param &$encryptMsg string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串,
	 *                      当return返回0时有效
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function EncryptMsg($sReplyMsg, &$sEncryptMsg,$sTimeStamp=null, $sNonce=null)
	{

		//加密
		$array = $this->encrypt($sReplyMsg, $this->m_sCorpid);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}

		if ($sTimeStamp == null) {
			$sTimeStamp = $_SERVER['REQUEST_TIME'];
		}
        if ($sNonce == null) {
            $sNonce = $this->getRandomStr();
        }
		$encrypt = $array[1];

		//生成安全签名
		$array = $this->getSHA1($this->m_sToken, $sTimeStamp, $sNonce, $encrypt);
		$ret = $array[0];
		if ($ret != 0) {
			return $ret;
		}
		$signature = $array[1];
		//生成发送的xml
		$sEncryptMsg = $this->generate($encrypt, $signature, $sTimeStamp, $sNonce);
		return self::$OK;
	}


	/**
	 * 检验消息的真实性，并且获取解密后的明文.
	 * <ol>
	 *    <li>利用收到的密文生成安全签名，进行签名验证</li>
	 *    <li>若验证通过，则提取xml中的加密消息</li>
	 *    <li>对消息进行解密</li>
	 * </ol>
	 *
	 * @param $msgSignature string 签名串，对应URL参数的msg_signature
	 * @param $timestamp string 时间戳 对应URL参数的timestamp
	 * @param $nonce string 随机串，对应URL参数的nonce
	 * @param $postData string 密文，对应POST请求的数据
	 * @param &$msg string 解密后的原文，当return返回0时有效
	 *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function DecryptMsg($sMsgSignature, $sTimeStamp = null, $sNonce, $sPostData, &$sMsg)
	{
		if (strlen($this->m_sEncodingAesKey) != 32) {
			return self::$IllegalAesKey;
		}

		//提取密文
		$array = $this->extract($sPostData);
		$ret = $array[0];

		if ($ret != 0) {
			return $ret;
		}

		if ($sTimeStamp == null) {
			$sTimeStamp = time();
		}

		$encrypt = $array[1];
		$touser_name = $array[2];

		//验证安全签名
		$array = $this->getSHA1($this->m_sToken, $sTimeStamp, $sNonce, $encrypt);
		$ret = $array[0];

		if ($ret != 0) {
			return $ret;
		}

		$signature = $array[1];
		if ($signature != $sMsgSignature) {
			return self::$ValidateSignatureError;
		}

		$result = $this->decrypt($encrypt, $this->m_sCorpid);
		if ($result[0] != 0) {
			return $result[0];
		}
		$sMsg = $result[1];

		return self::$OK;
	}







    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    public function encode($text)
    {
        $block_size  = self::$block_size;
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = self::$block_size - ($text_length % self::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = self::block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp     = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    public function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > self::$block_size) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @return string 加密后的密文
     */
    public function encrypt($text, $corpid)
    {
        try {
            //获得16位随机字符串，填充到明文之前
            $random = $this->getRandomStr();
            $text   = $random . pack("N", strlen($text)) . $text . $corpid;
            // 网络字节序
            $size   = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv     = substr($this->m_sEncodingAesKey, 0, 16);
            //使用自定义的填充方式对明文进行补位填充
            $text        = $this->encode($text);
            mcrypt_generic_init($module, $this->m_sEncodingAesKey, $iv);
            //加密
            $encrypted = mcrypt_generic($module, $text);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);

            //print(base64_encode($encrypted));
            //使用BASE64对加密后的字符串进行编码
            return array(self::$OK, base64_encode($encrypted));
        } catch (Exception $e) {
            print $e;
            return array(self::$EncryptAESError, null);
        }
    }

    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @return string 解密得到的明文
     */
    public function decrypt($encrypted, $corpid)
    {
        try {
            //使用BASE64对需要解密的字符串进行解码
            $ciphertext_dec = base64_decode($encrypted);
            $module         = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv             = substr($this->m_sEncodingAesKey, 0, 16);
            
            mcrypt_generic_init($module, $this->m_sEncodingAesKey, $iv);

            //解密
            $decrypted = mdecrypt_generic($module, $ciphertext_dec);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            return array(self::$DecryptAESError, null);
        }

        try {
            //去除补位字符
            $result      = $this->decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16) {
                return "";
            }
            $content     = substr($result, 16, strlen($result));
            $len_list    = unpack("N", substr($content, 0, 4));
            $xml_len     = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_corpid = substr($content, $xml_len + 4);
        } catch (Exception $e) {
            print $e;
            return array(self::$IllegalBuffer, null);
        }
        if ($from_corpid != $corpid) {
            return array(self::$ValidateCorpidError, null);
        }
        return array(0, $xml_content);

    }

    /**
     * 提取出xml数据包中的加密消息
     * @param string $xmltext 待提取的xml字符串
     * @return string 提取出的加密消息字符串
     */
    public function extract($xmltext)
    {
        try {
            $xml = new \DOMDocument();
            $xml->loadXML($xmltext);
            $array_e    = $xml->getElementsByTagName('Encrypt');
            $array_a    = $xml->getElementsByTagName('ToUserName');
            $encrypt    = $array_e->item(0)->nodeValue;
            $tousername = $array_a->item(0)->nodeValue;
            // libxml_disable_entity_loader(true);
            // $obj = simplexml_load_string($xmltext);
            // $encrypt = $obj->Encrypt;
            // $tousername = $obj->ToUserName;
            return array(0, $encrypt, $tousername);
        } catch (Exception $e) {
            print $e . "\n";
            return array(self::$ParseXmlError, null, null);
        }
    }

    /**
     * 生成xml消息
     * @param string $encrypt 加密后的消息密文
     * @param string $signature 安全签名
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     */
    public function generate($encrypt, $signature, $timestamp, $nonce)
    {
        $format = "<xml>
					   <Encrypt><![CDATA[%s]]></Encrypt>
					   <MsgSignature><![CDATA[%s]]></MsgSignature>
					   <TimeStamp>%s</TimeStamp>
					   <Nonce><![CDATA[%s]]></Nonce>
				   </xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }

    /**
     * 提取出xml数据包中的加密消息
     * @param string $xmltext 待提取的xml字符串
     * @return string 提取加密后回调模式接口验证需要的参数
     */
    public function extractCallbackParamter($xmltext)
    {
        try {
            $xml = new \DOMDocument();
            $xml->loadXML($xmltext);
            $Encrypt      = $xml->getElementsByTagName('Encrypt')->item(0)->nodeValue;
            $MsgSignature = $xml->getElementsByTagName('MsgSignature')->item(0)->nodeValue;
            $TimeStamp    = $xml->getElementsByTagName('TimeStamp')->item(0)->nodeValue;
            $Nonce        = $xml->getElementsByTagName('Nonce')->item(0)->nodeValue;
            
            // $obj = simplexml_load_string($xmltext);
            // $Encrypt = $obj->Encrypt;
            // $MsgSignature = $obj->MsgSignature;
            // $TimeStamp = $obj->TimeStamp;
            // $Nonce = $obj->Nonce;


            return array($Encrypt, $MsgSignature, $TimeStamp, $Nonce);
        } catch (Exception $e) {
            print $e . "\n";
            return array(self::$ParseXmlError, null, null);
        }
    }

    /**
     * 生成xml消息
     * @param string $encrypt 加密后的消息密文
     * @param string $agentId 应用ID
     * @param string $tousername 企业ID
     */
    public function generateCallbackXml($encrypt, $agentId, $tousername)
    {
        $format = "<xml>
					   <ToUserName><![CDATA[%s]]></ToUserName>
					   <AgentID><![CDATA[%s]]></AgentID>
					   <Encrypt>%s</Encrypt>
				   </xml>";
        return sprintf($format, $tousername, $agentId, $encrypt);
    }
    /**
     * 用SHA1算法生成安全签名
     * @param string $token 票据
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param string $encrypt 密文消息
     */
    public function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
    {
        //排序
        try {
            $array = array($encrypt_msg, $token, $timestamp, $nonce);
            sort($array, SORT_STRING);
            $str = implode($array);
            return array(self::$OK, sha1($str));
        } catch (Exception $e) {
            // print $e . "\n";
            return array(self::$ComputeSignatureError, null);
        }
    }
    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    public function getRandomStr()
    {

        $str     = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max     = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
}
