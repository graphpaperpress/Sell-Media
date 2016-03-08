<?php
/**
 *  PayPal IPN Listener
 *
 *  A class to listen for and handle Instant Payment Notifications (IPN) from
 *  the PayPal server.
 *
 *  Forked from the great Quixotix PayPal IPN script. This fork plans to
 *  fix the current issues with the original repo, as well as update the code
 *  for use according to PayPal's documentation, and today's standards.
 *
 *  @package    PHP-PayPal-IPN
 *  @link       https://github.com/WadeShuler/PHP-PayPal-IPN
 *  @forked     https://github.com/Quixotix/PHP-PayPal-IPN
 *  @author     Wade Shuler
 *  @copyright  Copyright (c) 2015, Wade Shuler
 *  @license    http://choosealicense.com/licenses/gpl-2.0/
 *  @version    2.2.0
 */

class IPNListener
{

    /**
     *  If true, the recommended cURL PHP library is used to send the post back
     *  to PayPal. If flase then fsockopen() is used. Default true.
     *
     *  @var boolean
     */
    public $use_curl = true;

    /**
     *  If true, cURL will use the CURLOPT_FOLLOWLOCATION to follow any
     *  "Location: ..." headers in the response.
     *
     *  @var boolean
     */
    public $follow_location = false;

    /**
     *  If true, an SSL secure connection (port 443) is used for the post back
     *  as recommended by PayPal. If false, a standard HTTP (port 80) connection
     *  is used. Default true.
     *
     *  @var boolean
     */
    public $use_ssl = true;

    /**
     *  If true, the paypal sandbox URI www.sandbox.paypal.com is used for the
     *  post back. If false, the live URI www.paypal.com is used. Default false.
     *
     *  @var boolean
     */
    public $use_sandbox = false;

    /**
     *  The amount of time, in seconds, to wait for the PayPal server to respond
     *  before timing out. Default 30 seconds.
     *
     *  @var int
     */
    public $timeout = 30;

	private $_errors = array();
    private $post_data;
	private $rawPostData;				// raw data from php://input
    private $post_uri = '';
    private $response_status = '';
    private $response = '';

    const PAYPAL_HOST = 'www.paypal.com';
    const SANDBOX_HOST = 'www.sandbox.paypal.com';

    /**
     *  Post Back Using cURL
     *
     *  Sends the post back to PayPal using the cURL library. Called by
     *  the processIpn() method if the use_curl property is true. Throws an
     *  exception if the post fails. Populates the response, response_status,
     *  and post_uri properties on success.
     *
	 *  @todo add URL param so function is more dynamic
	 *
     *  @param  string  The post data as a URL encoded string
     */
    protected function curlPost($encoded_data)
	{

        if ($this->use_ssl) {
            $uri = 'https://'.$this->getPaypalHost().'/cgi-bin/webscr';
            $this->post_uri = $uri;
        } else {
            $uri = 'http://'.$this->getPaypalHost().'/cgi-bin/webscr';
            $this->post_uri = $uri;
        }

        $ch = curl_init();

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cert/api_cert_chain.crt');
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->follow_location);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: sell-media-plugin'));

        $this->response = curl_exec($ch);
        $this->response_status = strval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        if ($this->response === false || $this->response_status == '0') {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            throw new Exception("cURL error: [$errno] $errstr");
        }

