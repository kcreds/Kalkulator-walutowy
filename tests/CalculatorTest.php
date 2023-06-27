<?php

use PHPUnit\Framework\TestCase;

require_once 'calculator.php';

class CalculatorTest extends TestCase
{
    public function testValidateData_WithValidData_ReturnsNull()
    {
        $conn = null;
        $calculator = new Calculator($conn);
        $amount = 100;
        $firstCurrency = 'USD';
        $secondCurrency = 'EUR';
        $currencyRate = ['USD', 'EUR'];

        $result = $calculator->validateData($amount, $firstCurrency, $secondCurrency, $currencyRate);

        $this->assertNull($result);
    }

    public function testValidateData_WithInvalidAmount_ReturnsAmountError()
    {
        $conn = null;
        $calculator = new Calculator($conn);
        $amount = 'invalid';
        $firstCurrency = 'USD';
        $secondCurrency = 'EUR';
        $currencyRate = ['USD', 'EUR'];
        $expectedError = "Wprowadź poprawną kwotę";

        $result = $calculator->validateData($amount, $firstCurrency, $secondCurrency, $currencyRate);

        $this->assertEquals($expectedError, $result);
    }


    public function testCalculations_ReturnsCorrectResult()
    {
        $conn = null;
        $calculator = new Calculator($conn);
        $amount = 100;
        $firstCurrency = 2.5;
        $secondCurrency = 1.2;
        $expectedResult = 208.33;

        $result = $calculator->calculations($amount, $firstCurrency, $secondCurrency);

        $this->assertEquals($expectedResult, $result);
    }


    public function testRenderCalcDataTable_WithNonEmptyData_ReturnsHTMLTable()
    {
        $conn = null;
        $calculator = new Calculator($conn);
        $data = [
            [
                'amount' => 100,
                'firstCurrency' => 'USD',
                'firstCurrencyMid' => 2.5,
                'secondCurrency' => 'EUR',
                'secondCurrencyMid' => 1.2,
                'result' => 208.33
            ],
        ];
        $expectedTable = '<div class="container mb-5"> <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th scope="col">Kwota</th>
                <th scope="col">Waluta źródłowa</th>
                <th scope="col">Kurs waluty źródłowej</th>
                <th scope="col">Waluta docelowa</th>
                <th scope="col">Kurs waluty docelowej</th>
                <th scope="col">Kwota po przeliczeniu</th>
            </tr>
        </thead>
        <tbody><tr><td scope="row">100</td><td>USD</td><td>2.5</td><td>EUR</td><td>1.2</td><td>208.33</td></tr></tbody></table></div>';

        $result = $calculator->renderCalcDataTable($data);

        $this->assertEquals($expectedTable, $result);
    }

    public function testRenderCalcDataTable_WithEmptyData_ReturnsNoDataMessage()
    {
        $conn = null;
        $calculator = new Calculator($conn);
        $data = [];
        $expectedMessage = "Brak danych w bazie";

        $result = $calculator->renderCalcDataTable($data);

        $this->assertEquals($expectedMessage, $result);
    }
}
