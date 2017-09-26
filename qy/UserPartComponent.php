<?php
namespace qy\component;

use \qy\WeChatHelp;

/**
 *     部门管理
 *     @param string  name 部门名称。长度限制为1~64个字节，字符不能包括\:?”<>｜
 *     @param int        parentid 父级部门id
 *     @param int     order 在父部门中的次序值。order值大的排序靠前。
 *     @param int        id    部门id
 *     部门的最大层级为15层；部门总数不能超过3万个；每个部门下的节点不能超过3万个。
 *
 *   成员管理
 *   @param string  userid  企业内必须唯一。不区分大小写，长度为1~64个字节
 *   @param string  name  成员名称。长度为1~64个字节
 *   @param string  mobile 手机号码。企业内必须唯一
 *   @param array   department 成员所属部门id列表,不超过20个
 *   @param string  english_name 英文名。长度为1-64个字节。
 *   @param int     order 部门内排序 默认0
 *   @param string  position 职位信息。长度为0~64个字节
 *   @param int     gender 性别  1男 2女 0 未知
 *   @param string  email  邮箱。长度为0~64个字节。企业内必须唯一
 *   @param string  telephone  座机。长度0-64个字节。
 *   @param int     isleader 上级字段，标识是否为上级
 *   @param int     enable 启用/禁用成员。1表示启用成员，0表示禁用成员
 *   @param array   extattr 自定义字段 ['atrs'=>['name'=>'','value'=>'',],]
 *   #仅创建/更新可用
 *   @param string  avatar_mediaid  成员头像的mediaid，
 *   #仅查询可用
 *   @param string  avatar 头像url。注：如果要获取小图将url最后的”/0”改成”/100”即可
 *   @param int     status 激活状态: 1=已激活，2=已禁用，4=未激活。
 *
 */
