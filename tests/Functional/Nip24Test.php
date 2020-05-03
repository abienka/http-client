<?php declare(strict_types=1);

namespace Abienka\HttpClient\Tests\Functional;

use Abienka\HttpClient\CurlClient;
use Abienka\HttpClient\Nip24;
use Abienka\HttpClient\Service\CurlService;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

class Nip24Test extends TestCase
{
    /** @var Nip24 */
    protected $nip24;
    
    public function setUp(): void
    {
        parent::setUp();
        
        $curlService = new CurlService();
        $psr17Factory = new Psr17Factory();
        $curlClient = new CurlClient($curlService, $psr17Factory, $psr17Factory);
        $this->nip24 = new Nip24($curlClient, $psr17Factory);
    }
    
    public function testIsActive()
    {
        $isActive = $this->nip24->isActive('7272445205');
        
        $this->assertTrue($isActive);
    }
    
    public function testIsActiveInvlidNip()
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage('Zapytanie o podane dane nie jest możliwe w trybie testowym');

        $this->nip24->isActive('6262753229');
    }
    
    public function testGetInvoiceData()
    {
        $expectedInvoiceData = [
            'nip' => '7272445205',
            'name' => '"NETCAT SYSTEMY INFORMATYCZNE" ROBERT JAZGARA',
            'firstname' => 'Robert',
            'lastname' => 'Jazgara',
            'street' => 'ul. Zagłoby',
            'streetNumber' => '21',
            'houseNumber' => '10',
            'city' => 'Warszawa',
            'postCode' => '02495',
            'postCity' => 'Warszawa',
            'phone' => '',
            'email' => '',
            'www' => ''
        ];
        
        $invoiceData = $this->nip24->getInvoiceData('7272445205');
        
        $this->assertArrayHasKey('uid', $invoiceData);
        unset($invoiceData['uid']);
        
        $this->assertEquals($expectedInvoiceData, $invoiceData);
    }
}
