<?php
require_once 'db_conn.php';

class NBPData
{
    // Pobieranie aktualnych kursów
    private $url = 'http://api.nbp.pl/api/exchangerates/tables/a?format=json';

    // Zmienna połączenia z DB
    private $conn;


    // Konstruktor zmiennej połączenia
    function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Getter do odczytywania $url
    function getUrl()
    {
        return $this->url;
    }

    // Funkcja pobierania danych z api
    // Zwraca tablicę w $data

    function fetchDataApi()
    {
        // Przypisujemy do $data jsona z api
        $data = file_get_contents($this->getUrl());
        return json_decode($data, true);
    }

    // Zapisywanie danych do bazy danych
    // Przyjmuje dane w $data
    // Przyjmuje połączenie do bazy w $conn
    function saveToDatabase($data, $conn)
    {
        foreach ($data[0]['rates'] as $rate) {
            $currency = $rate['currency'];
            $code = $rate['code'];
            $mid = $rate['mid'];

            // Sprawdzenie czy dane już istnieją w bazie
            $checkQuery = "SELECT COUNT(*) FROM current_rate WHERE currency = :currency AND code = :code AND mid = :mid";
            $checkValue = $conn->prepare($checkQuery);
            $checkValue->bindValue(':currency', $currency);
            $checkValue->bindValue(':code', $code);
            $checkValue->bindValue(':mid', $mid);
            $checkValue->execute();
            $count = $checkValue->fetchColumn();

            // Jeśli dane nie istnieją - wykonaj zapis
            if ($count == 0) {
                $saveQuery = "INSERT INTO current_rate (currency, code, mid) VALUES (:currency, :code, :mid) ON DUPLICATE KEY UPDATE mid = :mid";
                $saveValue = $conn->prepare($saveQuery);
                $saveValue->bindValue(':currency', $currency);
                $saveValue->bindValue(':code', $code);
                $saveValue->bindValue(':mid', $mid);
                $saveValue->execute();
            }
            // Jeśli nie aktualizuje dane
            else {
                $updateQuery = "UPDATE current_rate SET mid = :mid WHERE currency = :currency AND code = :code AND mid = :mid";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bindValue(':currency', $currency);
                $updateStmt->bindValue(':code', $code);
                $updateStmt->bindValue(':mid', $mid);
                $updateStmt->execute();
            }
        }
        $conn = null;
    }

    // Funkcja pomocnicza do zapisania pobranych danych z bazy 
    // Zwraca array w $fetchData
    // Przyjumję nazwę tabeli $tableName
    function fetchDataFromDatabase($tableName)
    {
        try {
            $query = "SELECT * FROM $tableName";
            $prepareList = $this->conn->prepare($query);
            $prepareList->execute();
            $fetchData = $prepareList->fetchAll(PDO::FETCH_ASSOC);
            return $fetchData;
        } catch (PDOException $error) {
            die("Błąd: " . $error->getMessage());
        }
    }

    // Generowanie tablicy z wynikami
    // Przyjmuje array w $data
    // Zwraca kod html tabeli w $table
    function renderRatesTable($data)
    {
        if (!empty($data)) {
            $table = '<div class="container mb-5"> <table class="table table-striped table-hover">';
            $table .= '
        <thead>
            <tr>
                <th scope="col">Nazwa waluty</th>
                <th scope="col">Kod waluty</th>
                <th scope="col">Kurs</th>
            </tr>
        </thead>
        <tbody>';

            foreach ($data as $rate) {
                $table .= '<tr>';
                $table .= '<td scope="row">' . $rate['currency'] . '</td>';
                $table .= '<td>' . $rate['code'] . '</td>';
                $table .= '<td>' . $rate['mid'] . '</td>';
                $table .= '</tr>';
            }

            $table .= '</tbody></table></div>';
            return $table;
        } else {
            return "Brak danych w bazie";
        }
    }

    // Funkcja pomocnicza do wyszukiwania danej kolumny w tabeli 
    // Przyjmuje dane z tabeli w $currencies
    // Przyjmuje nazwę kolumny w $columnName
    // Zwraca array w $columnValues
    function getColumnValuesFromCurrencies($currencies, $columnName)
    {
        $columnValues = array();

        foreach ($currencies as $currency) {
            if (isset($currency[$columnName])) {
                $columnValues[] = $currency[$columnName];
            }
        }

        return $columnValues;
    }

    // Funkcja pomocnicza do wyszukiwania kodu waluty na podstawie wartości z wskazanej kolumny
    // Przyjmuje array w $currencies oraz $columnValues
    // Przyjmuje wartość, której szukamy $searchValue
    function getColumnCodeFromCurrencies($currencies, $columnValues, $searchValue)
    {
        $index = array_search($searchValue, $columnValues);
        if ($index !== false) {
            return $currencies[$index]['code'];
        }
        return null;
    }
}




// Tworzymy instancję klasy i przypisujemy do $NBPData
$NBPData = new NBPData($conn);

// Przypisujemy pobrane dane do $rates
$rates = $NBPData->fetchDataApi();

// Zapisujemy dane do bazy danych
$NBPData->saveToDatabase($rates, $conn);

// Pobieramy dane z bazy danych 'current_rate' do $NBPApiData
$NBPApiData = $NBPData->fetchDataFromDatabase('current_rate');

// Generujemy tablicę z wynikami i zapisujemy w $table
$ratesTable = $NBPData->renderRatesTable($NBPApiData);
