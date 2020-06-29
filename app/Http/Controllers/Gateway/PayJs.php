<?php

namespace App\Http\Controllers\Gateway;

use App\Models\Payment;
use Auth;
use Response;
use Xhat\Payjs\Payjs as Pay;

class PayJs extends AbstractPayment {
	private static $config;

	function __construct() {
		parent::__construct();
		self::$config = [
			'mchid' => self::$systemConfig['payjs_mch_id'],   // 配置商户号
			'key'   => self::$systemConfig['payjs_key'],   // 配置通信密钥
		];
	}

	public function purchase($request) {
		$payment = $this->creatNewPayment(Auth::id(), $request->input('oid'), $request->input('amount'));

		$result = (new Pay($this::$config))->cashier([
			'body'         => parent::$systemConfig['subject_name']?: parent::$systemConfig['website_name'],
			'total_fee'    => $payment->amount * 100,
			'out_trade_no' => $payment->trade_no,
			'notify_url'   => (parent::$systemConfig['website_callback_url']?: parent::$systemConfig['website_url']).'/callback/notify?method=payjs',
		]);

		// 获取收款二维码内容
		Payment::whereId($payment->id)->update([
			'url'     => $result,
			'qr_code' => 'http://qr.topscan.com/api.php?text='.urlencode($result).'&el=1&w=400&m=10&logo='.parent::$systemConfig['website_url'].'/assets/images/payment/wechat.png'
		]);

		//$this->addPamentCallback($payment->trade_no, null, $payment->amount * 100);
		return Response::json(['status' => 'success', 'data' => $payment->trade_no, 'message' => '创建订单成功!']);
	}

	public function notify($request) {
		$data = (new Pay($this::$config))->notify();

		if($data['return_code'] == 1){
			//			PaymentCallback::query()
			//			               ->whereTradeNo($data['out_trade_no'])
			//			               ->update(['out_trade_no' => $data['payjs_order_id'], 'status' => 1]);
			$this::postPayment($data['out_trade_no'], 'PayJs');
			exit("success");
		}
		exit("fail");
	}
}
