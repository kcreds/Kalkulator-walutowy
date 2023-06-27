<?php 
use PHPUnit\Framework\TestCase;

require_once "nbp_data.php";

class NBPDataTest extends TestCase
{
    public function testGetUrl_ReturnsUrl()
    {
        $conn = null;
        $nbpData = new NBPData($conn);
        $expectedUrl = 'http://api.nbp.pl/api/exchangerates/tables/a?format=json';

        $result = $nbpData->getUrl();

        $this->assertEquals($expectedUrl, $result);
    }

    public function testGetColumnValuesFromCurrencies_WithValidData_ReturnsColumnValues()
    {
        $conn = null;
        $nbpData = new NBPData($conn);
        $currencies = [
            ['currency' => 'Dollar', 'code' => 'USD', 'mid' => 3.75],
            ['currency' => 'Euro', 'code' => 'EUR', 'mid' => 4.31],
        ];
        $columnName = 'currency';
        $expectedColumnValues = ['Dollar', 'Euro'];

        $result = $nbpData->getColumnValuesFromCurrencies($currencies, $columnName);

        $this->assertEquals($expectedColumnValues, $result);
    }

    public function testGetColumnValuesFromCurrencies_WithMissingColumn_ReturnsEmptyArray()
    {
        $conn = null;
        $nbpData = new NBPData($conn);
        $currencies = [
            ['code' => 'USD', 'mid' => 3.75],
            ['code' => 'EUR', 'mid' => 4.31],
        ];
        $columnName = 'currency';
        $expectedColumnValues = [];

        $result = $nbpData->getColumnValuesFromCurrencies($currencies, $columnName);

        $this->assertEquals($expectedColumnValues, $result);
    }

    public function testGetColumnCodeFromCurrencies_WithValidSearchValue_ReturnsCode()
    {
        $conn = null;
        $nbpData = new NBPData($conn);
        $currencies = [
            ['currency' => 'Dollar', 'code' => 'USD', 'mid' => 3.75],
            ['currency' => 'Euro', 'code' => 'EUR', 'mid' => 4.31],
        ];
        $columnValues = ['Dollar', 'Euro'];
        $searchValue = 'Euro';
        $expectedCode = 'EUR';

        $result = $nbpData->getColumnCodeFromCurrencies($currencies, $columnValues, $searchValue);

        $this->assertEquals($expectedCode, $result);
    }

    public function testGetColumnCodeFromCurrencies_WithInvalidSearchValue_ReturnsNull()
    {
        $conn = null;
        $nbpData = new NBPData($conn);
        $currencies = [
            ['currency' => 'Dollar', 'code' => 'USD', 'mid' => 3.75],
            ['currency' => 'Euro', 'code' => 'EUR', 'mid' => 4.31],
        ];
        $columnValues = ['Dollar', 'Euro'];
        $searchValue = 'Yen';

        $result = $nbpData->getColumnCodeFromCurrencies($currencies, $columnValues, $searchValue);

        $this->assertNull($result);
    }

}
