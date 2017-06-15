<?php
/**
 * TSP订票选座接口
 * @version 1.01
 * @var data 2016/7/30
 * @author   esyy <esyy@qq.com>
  生产环境的账号已经开通：
 */
class TspapiModel extends Model{
	
	/* 测试
	*/
	private $wsdl = 'http://#############:####/#############/tsp/cinema?wsdl';
	private $pa = array(
		'appCode'	=> '###############',
		'key'		=> '############',
		'compress'	=> '0',
	);
	/* 生产环境的址：
	
	private $wsdl = 'http://####################/tsp/cinema?wsdl';
	private $pa = array(
		'appCode'	=> '####################',
		'key'		=> '######################',
		'compress'	=> '0',
	);*/
	/**
	 * 查询影厅获取座位信息
	 * @param string $param
	 * @return unknown
	 */
	public function QuerySeatInfo($cinemaCode,$pScreenCode){
		$pa = $this->pa;
		$param = array(
			'pAppCode'=>$pa['appCode'], 
			'pCinemaCode'=>$cinemaCode, 
			'pScreenCode'=>$pScreenCode, 
			'pCompress'=>$pa['compress'], 
			//'pVerifyInfo'=>$pVerifyInfo
		);
		$signstr = '';
		foreach($param as $v){
			$signstr .=$v;
		}
		$param['pVerifyInfo'] = md5(strtolower($signstr.$pa['key']));
		$result = $this->getData('QuerySeatInfo',$param,'QuerySeatInfoResult');
		return $result;
		
	}
	
	/*
	4.1.6 QueryPlanSeat查询放映计划座位售出状态
	接口地址	[接口位置]/ QueryPlanSeat
	接口功能	查询放映计划座位售出状态
		参数名	类型[最大长度]	说明
	1	pAppCode	String	应用编码
	2	pCinemaCode	String	电影院编码
	3	pFeatureAppNo	String	放映计划编码（不能为空）
	4	pStatus	String	座位售出状态：All，所有
				  Available，可售出
				  Locked，已锁定
				  Sold，已售出
				  Booked，已预订
				  Unavailable，不可用
	5	pCompress	String	是否压缩(0：不压缩 1：压缩)
	6	pVerifyInfo	String	检验信息
	*/
	public function QueryPlanSeat($pCinemaCode,$pFeatureAppNo){
		$pa = $this->pa;
		$pStatus = 'All';
		$param = array(
			'pAppCode'		=>$pa['appCode'], 
			'pCinemaCode'	=>$pCinemaCode, 
			'pFeatureAppNo'	=>$pFeatureAppNo, 
			'pStatus'		=>$pStatus, 
			'pCompress'		=>$pa['compress'], 
		);
		$signstr = '';
		foreach($param as $v){
			$signstr .=$v;
		}
		$param['pVerifyInfo'] = md5(strtolower($signstr.$pa['key']));
		$seatArr = $this->getData('QueryPlanSeat',$param,'QueryPlanSeatResult');
		return $seatArr;
	}
	
	//4.1.7 LockSeat锁定座位
	/*
	CinemaCode	String	影院编码
	FeatureAppNo	String	放映计划编码
	SeatCode	String	座位编码
	*/
	public function LockSeat($CinemaCode,$FeatureAppNo,$SeatCode=array()){
		$pa = $this->pa;
		$signstr = '';$SeatCodes='';
		foreach($SeatCode as $v){
			$v = str_replace('_','#',$v);
			$signstr .= '<SeatCode>'.$v.'</SeatCode>';
			$SeatCodes.=$v;
		}
		$VerifyInfo = md5(strtolower($pa['appCode'].$CinemaCode.$FeatureAppNo.$SeatCodes.$pa['compress'].$pa['key']));
		$param = array(
		'LockSeatXml'=>'<?xml version="1.0" encoding="UTF-8"?>
		<LockSeatParameter>
			<AppCode>'.$pa['appCode'].'</AppCode>
			<CinemaCode>'.$CinemaCode.'</CinemaCode>
			<Compress>0</Compress>
			<SeatInfos>
				'.$signstr.'
			</SeatInfos>
			<VerifyInfo>'.$VerifyInfo.'</VerifyInfo>
			<FeatureAppNo>'.$FeatureAppNo.'</FeatureAppNo>
		</LockSeatParameter>'
		);
		$seatArr = $this->getData('LockSeat',$param,'LockSeatResult');
		return $seatArr;
	}
	/*
		4.1.8 ReleaseSeat解锁座位
	接口地址	[接口位置]/ ReleaseSeat
	接口功能	解锁座位
		参数名	类型[最大长度]	说明
	1	ReleaseSeatXml	String	XML格式的解锁字符串（拆分成下面参数来生成校验信息）
	1.1	AppCode	String	应用编码
	1.2	CinemaCode	String	影院编码
	1.3	OrderCode	String	订单编码
	1.4	FeatureAppNo	String	放映计划编码
	1.5	SeatCode	String	座位编码
	1.6	Compress	String	是否压缩(0：不压缩 1：压缩)
	1.7	VerifyInfo	String	检验信息
	*/
	public function ReleaseSeat($CinemaCode,$OrderCode,$FeatureAppNo,$SeatCode=array()){
		$pa = $this->pa;
		$signstr = '';$SeatCodes='';
		foreach($SeatCode as $v){
			$v = str_replace('_','#',$v);
			$signstr .= '<SeatCode>'.$v.'</SeatCode>';
			$SeatCodes.=$v;
		}
		$VerifyInfo = md5(strtolower($pa['appCode'].$CinemaCode.$OrderCode.$FeatureAppNo.$SeatCodes.$pa['compress'].$pa['key']));
		$param = array(
		'ReleaseSeatXml'=>'<?xml version="1.0" encoding="UTF-8"?>
		<ReleaseSeatParameter>
			<AppCode>'.$pa['appCode'].'</AppCode>
			<CinemaCode>'.$CinemaCode.'</CinemaCode>
			<OrderCode>'.$OrderCode.'</OrderCode>
			<Compress>0</Compress>
			<SeatInfos>
				'.$signstr.'
			</SeatInfos>
			<VerifyInfo>'.$VerifyInfo.'</VerifyInfo>
			<FeatureAppNo>'.$FeatureAppNo.'</FeatureAppNo>
		</ReleaseSeatParameter>'
		);
		$seatArr = $this->getData('ReleaseSeat',$param,'ReleaseSeatResult');
		return $seatArr;
	}
	
