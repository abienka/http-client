<?php declare(strict_types = 1);

namespace Abienka\HttpClient;

use Abienka\HttpClient\Helper\XmlPathHelper;
use Nyholm\Psr7\Factory\Psr17Factory;

class Nip24
{
    private const URL = 'https://www.nip24.pl/api-test';
    private const ID = 'test_id';
    private const KEY = 'test_key';
    private const HASHING = 'sha256';
    private const INACTIVE_CODE = 9;
    
    /** @var CurlClient */
    protected $curlClient;
    
    /** @var Psr17Factory */
    protected $psr17Factory;
    
    /**
     * @param CurlClient $curlClient
     * @param Psr17Factory $psr17Factory
     * @throws ClientException
     */
    public function __construct(
        CurlClient $curlClient,
        Psr17Factory $psr17Factory
    ) {
        $this->curlClient = $curlClient;
        $this->psr17Factory = $psr17Factory;
    }
    
    /**
     * @param string $nip
     * @return bool
     * @throws \Exception
     */
    public function isActive(string $nip): bool
    {
        $url = self::URL . '/check/firm/nip/' . $nip;
        $xml = $this->get($url);
        
        $code = $this->xpath($xml, XmlPathHelper::ERROR_CODE);
        
        if (strlen($code) > 0) {
            if (self::INACTIVE_CODE === $code) {
                return false;
            }
            
            throw new \Exception($this->xpath($xml, XmlPathHelper::ERROR_DESCRIPTION));
        }
        
        return true;
    }
    
    /**
     * @param string $nip
     * @return array
     * @throws \Exception
     */
    public function getInvoiceData(string $nip): array
    {
        $url = self::URL . '/get/invoice/nip/' . $nip;
        $xml = $this->get($url);
        
        $code = $this->xpath($xml, XmlPathHelper::ERROR_CODE);
        
        if (strlen($code) > 0) {
            throw new \Exception($this->xpath($xml, XmlPathHelper::ERROR_DESCRIPTION));
        }
        
        $data = [
            'uid' => $this->xpath($xml, XmlPathHelper::UID),
            'nip' => $this->xpath($xml, XmlPathHelper::NIP),
            'name' => $this->xpath($xml, XmlPathHelper::NAME),
            'firstname' => $this->xpath($xml, XmlPathHelper::FIRSTNAME),
            'lastname' => $this->xpath($xml, XmlPathHelper::LASTNAME),
            'street' => $this->xpath($xml, XmlPathHelper::STREET),
            'streetNumber' => $this->xpath($xml, XmlPathHelper::STREET_NUMBER),
            'houseNumber' => $this->xpath($xml, XmlPathHelper::HOUSE_NUMBER),
            'city' => $this->xpath($xml, XmlPathHelper::CITY),
            'postCode' => $this->xpath($xml, XmlPathHelper::POST_CODE),
            'postCity' => $this->xpath($xml, XmlPathHelper::POST_CITY),
            'phone' => $this->xpath($xml, XmlPathHelper::PHONE),
            'email' => $this->xpath($xml, XmlPathHelper::EMAIL),
            'www' => $this->xpath($xml, XmlPathHelper::WWW)
        ];
        
        return $data;
    }
    
    /**
     * @param string $url
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    protected function get(string $url): \SimpleXMLElement
    {
        $method = 'GET';
        $auth = $this->auth($method, $url);
        
        /** @var Psr\Http\Message\RequestInterface $request */
        $request = $this->psr17Factory->createRequest($method, $url)
            ->withHeader('Authorization', $auth);
        
        /** @var Psr\Http\Message\ResponseInterface $response */
        $response = $this->curlClient->sendRequest($request);
        
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Incorrect server response.');
        }
        
        $body = (string) $response->getBody();

        $xml = simplexml_load_string($body);
        if (!$xml) {
            throw new \Exception('Incorrect response body.');
        }    

        return $xml;
    }
    
    /**
     * @param string $method
     * @param string $url
     * @return string
     * @throws Exception
     */
    protected function auth(string $method, string $url): string
    {
        $u = parse_url($url);

        if (!array_key_exists('port', $u)) {
            $u['port'] = ($u['scheme'] == 'https' ? '443' : '80');
        }

        $nonce = bin2hex(openssl_random_pseudo_bytes(4));
        $ts = time();

        $str = $ts . "\n"
            . $nonce . "\n"
            . $method . "\n"
            . $u['path'] . "\n"
            . $u['host'] . "\n"
            . $u['port'] . "\n"
            . "\n";

        $mac = base64_encode(hash_hmac(self::HASHING, $str, self::KEY, true));

        if (!$mac) {
            throw new Exception('An error occurred while generating the MAC.');
        }

        return sprintf('MAC id="%s", ts="%s", nonce="%s", mac="%s"', self::ID, $ts, $nonce, $mac);
    }
    
    /**
     * @param \SimpleXMLElement $xml
     * @param string $path
     * @return string
     */
    private function xpath(\SimpleXMLElement $xml, string $path): string
    {
        $a = $xml->xpath($path);
        
        if (!$a) {
            return '';
        }
        
        if (count($a) !== 1) {
            return '';
        }
        
        return trim((string) $a[0]);
    }
}
