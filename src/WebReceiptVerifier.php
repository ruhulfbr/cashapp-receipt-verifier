<?php

/**
 * src/WebReceiptVerifier.php
 * CSV to SQL Query Generator Script

 *
 * @package ruhulfbr/cashapp-receipt-verifier
 * @author Md Ruhul Amin (ruhul11bd@gmail.com)
 * @version 1.0.1
 */

namespace Ruhulfbr\CashApp;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

class WebReceiptVerifier{

    private string $_USERNAME;
    private string $_REFERENCE;
    private string $_RECEIPT;
    private string $_RECEIPT_TRX;
    private string $_RECEIPT_BASE_URL='https://cash.app/payments/';
    private string $_RECEIPT_JSON_BASE_URL='https://cash.app/receipt-json/f/';
    private object $_RESPONSE;


    /**
     * Constructor for creating a new instance of the class.
     *
     * Initializes the response object and sets the username and reference values.
     *
     * @param string $_USERNAME The username associated with the instance.
     * @param string $_REFERENCE The reference associated with the instance.
     */
    public function __construct(string $_USERNAME, string $_REFERENCE)
    {
        $this->_RESPONSE = new \stdClass();
        $this->_USERNAME = $_USERNAME;
        $this->_REFERENCE = $_REFERENCE;
    }

    /**
     * Verify a web receipt.
     *
     * @param string $receipt The web receipt to verify.
     * @return object The response object indicating the verification result.
     */
    public function verify(string $receipt): object
    {
        $this->_RECEIPT = $receipt;

        if(!$this->verifyNoteAndUserName() || !$this->validateReceipt()) {
            return $this->_RESPONSE;
        }

        try {
            $validatorUrl = $this->_RECEIPT_JSON_BASE_URL . $this->_RECEIPT_TRX;

            $response = $this->httpRequest($validatorUrl);

            if ($response->getStatusCode() == 200) {
                $result = json_decode($response->getBody());
                $this->validateResponse($result);
            }
            else{
                $this->setResponse('error','Failed to verify web receipt, please provide a valid receipt');
            }
        }
        catch (\Exception $e){
            $this->setResponse('error',$e->getMessage());
        }

        return $this->_RESPONSE;
    }

     /**
     * Validate a web receipt URL.
     *
     * @return bool True if the receipt is valid, false otherwise.
     */
    private function validateReceipt(): bool
    {
        // Check if receipt is empty, not a valid URL, or not from the base URL
        if (
            empty($this->_RECEIPT) ||
            !filter_var($this->_RECEIPT, FILTER_VALIDATE_URL) ||
            !str_contains($this->_RECEIPT, $this->_RECEIPT_BASE_URL)
        ) {
            $this->setResponse('error', 'Invalid web receipt URL');
            return false;
        }

        // Parse the URL
        $urlParts = parse_url($this->_RECEIPT);

        // Check if the path is empty
        if (empty($urlParts['path'])) {
            $this->setResponse('error', 'Invalid web receipt URL');
            return false;
        }

        // Extract path segments
        $pathSegments = explode('/', trim($urlParts['path'], '/'));

        // Check if the second segment exists and has a minimum length
        if (empty($pathSegments[1]) || strlen($pathSegments[1]) < 15) {
            $this->setResponse('error', 'Invalid web receipt URL');
            return false;
        }

        // Extract the first five characters of the second segment
        $receiptTrx = $pathSegments[1];
        $firstFive = substr($receiptTrx, 0, 5);

        // Check if all characters of the first five are the same
        if (count(array_count_values(str_split($firstFive))) === 1) {
            $this->setResponse('error', 'Invalid web receipt URL');
            return false;
        }

        // If all checks passed, the receipt is valid
        $this->_RECEIPT_TRX = $receiptTrx;

        return true;
    }

    /**
     * Validate the response from the web receipt verification.
     *
     * @param object $result The result object from the web receipt verification.
     * @return void
     */
    private function validateResponse(object $result): void
    {
        $notes = strtolower($result->notes);
        $reference = strtolower($this->_REFERENCE);
        $username = $this->_USERNAME;

        // Check if notes or host doesn't match the expected values
        if (
            empty($notes) ||
            $notes !== $reference ||
            empty($result->detail_rows[3]->value) ||
            $result->detail_rows[3]->value !== $username
        ) {
            $this->setResponse('error', 'Failed to verify web receipt, Unmatched notes or host.');
            return;
        }

        $this->setResponse('success', 'Web Receipt Verified Successfully.', $result);
    }

    /**
     * Verify that both username and payment reference are provided.
     *
     * @return bool True if both username and payment reference are provided, false otherwise.
     */
    private function verifyNoteAndUserName(): bool
    {
        // Check if the username is empty
        if (empty($this->_USERNAME)) {
            $this->setResponse('error', 'CashApp `username` is required');
            return false;
        }

        // Check if the payment reference is empty
        if (empty($this->_REFERENCE)) {
            $this->setResponse('error', 'CashApp payment `reference` is required');
            return false;
        }

        // If both username and payment reference are provided, return true
        return true;
    }

    /**
     * Sets the response properties.
     *
     * @param string $type The response type.
     * @param string $message The response message.
     * @param array $data (Optional) The receipt response data.
     *
     * @return void
     */
    private function setResponse(string $type, string $message, object|array $data = []): void
    {
        $this->_RESPONSE->type = $type;
        $this->_RESPONSE->message = $message;

        if (!empty($data)) {
            $this->_RESPONSE->data = $data;
        }
    }

    /**
     * Perform an HTTP GET request to the specified URL.
     *
     * @param string $URL The URL to request.
     * @return ResponseInterface The response object.
     * @throws GuzzleHttp\Exception\RequestException if an error occurs during the request.
     */
    private function httpRequest(string $URL): ResponseInterface
    {
        $http = new GuzzleClient();
        return $http->request('GET', $URL);
    }
}