	//4.1.9 SubmitOrder确认订单交易
	/*
	1.1	AppCode	String	应用编码
	1.2	CinemaCode	String	电影院编码
	1.3	OrderCode	String	订单编码
	1.4	FeatureAppNo	String	放映计划编码
	1.5	MobilePhone	String	手机号码
	1.6	SeatInfo节点
	1.6.1	SeatCode	String	座位编码
	1.6.2	Price	Float	票价（如果ServiceCharge节点为空，拆分票价方法是：Price - 渠道商StandardPrice = 网络代售服务费）
	1.6.3	ServiceCharge	Float	网络代售服务费（如果传入该节点，拆分票价方法是：Price - ServiceAddFee - ServiceCharge = 票价
	1.6.4	ServiceAddFee	Float	增值服务费
	*/
	public function SubmitOrder($orderInfo,$SeatInfo,$MemberInfo='', $SaleMerInfos='', $ActivieInfo='', $PaymentInfo=''){
		$pa = $this->pa;
		$md5str = $pa['appCode'].$orderInfo['CinemaCode'].$orderInfo['OrderCode'].$orderInfo['FeatureAppNo'].$orderInfo['MobilePhone'];
		$SeatCodexml='';
		foreach($SeatInfo as $v){
			$v['SeatCode'] = str_replace('_','#',$v['SeatCode']);
			$SeatCodexml .= '
			    <SeatInfo>
 			     <SeatCode>'.$v['SeatCode'].'</SeatCode>
			     <Price>'.$v['Price'].'</Price>
				 <ServiceCharge>'.$v['ServiceCharge'].'</ServiceCharge>
				 <ServiceAddFee>'.$v['ServiceAddFee'].'</ServiceAddFee>
				 <CinemaAllowance>'.$v['CinemaAllowance'].'</CinemaAllowance>
			    </SeatInfo>';
				// <SeqNo>C201230120</SeqNo>
			$md5str.=$v['SeatCode'].$v['Price'].$v['ServiceCharge'].$v['ServiceAddFee'].$v['CinemaAllowance'];
		}
		
		$VerifyInfo = md5(strtolower($md5str.$pa['compress'].$pa['key']));
		
		$param = array(
			'SubmitOrderXml'=>'<?xml version="1.0" encoding="UTF-8"?>
			<SubmitOrderParameter> 
		    <AppCode>'.$pa['appCode'].'</AppCode>
			<CinemaCode>'.$orderInfo['CinemaCode'].'</CinemaCode>
			<OrderCode>'.$orderInfo['OrderCode'].'</OrderCode>
			<FeatureAppNo>'.$orderInfo['FeatureAppNo'].'</FeatureAppNo>
            <MobilePhone>'.$orderInfo['MobilePhone'].'</MobilePhone>
			 <SeatInfos>
			    '.$SeatCodexml.'
			 </SeatInfos>
            <Compress>'.$pa['compress'].'</Compress>
		    <VerifyInfo>'.$VerifyInfo.'</VerifyInfo>
            </SubmitOrderParameter>', 
		);
		//print_r($param);
		$seatArr = $this->getData('SubmitOrder',$param,'SubmitOrderResult');
		return $seatArr;
	}
	
