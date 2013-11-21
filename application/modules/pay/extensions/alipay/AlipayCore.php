<?php
/**
 * file: AlipayCore.php
 * author: Toruneko<toruneko@outlook.com>
 * date: 2013-11-16
 * desc: 该类是请求、通知返回两个文件所调用的公用函数核心处理文件
 */
class AlipayCore{
	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param array $para 需要拼接的数组
	 * @return string 拼接完成以后的字符串
	 */
	public static function createLinkstring($para){
		$arg  = array();
		foreach($para as $key => $val){
			$arg[] = $key."=".$val;
		}
		$arg = join('&',$arg);
		
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){
			$arg = stripslashes($arg);
		}
		
		return $arg;
	}
	
	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
	 * @param array $para 需要拼接的数组
	 * @return string 拼接完成以后的字符串
	 */
	public static function createLinkstringUrlencode($para) {
		$arg  = array();
		foreach($para as $key => $val){
			$arg[] = $key."=".urlencode($val);
		}
		$arg = join('&',$arg);
	
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){
			$arg = stripslashes($arg);
		}
	
		return $arg;
	}
	
	/**
	 * 除去数组中的空值和签名参数
	 * @param array $para 签名参数组
	 * @return array 去掉空值与签名参数后的新签名参数组
	 */
	public static function paraFilter($para) {
		$para_filter = array();
		foreach($para as $key => $val){
			if($key == "sign" || $key == "sign_type" || $val == "") continue;
			$para_filter[$key] = $val;
		}
		return $para_filter;
	}
	
	/**
	 * 对数组排序
	 * @param array $para 排序前的数组
	 * @return array 排序后的数组
	 */
	public static function argSort($para) {
		ksort($para);
		reset($para);
		return $para;
	}
	
	/**
	 * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
	 * 注意：服务器需要开通fopen配置
	 * @param string $word 要写入日志里的文本内容 默认值：空值
	 */
	public static function logResult($word='') {
		$fp = fopen("log.txt","a");
		flock($fp, LOCK_EX) ;
		fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	
	/**
	 * 远程获取数据，POST模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param string $url 指定URL完整路径地址
	 * @param string $cacert_url 指定当前工作目录绝对路径
	 * @param string $para 请求的数据
	 * @param string $input_charset 编码格式。默认值：空值
	 * @return mixed 远程输出的数据
	 */
	public static function getHttpResponsePOST($url, $cacert_url, $para, $input_charset = '') {
	
		if (trim($input_charset) != '') {
			$url = $url."_input_charset=".$input_charset;
		}
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
		curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$para);// post传输数据
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
	
		return $responseText;
	}
	
	/**
	 * 远程获取数据，GET模式
	 * 注意：
	 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
	 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
	 * @param string $url 指定URL完整路径地址
	 * @param string $cacert_url 指定当前工作目录绝对路径
	 * @return mixed 远程输出的数据
	 */
	public static function getHttpResponseGET($url,$cacert_url) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
		curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址
		$responseText = curl_exec($curl);
		//var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
		curl_close($curl);
	
		return $responseText;
	}
	
	/**
	 * 实现多种字符编码方式
	 * @param string $input 需要编码的字符串
	 * @param string $_output_charset 输出的编码格式
	 * @param string $_input_charset 输入的编码格式
	 * @return string 编码后的字符串
	 */
	public static function charsetEncode($input,$_output_charset ,$_input_charset) {
		$output = "";
		if(!isset($_output_charset)){
			$_output_charset  = $_input_charset;
		}
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else {
			die("sorry, you have no libs support for charset change.");
		}
		return $output;
	}
	
	/**
	 * 实现多种字符解码方式
	 * @param string $input 需要解码的字符串
	 * @param string $_input_charset 输出的解码格式
	 * @param string $_output_charset 输入的解码格式
	 * @return string 解码后的字符串
	 */
	public static function charsetDecode($input,$_input_charset ,$_output_charset) {
		$output = "";
		if(!isset($_input_charset)){
			$_input_charset  = $_output_charset;
		}
		if($_input_charset == $_output_charset || $input ==null ) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")) {
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else {
			die("sorry, you have no libs support for charset changes.");
		}
		return $output;
	}
}