class UserPartComponent
{
    /**
     * [createDepartment 创建部门]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T09:10:24+0800
     * @param    [type]                   $access_token [description]
     * @param    [type]                   $id           部门id
     * @param    string                   $name         部门名称 必填
     * @param    string                   $parentid     父级部门id 必填
     * @param    string                   $order        部门内排序
     * @return   [type]                                 [description]
     * @example
     * $data = {"name": "广州研发中心","parentid": 1,"order": 1,"id": 2}
     */
    public static function createDepartment($access_token, $data)
    {
        if (!isset($data['name']) || !isset($data['parentid'])) {
            return false;
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/department/create?access_token={$access_token}";
        return WeChatHelp::post($url, $data);
    }

    /**
     * [updateDepartment 更新部门]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T09:38:06+0800
     * @param    [type]                   $access_token [description]
     * @param    [type]                   $id           部门id
     * @param    string                   $name         部门名称
     * @param    string                   $parentid     父级部门id
     * @param    string                   $order        部门内排序
     * @return   [type]                                 [description]
     */
    public static function updateDepartment($access_token, $data)
    {
        if (!isset($data['id'])) {
            return false;
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/department/update?access_token={$access_token}";
        return WeChatHelp::post($url, $data);
    }
    /**
     * [getDepartment 查询部门下的子部门 默认查询全部]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T12:32:28+0800
     * @param    [type]                   $access_token [description]
     * @param    string                   $id           [部门id]
     * @return   [type]                                 [description]
     */
    public static function getDepartment($access_token, $id = '')
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token={$access_token}&id={$id}";
        return WeChatHelp::get($url);
    }
    /**
     * [deleteDepartment 删除部门]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T09:36:39+0800
     * @param    [type]                   $access_token [description]
     * @param    [type]                   $id           [部门id]
     * @return   [type]                                 [description]
     * （注：不能删除根部门；不能删除含有子部门、成员的部门）
     */
    public static function deleteDepartment($access_token, $id)
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/department/delete?access_token={$access_token}&id={$id}";
        return WeChatHelp::get($url);
    }
    /**
     * [createUser 创建用户]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T12:43:25+0800
     * @param    [type]                   $access_token [description]
     * @param    [type]                   $data         [用户信息]
     * @return   [type]                                 [description]
     * @example
     *
     */
    public static function createUser($access_token, $data)
    {
        //必填字段 编号 手机 姓名 部门
        if ($data['userid'] && $data['mobile'] && $data['name'] && $data['department']) {
            $url = "https://qyapi.weixin.qq.com/cgi-bin/user/create?access_token={$access_token}";
            return WeChatHelp::post($url, $data);
        } else {
            return false;
        }

    }
    //获取用户信息
    public static function getUserInfo($access_token,$userid)
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}&userid={$userid}";
        return WeChatHelp::get($url);
    }
    //更新成员
    public static function updateUser($access_token,$data)
    {
        if (!isset($data['id'])) {
            return false;
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token={$access_token}";
        return WeChatHelp::post($url,$data);
    }
    //删除成员
    public static function deleteUser($access_token,$userid)
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/delete?access_token={$access_token}&userid={$userid}";
        return WeChatHelp::get($url);
    }
    //批量删除成员
    public static function deleteUsers($access_token,$userids)
    {
        if (count($userids)>200) {
            return false;
        }
        $userids = ['useridlist'=>$useridlist];
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/batchdelete?access_token={$access_token}";
        return WeChatHelp::post($access_token,$userids);
    }
    /**
     * [getUserByDepartment 获取部门成员 默认递归]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T13:10:59+0800
     * @param    [type]                   $access_token [description]
     * @param    integer                  $department_id[description]
     * @param    integer                  $fetch_child  [是否递归]
     * @return   [type]                                 [description]
     */
    public static function getUserByDepartment($access_token,$department_id = 1,$fetch_child = 1)
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token={$access_token}&department_id={$department_id}&fetch_child={$fetch_child}";
        return WeChatHelp::get($url);
    }

    public static function getUserByDepartment($access_token,$department_id = 1,$fetch_child = 1)
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/list?access_token={$access_token}&department_id={$department_id}&fetch_child={$fetch_child}";
        return WeChatHelp::get($url);
    }

    public static function UseridToOpenid($access_token,$userid,$agentid=-1)
    {
        $data['userid'] = $userid;
        $agentid == -1 && $data['agentid'] = $agentid;
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token={$access_token}";
        return WeChatHelp::post($url,$data);
    }
    public static function OpenidToUserid($access_token,$openid)
    {
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid?access_token={$access_token}";
        return WeChatHelp::post($url,['openid'=>$openid]);
    }

    /**
     * [createTag 创建标签]
     * look me baby
     * @Author   DongDavid
     * @DateTime 2017-07-12T16:34:09+0800
     * @param    [type]                   $access_token [description]
     * @param    array                    $data         ['name'=>'','id'=>'']
     * @return   [type]                                 [description]
     */
    public static function createTag($access_token,$data)
    {
    	if (!$data['name']) {
    		return false;
    	}
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/tag/create?access_token={$access_token}";

    	return WeChatHelp::post($url,$data);
    }
    public static function updateTag($access_token,$data)
    {
    	if (!isset($data['id']) || !isset($data['name'])) {
    		return false;
    	}
    	$url="https://qyapi.weixin.qq.com/cgi-bin/tag/update?access_token={$access_token}";
    	return WeChatHelp::post($url,$data);
    }
    public static function getTag($access_token)
    {
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/tag/list?access_token={$access_token}";
    	return WeChatHelp::get($url);
    }
    public static function deleteTag($access_token,$tag_id)
    {
    	$url="https://qyapi.weixin.qq.com/cgi-bin/tag/delete?access_token={$access_token}&tagid={$tag_id}";
    	return WeChatHelp::get($url);
    }
    public static function getUserByTag($access_token,$tag_id)
    {
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/tag/get?access_token={$access_token}&tagid={$tag_id}";
    	return WeChatHelp::get($url);
    }
    public static function addUserToTag($access_token,$tag_id,$user_list=[],$part_list = [])
    {
    	$data = ['tagid'=>$tag_id,'userlist'=>$user_list,'partylist'=>$part_list];
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/tag/addtagusers?access_token={$access_token}";
    	return WeChatHelp::post($url,$data);
    }
    public static function RemoveUserFromTag($access_token,$tag_id,$user_list=[],$part_list=[])
    {
    	$data = ['tagid'=>$tag_id,'userlist'=>$user_list,'partylist'=>$part_list];
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/tag/deltagusers?access_token={$access_token}";
    	return WeChatHelp::post($url,$data);
    }
    /***********************************异步接口***********************************/

    public static function synUpdateUser($access_token,$media_id,$callback = false)
    {
    	// $callback = ['url'=>'','token'=>'','encodingaeskey'=>''];
    	$data = [
    		'media_id'=>$media_id,
    	];
    	$callback && $data['callback'] =$callback;
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/batch/syncuser?access_token={$access_token}";
    	return WeChatHelp::post($url,$data);
    	// ['errcode','errmsg','jobid']  jobid异步任务id，最大长度为64字节
    }
    public static function synReplaceUser($access_token,$media_id,$callback = false)
    {
    	// $callback = ['url'=>'','token'=>'','encodingaeskey'=>''];
    	$data = [
    		'media_id'=>$media_id,
    	];
    	$callback && $data['callback'] =$callback;
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/batch/replaceuser?access_token={$access_token}";
    	return WeChatHelp::post($url,$data);
    	// ['errcode','errmsg','jobid']  jobid异步任务id，最大长度为64字节
    }

    public static function synUpdateDepartment($access_token,$media_id,$callback = false)
    {
    	// $callback = ['url'=>'','token'=>'','encodingaeskey'=>''];
    	$data = [
    		'media_id'=>$media_id,
    	];
    	$callback && $data['callback'] =$callback;
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/batch/syncuser?access_token={$access_token}";
    	return WeChatHelp::post($url,$data);
    	// ['errcode','errmsg','jobid']  jobid异步任务id，最大长度为64字节
    }
    public static function synReplaceDepartment($access_token,$media_id,$callback = false)
    {
    	// $callback = ['url'=>'','token'=>'','encodingaeskey'=>''];
    	$data = [
    		'media_id'=>$media_id,
    	];
    	$callback && $data['callback'] =$callback;
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/batch/replaceparty?access_token={$access_token}";
    	return WeChatHelp::post($url,$data);
    	// ['errcode','errmsg','jobid']  jobid异步任务id，最大长度为64字节
    }
    public static function getSynResult($access_token,$job_id)
    {
    	$url = "https://qyapi.weixin.qq.com/cgi-bin/batch/getresult?access_token={$access_token}&jobid={$job_id}";
    	return WeChatHelp::get($url);
    	/* 	
    		errcode	返回码
			errmsg	对返回码的文本描述内容
			status	任务状态，整型，1表示任务开始，2表示任务进行中，3表示任务已完成
			type	操作类型，字节串，目前分别有：1. sync_user(增量更新成员) 2. replace_user(全量覆盖成员)3. replace_party(全量覆盖部门)
			total	任务运行总条数
			percentage	目前运行百分比，当任务完成时为100
		*/
    }
}
