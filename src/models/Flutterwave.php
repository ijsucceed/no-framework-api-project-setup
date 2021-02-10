<?php
use Curl\Curl;

class Flutterwave
{
    /**
     * URL of the payment processor
     */
    public string $URL;
    
    /**
     * Secret/Private key of the merchant
     * 
     */
    private string $secretKey;

    /**
     * The request header of the payment processor
     * 
     * @since 0.5
     */
    protected array $headers;

    /**
     * Flutterwave supported currencies
     */
    protected array $currencies = [
        'NGN', 'GHS', 'RND', 'USD'
    ];

    /**
     * Curl request
     * 
     * @var Curl
     */
    protected $request;

    /**
     * The response body per request sent
     * 
     * @since 0.5
     */
    public object $response;

    /**
     * The response status a processor request
     */
    public bool $status = false;

    /**
     * The response message of a request
     */
    public string $message = 'Nothing found';

    /**
     * Default bills we support from flutterwave
     */
    public array $bills_supported = [ 'airtime', 'power', 'data', 'cable' ];

    /**
     * Constructor
     * 
     * @since 0.5
     */
    public function __construct(string $secretKey = '')
    {
        if( empty($secretKey) ) {
            $this->secretKey = appEnv('WAVE_SECRET');
        }
        else {
            $this->secretKey = $secretKey;
        }

        $this->URL = appEnv('WAVE_URL');

        $this->setHeaders([
            'Authorization' => sprintf('Bearer %s', $this->secretKey),
            'Content-Type'  => 'application/json',
        ]);

        $this->request = new Curl();
        $this->request->setHeaders( $this->getHeaders() );
    }

    /**
     * set request with required headers
     *
     * @return void
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Set the default currency
     * 
     * @since 0.5
     */
    public function getCurrencies(): array
    {
        return $currencies;
    }

      /**
     * Get the secret/private key of the merchant 
     * 
     * @since 0.5
     */
    public function getSecretKey(): string
    {
        return $this->secretKey ?? '';
    }

    /**
     * Get the URL of the gateway 
     * 
     * @since 0.5
     */
    public function getURL(): string
    {
        return $this->URL ?? '';
    }

    /**
     * Get the request headers of the processor
     * 
     * @since 0.5
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get bank lists
     *
     */
    public function bankList(): void
    {
        $endpoint = $this->URL . '/banks/ng'; // nigeria bank list endpoint

        $this->request($endpoint, [], 'get'); // request the bank list
    }

    /**
     * Resolve a user bank account
     *
     */
    public function accountResolve( string $account_number, string $bank_code ): void
    {
        $endpoint = $this->URL . '/accounts/resolve'; // account resolve endpoint

        // make request to the account resolve endpoint
        $this->request($endpoint, [
            'account_number' => $account_number,
            'account_bank' => $bank_code
        ], 'post');
    }

    /**
     * Resolve a person BVN and get info
     * 
     */
    public function bvnResolve( string $bvn ): void
    {
        $endpoint = $this->URL . '/kyc/bvns/' . $bvn; // account resolve endpoint

        // make request to the account resolve endpoint
        $this->request($endpoint, [], 'get');
    }

    function virtualAccountCreate(array $user_info): void
    {
        $endpoint = $this->URL . '/virtual-account-numbers'; // account resolve endpoint

        // $txt_ref = strtolower(app_slugify(sprintf(
        //     '%s %s %s',
        //     $user_info['firstname'],
        //     $user_info['lastname'],
        //     time()
        // )));

        // create virtual account
        $data = [
            "email"=> $user_info['email'],
            "is_permanent"=> true,
            "tx_ref"=> $user_info['tx_ref']
        ];

        // create virtual accounts
        $this->request($endpoint, $data, 'post');
    }

    function virtualAccountGet(string $order_ref): void
    {
        $this->setEndpoint("/virtual-account-numbers/order_ref/$order_ref");

        // create virtual accounts
        $this->request($this->endpoint, [], 'get');
    }

    /**
     * Transfer money to a designated bank account
     * @param $bank_code is the bank code
     */
    public function bankTransfer(array $body): void
    {
        $endpoint = $this->URL . '/transfers'; // transfer endpoint

        // make the transfer
        $this->request($endpoint, $body, 'post');
    }

    public function billsCategories(array $type): void
    {
        $endpoint = $this->URL . '/bill-categories';

        // get all bills categories
        $this->request($endpoint, $type, 'get');
    }

    public function bills(array $body): void
    {
        $endpoint = $this->URL . '/bills';

        // get all bills categories
        $this->request($endpoint, $body, 'post');
    }

    function request(string $url, array $body, string $method = 'post'): void
    {
        $curl = new Curl();
        $curl->setHeaders([
            'Authorization' => 'Bearer ' . $this->secretKey,
            'Content-Type' => 'application/json'
        ]);

        try {
            // make request to the account resolve endpoint
            $curl->$method( $url, $body ); // method 

            if ( $curl->error ) {
                // set the flutterwave message
                $this->message = $curl->response->message ?? 'External service unavailable';

                app_log_error( 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage );
            }
            else {
                $this->message = $curl->response->message ?? ' '; // set the flutterwave message
            }

            if( is_object($curl->response) ) {
                $this->response = $curl->response;
            } else {
                $this->response = (object) [
                    'message' => 'Something went wrong connecting to flutterwave'
                ];
            }

            if( 'success' === $curl->response->status ?? null ) {
                $this->status = true; // set the status
            }
        }
        catch(Exception $e) {
            // set the flutterwave message
            $this->message = 'Error occur requesting Flutterwave service';
            app_log_error( 'Error occur requesting Flutterwave service' );
        }
    }
}