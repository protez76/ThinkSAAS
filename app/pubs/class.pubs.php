<?php 
defined('IN_TS') or die('Access Denied.');
class pubs extends tsApp{

	//构造函数
	public function __construct($db){
        $tsAppDb = array();
		include 'app/pubs/config.php';
		//判断APP是否采用独立数据库
		if($tsAppDb){
			$db = new MySql($tsAppDb);
		}
		parent::__construct($db);
	}

    /**
     * 解密userkey,加验证userid
     * @param $userkey
     * @return string
     */
    public function getUserId($userkey){
        include 'thinksaas/class.crypt.php';
        $crypt= new crypt();
        $userid = $crypt->decrypt($userkey,$GLOBALS['TS_SITE']['site_pkey']);
        $isUser = $this->findCount('user',array(
            'userid'=>$userid,
        ));
        if($isUser == 0){
            echo json_encode(array(
                'status'=> 0,
                'msg'=> '非法操作',
                'data'=> '',
            ));
            exit;
        }else{
            return $userid;
        }
    }

    /**
     * @param $string
     * @param string $action
     * @return string
     */
    public function strCode($string, $action = 'ENCODE'){
        $action != 'ENCODE' && $string = base64_decode($string);
        $code = '';
        $key = $GLOBALS['TS_SITE']['site_pkey'];
        $keyLen = strlen($key);
        $strLen = strlen($string);
        for ($i = 0; $i < $strLen; $i++) {
            $k = $i % $keyLen;
            $code .= $string[$i] ^ $key[$k];
        }
        return ($action != 'DECODE' ? base64_encode($code) : $code);
    }

    /**
     * @param $phone
     * @param $code
     * @return bool
     */
    public function verifyPhoneCode($phone, $code){
        $strPhoneCode = $this->find('phone_code',array(
            'phone'=>$phone,
        ));

        #空数据
        if($strPhoneCode==''){
            return false;exit;
        }

        #空验证码
        if($strPhoneCode['code']==''){
            return false;exit;
        }

        #手机验证码错误次数>=2
        if($strPhoneCode['nums']>=2){
            $this->update('phone_code',array(
                'phone'=>$phone,
            ),array(
                'code'=>'',
                'nums'=>0,
            ));
            return false;exit;
        }

        #手机验证码错误
        if($strPhoneCode['code']!=$code){
            $this->update('phone_code',array(
                'phone'=>$phone,
            ),array(
                'nums'=>$strPhoneCode['nums']+1,
            ));
            return false;exit;
        }
        return true;
    }

}