		return $this->response;		// function should return it's data itself
    }

    /**
     *  Post Back Using fsockopen()
     *
     *  Sends the post back to PayPal using the fsockopen() function. Called by
     *  the processIpn() method if the use_curl property is false. Throws an
     *  exception if the post fails. Populates the response, response_status,
     *  and post_uri properties on success.
     *
	 *  @todo add URL param so function is more dynamic
	 *
     *  @param  string  The post data as a URL encoded string
     */
    protected function fsockPost($encoded_data)
	{

        if ($this->use_ssl) {
            $uri = 'ssl://'.$this->getPaypalHost();
            $port = '443';
            $this->post_uri = $uri.'/cgi-bin/webscr';
        } else {
            $uri = $this->getPaypalHost(); // no "http://" in call to fsockopen()
            $port = '80';
            $this->post_uri = 'http://'.$uri.'/cgi-bin/webscr';
        }

        $fp = fsockopen($uri, $port, $errno, $errstr, $this->timeout);

        if (!$fp) {
            // fsockopen error
            throw new Exception("fsockopen error: [$errno] $errstr");
        }

        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Host: ".$this->getPaypalHost()."\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen($encoded_data)."\r\n";
        $header .= "Connection: Close\r\n\r\n";

        fputs($fp, $header.$encoded_data."\r\n\r\n");

        while(!feof($fp)) {
            if (empty($this->response)) {
                // extract HTTP status from first line
                $this->response .= $status = fgets($fp, 1024);
                $this->response_status = trim(substr($status, 9, 4));
            } else {
                $this->response .= fgets($fp, 1024);
            }
        }

        fclose($fp);
		return $this->response;		// function should return it's data itself
    }

    private function getPaypalHost()
	{
		return ($this->use_sandbox) ? self::SANDBOX_HOST : self::PAYPAL_HOST;
    }

	public function getErrors()
	{
		return $this->_errors;
	}

	private function addError($error)
	{
		$this->_errors[] .= $error;
	}

	public function getPostData()
	{
		return $this->post_data;
	}

	public function getRawPostData()
	{
		return $this->rawPostData;
	}

    /**
     *  Get POST URI
     *
     *  Returns the URI that was used to send the post back to PayPal. This can
     *  be useful for troubleshooting connection problems. The default URI
     *  would be "ssl://www.sandbox.paypal.com:443/cgi-bin/webscr"
     *
     *  @return string
     */
    public function getPostUri()
	{
        return $this->post_uri;
    }

    /**
     *  Get Response
     *
     *  Returns the entire response from PayPal as a string including all the
     *  HTTP headers.
     *
     *  @return string
     */
    public function getResponse()
	{
        return $this->response;
    }

    /**
     *  Get Response Status
     *
     *  Returns the HTTP response status code from PayPal. This should be "200"
     *  if the post back was successful.
     *
     *  @return string
     */
    public function getResponseStatus()
	{
        return $this->response_status;
    }

    /**
     *  Get Text Report
     *
     *  Returns a report of the IPN transaction in plain text format. This is
     *  useful in emails to order processors and system administrators. Override
     *  this method in your own class to customize the report.
     *
     *  @return string
     */
    public function getTextReport()
	{

        $r = '';

        // date and POST url
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n[".date('m/d/Y g:i A').'] - '.$this->getPostUri();
        if ($this->use_curl) $r .= " (curl)\n";
        else $r .= " (fsockopen)\n";

        // HTTP Response
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n{$this->getResponse()}\n";

        // POST vars
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n";

        foreach ($this->post_data as $key => $value) {
            $r .= str_pad($key, 25)."$value\n";
        }
        $r .= "\n\n";

        return $r;
    }

    /**
     *  Process IPN
     *
     *  Handles the IPN post back to PayPal and parsing the response. Call this
     *  method from your IPN listener script. Returns true if the response came
     *  back as "VERIFIED", false if the response came back "INVALID", and
     *  throws an exception if there is an error.
     *
     *  @param array
     *
     *  @return boolean
     */
    public function processIpn($post_data=null)
	{
		try
		{
			$this->requirePostMethod();		// processIpn() should check itself if data is POST

			// Read POST data
			// reading posted data directly from $_POST causes serialization
			// issues with array data in POST. Reading raw POST data from input stream instead.
			if ($post_data === null) {
				$raw_post_data = file_get_contents('php://input');
			} else {
				$raw_post_data = $post_data;
			}
			$this->rawPostData = $raw_post_data;							// set raw post data for Class use

			// if post_data is php input stream, make it an array.
			if ( ! is_array($raw_post_data) ) {
				$raw_post_array = explode('&', $raw_post_data);
				$this->post_data = $raw_post_array;								// use post array because it's same as $_POST
			} else {
				$this->post_data = $raw_post_data;								// use post array because it's same as $_POST
			}

			$myPost = array();
            if (isset($raw_post_array)) {
                foreach ($raw_post_array as $keyval) {
                    $keyval = explode('=', $keyval);
                    if (count($keyval) == 2) {
                        $myPost[$keyval[0]] = urldecode($keyval[1]);
                    }
                }
            }

			// read the post from PayPal system and add 'cmd'
			$req = 'cmd=_notify-validate';

			foreach ($myPost as $key => $value) {
				if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() == 1) {
					$value = urlencode(stripslashes($value));
				} else {
					$value = urlencode($value);
				}
				$req .= "&$key=$value";
			}

			if ($this->use_curl) {
				$res = $this->curlPost($req);
			} else {
				$res = $this->fsockPost($req);
			}

			if (strpos($res, '200') === false) {
				throw new Exception("Invalid response status: " . $res);
			}

			// Split response headers and payload, a better way for strcmp
			$tokens = explode("\r\n\r\n", trim($res));
			$res = trim(end($tokens));
			if (strcmp ($res, "VERIFIED") == 0) {
				return true;
			} else if (strcmp ($res, "INVALID") == 0) {
				return false;
			} else {
				throw new Exception("Unexpected response from PayPal.");
			}
		} catch (Exception $e) {
			$this->addError($e->getMessage());
            return false;
		}
		return false;
    }

    /**
     *  Require Post Method
     *
     *  Throws an exception and sets a HTTP 405 response header if the request
     *  method was not POST.
     */
    public function requirePostMethod()
	{
        // require POST requests
        if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Allow: POST', true, 405);
            throw new Exception("Invalid HTTP request method.");
        }
    }

}
