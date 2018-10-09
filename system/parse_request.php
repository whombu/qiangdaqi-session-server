<?php

/**
 * Created by PhpStorm.
 * User: ayisun
 * Date: 2016/10/3
 * Time: 11:22
 */
class parse_request
{

    public function __construct()
    {
        require_once('system/return_code.php');
        require_once('application/controllers/qcloud/minaauth/Auth.php');
        require_once('system/log/log.php');
    }

    /**
     * @param $request_json
     * @return array|int
     * 描述：解析接口名称
     */
    public function parse_json($request_json)
    {
        log_message("info",$request_json);
        if ($this->is_json($request_json)) {
            //echo('1.1');
	    //log_message("info",‘1.1’);
            $json_decode = json_decode($request_json, true);
            if (!isset($json_decode['interface']['interfaceName'])) {
                $ret['returnCode'] = return_code::MA_NO_INTERFACE;
                $ret['returnMessage'] = 'NO_INTERFACENAME_PARA';
                $ret['returnData'] = '';
            } else if (!isset($json_decode['interface']['para'])) {
                $ret['returnCode'] = return_code::MA_NO_PARA;
                $ret['returnMessage'] = 'NO_PARA';
                $ret['returnData'] = '';
            } else {
                if ($json_decode['interface']['interfaceName'] == 'qcloud.cam.id_skey') {
                    if (isset($json_decode['interface']['para']['code'])&&isset($json_decode['interface']['para']['encrypt_data'])) {
                        $code = $json_decode['interface']['para']['code'];
                        $encrypt_data = $json_decode['interface']['para']['encrypt_data'];
                        $auth = new Auth();
                        if(!isset($json_decode['interface']['para']['iv']))
                            $ret = $auth->get_id_skey($code,$encrypt_data);
                        else{
                            $iv = $json_decode['interface']['para']['iv'];
                            $ret = $auth->get_id_skey($code,$encrypt_data,$iv);
                        }
			//log_message("info",‘1.2’);
                        //echo('1.2');
                    } else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = '';
                    }
                } else if ($json_decode['interface']['interfaceName'] == 'qcloud.cam.auth') {
                    if (isset($json_decode['interface']['para']['id']) && isset($json_decode['interface']['para']['skey'])) {
                        $id = $json_decode['interface']['para']['id'];
                        $skey = $json_decode['interface']['para']['skey'];
                        $auth = new Auth();
                        $ret = $auth->auth($id, $skey);
                    } else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = '';
                    }
                } else if ($json_decode['interface']['interfaceName'] == 'qcloud.cam.decrypt') {
                    if (isset($json_decode['interface']['para']['id']) && isset($json_decode['interface']['para']['skey']) && isset($json_decode['interface']['para']['encrypt_data'])) {
                        $id = $json_decode['interface']['para']['id'];
                        $skey = $json_decode['interface']['para']['skey'];
                        $encrypt_data = $json_decode['interface']['para']['encrypt_data'];
                        $auth = new Auth();
                        $ret = $auth->decrypt($id, $skey, $encrypt_data);
                    } else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = '';
                    }
                }else if($json_decode['interface']['interfaceName'] == 'qcloud.cam.initdata'){
                    if (isset($json_decode['interface']['para']['appid']) && isset($json_decode['interface']['para']['secret']) && isset($json_decode['interface']['para']['qcloud_appid']) && isset($json_decode['interface']['para']['ip'])
                        && isset($json_decode['interface']['para']['cdb_ip'])&& isset($json_decode['interface']['para']['cdb_port']) && isset($json_decode['interface']['para']['cdb_user_name'])&& isset($json_decode['interface']['para']['cdb_pass_wd']) ) {
                        $appid = $json_decode['interface']['para']['appid'];
                        $secret = $json_decode['interface']['para']['secret'];
                        $qcloud_appid = $json_decode['interface']['para']['qcloud_appid'];
                        $ip = $json_decode['interface']['para']['ip'];
                        $cdb_ip = $json_decode['interface']['para']['cdb_ip'];
                        $cdb_port = $json_decode['interface']['para']['cdb_port'];
                        $cdb_user_name = $json_decode['interface']['para']['cdb_user_name'];
                        $cdb_pass_wd = $json_decode['interface']['para']['cdb_pass_wd'];
                        $auth = new Auth();
                        $ret = $auth->init_data($appid,$secret,$qcloud_appid,$ip,$cdb_ip,$cdb_port,$cdb_user_name,$cdb_pass_wd);
                    } else {
                        $ret['returnCode'] = return_code::MA_PARA_ERR;
                        $ret['returnMessage'] = 'PARA_ERR';
                        $ret['returnData'] = '';
                    }
                } else {
                    $ret['returnCode'] = return_code::MA_INTERFACE_ERR;
                    $ret['returnMessage'] = 'INTERFACENAME_PARA_ERR';
                    $ret['returnData'] = '';
                }
            }
        } else {
            $ret['returnCode'] = return_code::MA_REQUEST_ERR;
            $ret['returnMessage'] = 'REQUEST_IS_NOT_JSON';
            $ret['returnData'] = '';
        }
        $ret['version'] = 1;
        $ret['componentName'] = "MA";
        log_message("info",json_encode($ret));
        return json_encode($ret);
    }

    /**
     * @param $str
     * @return bool
     * 描述：判断字符串是不是合法的json
     */
    private function is_json($str)
    {
        json_decode($str);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
