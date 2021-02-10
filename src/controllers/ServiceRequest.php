<?php
class ServiceRequest extends Request
{
    /**
	 * Fetch bank list from the API
	 */
	public function bankList() : void
	{
		$flutterwave = new Flutterwave();
		$flutterwave->bankList();

		if( false === $flutterwave->status ) {
			app_false_response($flutterwave->message, 400);
		}

		app_true_response($flutterwave->message, [
			'banks' => $flutterwave->response,
		]);
	}

	/**
	 * Resolve a bank account
	 */
	public function accountResolve() : void
	{
		$this->validateJson();

		$req = $this->request;

		if( ! app_properties_found($req, [
			'account_number',
			'bank_code'
		]))
		{
			app_false_response('Required parameter mising', 400);
		}

		$account_number = app_clean_input($req->account_number);
		$bank_code = app_clean_input($req->bank_code);

		$flutterwave = new Flutterwave();
		$flutterwave->accountResolve( $account_number, $bank_code );

		if( false === $flutterwave->status ) {
			app_false_response($flutterwave->message, 400);
		}

		app_true_response($flutterwave->message, [
			'account' => $flutterwave->response,
		]);
	}
}