<?php
require_once 'db_conn.php';
require_once 'nbp_data.php';

class Calculator
{
    private $conn;
    private $NBPData;

    // Konstruktor zmiennej połączenia i instancji NBPData
    // Przyjumje zmienną połaczenia
    function __construct($conn)
    {
        $this->conn = $conn;
        $this->NBPData = new NBPData($conn);
    }

    // Funkcja startująca wykonywanie oparacji
    // Wykonuje obsługę danych wejściowych, walidację, obliczenia, zapis wyników i wyświetlanie rezultatów
    function runCalculator()
    {
        $availableCurrencies = $this->NBPData->fetchDataFromDatabase('current_rate');
        $currencyRate = $this->NBPData->getColumnValuesFromCurrencies($availableCurrencies, 'mid');

        // Pobranie wartości z formularza, sprawdzanie czy dane zostały przesłane przez POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = $_POST['amount'];
            $firstCurrency = $_POST['firstCurrency'];
            $secondCurrency = $_POST['secondCurrency'];

            $validationError = $this->validateData($amount, $firstCurrency, $secondCurrency, $currencyRate);

            $firstCurrencyCode = $this->NBPData->getColumnCodeFromCurrencies($availableCurrencies, $currencyRate, $firstCurrency);
            $secondCurrencyCode = $this->NBPData->getColumnCodeFromCurrencies($availableCurrencies, $currencyRate, $secondCurrency);

            if ($validationError !== null) {
                echo $validationError;
            } else {
                $result = $this->calculations($amount, $firstCurrency, $secondCurrency);
                echo $result;
                $this->saveCalcResultToDB($amount, $firstCurrencyCode, $firstCurrency, $secondCurrencyCode,  $secondCurrency, $result);
            }
        }
    }

    // Funkcja sprawdzająca dane z formularza
    // Przyjmuje dane przesłane przez formularz w $amount, $firstCurrency, $secondCurrency
    // Przyjumje kolumnę do sprawdzenia danych z bazy w $currencyRate
    // Zwraca null jeśli nie ma błędów w walidacji
    function validateData($amount, $firstCurrency, $secondCurrency, $currencyRate)
    {
        if (empty($amount) || !is_numeric($amount)) {
            $amountError = "Wprowadź poprawną kwotę";
            return $amountError;
        }

        if (empty($firstCurrency) || !in_array($firstCurrency, $currencyRate)) {
            $firstCurrencyError = "Wybierz poprawną walutę źródłową";
            return $firstCurrencyError;
        }

        if (empty($secondCurrency) || !in_array($secondCurrency, $currencyRate)) {
            $secondCurrencyError = "Wybierz poprawną walutę docelową";
            return $secondCurrencyError;
        }

        return null;
    }

    // Funkcja wykonująca obliczenia
    // Przyjmuje dane przesłane przez formularz w $amount, $firstCurrency, $secondCurrency
    // Zwraca wynik w $result
    function calculations($amount, $firstCurrency, $secondCurrency)
    {
        $result = ($amount * $firstCurrency) / $secondCurrency;

        // Zaokrąglenie wyniku
        $result = round($result, 2);

        return $result;
    }

    // Funkcja zapisująca dane do bazy danych
    // Przyjmuje dane przesłane przez formularz w $amount, $firstCurrencyMid, $secondCurrencyMid
    // Przyjumje dodatkowe dane o kodzie waluty z funkcji getColumnCodeFromCurrencies() w $firstCurrencyCode, $secondCurrencyCode
    // Przyjumje wynki obliczeń w $result
    function saveCalcResultToDB($amount, $firstCurrencyCode, $firstCurrencyMid, $secondCurrencyCode, $secondCurrencyMid, $result)
    {
        $conn = $this->conn;
        // Zapisanie wyniku do bazy danych
        $saveQuery = "INSERT INTO calculator_data (amount, firstCurrency, firstCurrencyMid, secondCurrency, secondCurrencyMid, result) VALUES (:amount, :firstCurrency, :firstCurrencyMid, :secondCurrency, :secondCurrencyMid, :result)";
        $saveValue = $conn->prepare($saveQuery);
        $saveValue->bindValue(':amount', $amount);
        $saveValue->bindValue(':firstCurrency', $firstCurrencyCode);
        $saveValue->bindValue(':firstCurrencyMid', $firstCurrencyMid);
        $saveValue->bindValue(':secondCurrency', $secondCurrencyCode);
        $saveValue->bindValue(':secondCurrencyMid', $secondCurrencyMid);
        $saveValue->bindValue(':result', $result);
        $saveSuccess = $saveValue->execute();

        if ($saveSuccess) {
            // echo "Dane zapisane";
        } else {
            //Wiadmość wyświetlana wkonsoli 
            echo "Błąd zapisu danych";
        }
    }

    // Generowanie tablicy z historią wykonywania obliczeń w kalkulatorze
    // Przyjmuje array w $data
    // Zwraca kod html tabeli w $table
    function renderCalcDataTable($data)
    {
        if (!empty($data)) {
            $table = '<div class="container mb-5"> <table class="table table-striped table-hover">';
            $table .= '
        <thead>
            <tr>
                <th scope="col">Kwota</th>
                <th scope="col">Waluta źródłowa</th>
                <th scope="col">Kurs waluty źródłowej</th>
                <th scope="col">Waluta docelowa:</th>
                <th scope="col">Kurs waluty docelowej</th>
                <th scope="col">Kwota po przeliczeniu</th>
            </tr>
        </thead>
        <tbody>';

            foreach ($data as $rate) {
                $table .= '<tr>';
                $table .= '<td scope="row">' . $rate['amount'] . '</td>';
                $table .= '<td>' . $rate['firstCurrency'] . '</td>';
                $table .= '<td>' . $rate['firstCurrencyMid'] . '</td>';
                $table .= '<td>' . $rate['secondCurrency'] . '</td>';
                $table .= '<td>' . $rate['secondCurrencyMid'] . '</td>';
                $table .= '<td>' . $rate['result'] . '</td>';
                $table .= '</tr>';
            }

            $table .= '</tbody></table></div>';
            return $table;
        } else {
            return "Brak danych w bazie";
        }
    }
}

// Tworzymy instancję klasy i przypisujemy do $calculator
$calculator = new Calculator($conn);

//Wywołujemy funkcję startującą wykonywanie operacji
$calculator->runCalculator();

// Pobieramy dane z bazy danych 'calculator_data' do $calcData
$calcData = $NBPData->fetchDataFromDatabase('calculator_data');

// Generujemy tablicę z wynikami i zapisujemy w $calcTable
$calcTable = $calculator->renderCalcDataTable($calcData);


//Zamknięcie połączenia
$conn = null;
