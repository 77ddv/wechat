## 企业微信API接口封装  

## 目录  

|- WeChatHelp.php  ------------- 	帮助类
|- ServerComponent.php  -------- 	服务端回调、消息回复插件
|- MediaComponent.php  ---------	素材管理插件
|- UserPartComponent.php  ------ 	用户部门标签管理插件
|- MessageComponent.php  ------- 	消息插件
|- Oauth_qy.php  ---------------	oauth插件
|- test.php  -------------------	测试入口


## 接口说明  
	

什么都没有啦  


## ApiGen  



## 测试说明  
	服务端
		回调验证 		----- 正常
		消息接收 		----- 正常
		文本消息回复	----- 正常
		图片消息回复	----- 正常
		语音消息回复	----- 正常
		视频消息回复	----- 正常
		图文消息回复    ----- 不正常
		事件响应		----- 未测试

	临时素材管理  
		临时素材上传	----- 正常
		临时素材下载	----- 正常

	帮助类  
		Post接口		----- 正常
		Get接口			----- 正常
		获取accesstoken ----- 正常
		获取随机字符串	----- 正常
		获取当前url		----- 正常
		文件缓存		----- 正常
		数据库缓存		----- 未测试

	消息发送类  
		发送文本消息	----- 正常
		发送图片消息	----- 正常
		发送语音消息	----- 未测试
		发送视频消息	----- 未测试
		发送文件消息	----- 正常
		发送图文消息	----- 正常
		发送mp图文消息	----- 正常
		发送文本卡片	----- 正常
		
	授权登陆类  
		获取userid 		----- 正常
		获取用户详细信息----- 正常
		userid转换为openid--- 正常
		根据openid获取信息--- 不正常
		网页授权登陆	----- 正常
		网页扫码登陆	----- 没写！


## 素材代码示例  
	
	include_once './MediaComponent.php';
	use \qy\MediaComponent;
	$imgpath = "./aa.png";
	// 上传临时素材
	$res = MediaComponent::uploadMedia($access_token,'image',$imgpath);
	$res = [
		"created_at"=>"1380000000",
		"media_id"=>"1gX_7eGBxen0IY0Cuu3Y8BOznCLKbJm9OfHjS7TPMuOo",
		"type"=>"image",
	];
	// 下载临时素材
	$filePath = MediaComponent::downloadMedia($access_token,$media_id,$savPath='./');

## 发送消息示例 
	
	include_once './MessageComponent.php';
	use \qy\MessageComponent;
	// 文本消息
	$res = MessageComponent::sendText($access_token,$agentid,'测试消息',$to);
	// 卡片消息
	$card = ['title'=>'标题','description'=>'描述','url'=>'https://dongdavid.com','btntxt'=>'查看详情'];
	$res = MessageComponent::sendCard($access_token,$agentid,$card,$to);

	
	$media_id = '1gX_7eGBxen0IY0Cuu3Y8BOznCLKbJm9OfHjS7TPMuOo';
	// 图片消息
	$mediaOption = ['msgtype'=>'image','media_id'=>$media_id];
	$res = MessageComponent::sendMedia($access_token,$agentid,$mediaOption,$to);
	// 文件消息
	$mediaOption = ['msgtype'=>'file','media_id'=>$media_id];
	$res = MessageComponent::sendMedia($access_token,$agentid,$mediaOption,$to);
	// 语音消息
	$mediaOption = ['msgtype'=>'voice','media_id'=>$media_id];
	$res = MessageComponent::sendMedia($access_token,$agentid,$mediaOption,$to);
	// 视频消息
	$mediaOption = ['msgtype'=>'video','media_id'=>$media_id,'title'=>'标题','description'=>'描述'];
	$res = MessageComponent::sendMedia($access_token,$agentid,$mediaOption,$to);
	// 图文消息
	$news = ['articles' =>
		    [
		        [
		            'title'       => '标题',
		            'description' => '描述',
		            'picurl'      => 'https://static.dongdavid.com/images/a.png',
		            'url'         => 'https://dongdavid.com',
		        ],
		    ],
		];
	$res = MessageComponent::sendNews($access_token, $agentid, $news, $to);
	// mp图文消息
	$mpnews = ['articles' =>
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
				];
	$res = MessageComponent::sendmpNews($access_token, $agentid, $mpnews, $to);

## 授权Oauth  






创建试题 设置发布时间 7-22  

写文章----   到7-22 推送到公众号

此时文章已推送出去，然后用户点击阅读原文可以进行答题。