	/*4.1.10 QueryOrderStatus查询订单交易状态
	接口地址	[接口位置]/QueryOrderStatus
	接口功能	根据查询交易状态
		参数名	类型[最大长度]	说明
	1	pAppCode	String	应用编码
	2	pCinemaCode	String	电影院编码
	3	pOrderCode	String	订单编码
	4	pCompress	String	是否压缩(0：不压缩 1：压缩)
	5	pVerifyInfo	String	检验信息
	*/
	public function QueryOrderStatus($pCinemaCode,$pOrderCode){
		
		$pa = $this->pa;
		$param = array(
			'pAppCode'=>$pa['appCode'], 
			'pCinemaCode'=>$pCinemaCode, 
			'pOrderCode' =>$pOrderCode, 
			'pCompress'  =>$pa['compress'], 
		);
		$signstr = '';
		foreach($param as $v){
			$signstr .=$v;
		}
		$param['pVerifyInfo'] = md5(strtolower($signstr.$pa['key']));
		$seatArr = $this->getData('QueryOrderStatus',$param,'QueryOrderStatusResult');
		return $seatArr;
	}
	
	/*4.1.11 CancelOrder取消交易订单
	接口地址	[接口位置]/CancelOrder
	接口功能	根据取票序号、取票验证码取消交易订单
		参数名	类型[最大长度]	说明
	1	pAppCode	String	应用编码
	2	pCinemaCode	String	电影院编码
	3	pPrintNo	String	取票序号
	4	pVerifyCode	String	取票验证码
	5	pCompress	String	是否压缩(0：不压缩 1：压缩)
	6	pVerifyInfo	String	检验信息
	*/
	public function CancelOrder($pCinemaCode,$pPrintNo,$pVerifyCode){
		
		$pa = $this->pa;
		$param = array(
			'pAppCode'=>$pa['appCode'], 
			'pCinemaCode'=>$pCinemaCode, 
			'pPrintNo' =>$pPrintNo, 
			'pVerifyCode' =>$pVerifyCode, 
			'pCompress'  =>$pa['compress'], 
		);
		$signstr = '';
		foreach($param as $v){
			$signstr .=$v;
		}
		$param['pVerifyInfo'] = md5(strtolower($signstr.$pa['key']));
		$seatArr = $this->getData('CancelOrder',$param,'CancelOrderResult');
		return $seatArr;
	}	
		
	/**
	* ------------------------------------------------------
	* 请求地址获取返回数据
	* @param str method 地址方法
	* @param array param 传入参数
	* @param array result 返回数据对象中包含有效信息的属性名
	* @return 
	* ------------------------------------------------------
	*/
	private function getData($method,$param=false,$result){
        //实例化对象
        $client=new SoapClient($this->wsdl);
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8 = true;
		//$signstr = '';//$pa['appCode'];
		//foreach($param as $v){
		//	$signstr .= $v;
		//}
		//$pVerifyInfo = md5(strtolower($signstr.$pa['compress'].$pa['key']));
		//接口方法
        $res = $client->$method($param);
		//$xml = $this->toXml($res, $result);
		$data  = simplexml_load_string($res->return);
		$res = $this->object_array($data);
	    //var_dump($res);
		//$data  = simplexml_load_string($xml);
		//file_put_contents(dirname(__FILE__).'/'.$method.date('Ymd').'.log',date("Y-m-d H:i:s")."\r\n".$method.'result:'.$str,FILE_APPEND);
	    //$data = json_decode(json_encode($xml),true);
		@file_put_contents(C('LOG_DIR').'TSPInterface_'.date('Ymd').'.log',date("Y-m-d H:i:s")."_method:".$method."\r\n".var_export($param, true)."\r\n".var_export($data, true)."\n\r",FILE_APPEND);
		return $res;
	}
	
	// 对象转数组
	private function object_array($array) {  
		if(is_object($array)) {  
			$array = (array)$array;  
		 } if(is_array($array)) {  
			 foreach($array as $key=>$value) {  
				 $array[$key] = $this->object_array($value);  
				 }  
		 }  
		 return $array;  
	}

	// 数组转xml
	private function toXml($data, $rootNodeName, $xml=null)		{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1){
			ini_set ('zend.ze1_compatibility_mode', 0);
		}
		
		if ($xml == null){
			$xml = simplexml_load_string("<?xml version='1.0' encoding='UTF-8'?><$rootNodeName/>");
		}
		// loop through the data passed in.
		foreach($data as $key => $value){
			// no numeric keys in our xml please!
			if (is_numeric($key)) {
				// make string key...
				$key = "unknownNode_". (string) $key;
			}				
			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z]/i', '', $key);				
			// if there is another array found recrusively call this function
			if (is_array($value)) {
				$node = $xml->addChild($key);
				// recrusive call.
				$this->toXml($value, $rootNodeName, $node);
			}
			else {
				$value = htmlentities($value, ENT_COMPAT, "UTF-8");
				$xml->addChild($key,$value);
			}				
		}
		return $xml->asXML();
	}
}
