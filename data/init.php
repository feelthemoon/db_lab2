<?php
// Подключение к базе данных
try {
    $pdoSet = new PDO('mysql:host=localhost;dbname=observatorbd', 'root', '');
    $pdoSet->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoSet->query('SET NAMES utf8;');
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Создание таблиц
try {
    // Создание таблицы sector
    $sqlTM = "
        CREATE TABLE IF NOT EXISTS sector (
            id INT PRIMARY KEY AUTO_INCREMENT,
            coordinates VARCHAR(255) NOT NULL,
            light_intensity DECIMAL(10, 2) NOT NULL,
            foreign_objects TEXT,
            star_objects_count INT NOT NULL,
            undefined_objects_count INT NOT NULL,
            defined_objects_count INT NOT NULL,
            notes TEXT
        );
    ";
    $pdoSet->exec($sqlTM);

    // Создание таблицы objects
    $sqlTM = "
        CREATE TABLE IF NOT EXISTS objects (
            id INT PRIMARY KEY AUTO_INCREMENT,
            type VARCHAR(255) NOT NULL,
            accuracy DECIMAL(5, 2) NOT NULL,
            quantity INT NOT NULL,
            time TIME NOT NULL,
            date DATE NOT NULL,
            notes TEXT
        );
    ";
    $pdoSet->exec($sqlTM);

    // Создание таблицы natural_objects
    $sqlTM = "
        CREATE TABLE IF NOT EXISTS natural_objects (
            id INT PRIMARY KEY AUTO_INCREMENT,
            type VARCHAR(255) NOT NULL,
            galaxy VARCHAR(255) NOT NULL,
            accuracy DECIMAL(5, 2) NOT NULL,
            light_flux DECIMAL(10, 2) NOT NULL,
            associated_objects TEXT,
            notes TEXT
        );
    ";
    $pdoSet->exec($sqlTM);

    // Создание таблицы position
    $sqlTM = "
        CREATE TABLE IF NOT EXISTS position (
            id INT PRIMARY KEY AUTO_INCREMENT,
            earth_pos VARCHAR(255) NOT NULL,
            sun_pos VARCHAR(255) NOT NULL,
            moon_pos VARCHAR(255) NOT NULL
        );
    ";
    $pdoSet->exec($sqlTM);

    // Создание таблицы Observation
    $sqlTM = "
        CREATE TABLE IF NOT EXISTS Observation (
            id INT PRIMARY KEY AUTO_INCREMENT,
            sector_id INT NOT NULL,
            object_id INT NOT NULL,
            natural_object_id INT NOT NULL,
            position_id INT NOT NULL,
            observation_time TIME NOT NULL,
            observation_date DATE NOT NULL,
            comments TEXT,
            FOREIGN KEY (sector_id) REFERENCES sector(id),
            FOREIGN KEY (object_id) REFERENCES objects(id),
            FOREIGN KEY (natural_object_id) REFERENCES natural_objects(id),
            FOREIGN KEY (position_id) REFERENCES `position`(id)
        );
    ";
    $pdoSet->exec($sqlTM);

    // Создание триггера

    $sqlTM = "
        CREATE TRIGGER IF NOT EXIST update_object_trigger
        AFTER UPDATE ON objects
        FOR EACH ROW
        BEGIN
            UPDATE objects SET date = CURRENT_DATE WHERE id = NEW.id;
        END;
    ";
    $pdoSet->exec($sqlTM);


    $sqlTM = "
    CREATE PROCEDURE IF NOT EXIST join_tables(IN table1 VARCHAR(255), IN table2 VARCHAR(255))
    BEGIN
        SET @query = CONCAT('SELECT * FROM ', table1, ' t1 JOIN ', table2, ' t2 ON t1.id = t2.id');
        PREPARE stmt FROM @query;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END;
";
$pdoSet->exec($sqlTM);


} catch (PDOException $e) {
    die("Ошибка при создании таблиц: " . $e->getMessage());
}

// // Пример вставки данных в таблицу `sector`
for ($i = 1; $i <= 10; $i++) {
    $sqlTM = "
        INSERT INTO sector (coordinates, light_intensity, foreign_objects, star_objects_count, undefined_objects_count, defined_objects_count, notes) 
        VALUES (
            'Coordinates $i',
            100.00 + $i,
            'Foreign objects $i',
            $i,
            $i + 1,
            $i + 2,
            'Notes for sector $i'
        );
    ";
    $pdoSet->exec($sqlTM);
}

// Пример вставки данных в таблицу `objects`
for ($i = 1; $i <= 10; $i++) {
    $sqlTM = "
        INSERT INTO objects (type, accuracy, quantity, time, date, notes) 
        VALUES (
            'Object Type $i',
            99.00,
            $i,
            '12:00:00',
            '2024-09-30',
            'Notes for object $i'
        );
    ";
    $pdoSet->exec($sqlTM);
}

// Пример вставки данных в таблицу `natural_objects`
for ($i = 1; $i <= 10; $i++) {
    $sqlTM = "
        INSERT INTO natural_objects (type, galaxy, accuracy, light_flux, associated_objects, notes) 
        VALUES (
            'Natural Object $i',
            'Galaxy $i',
            99.99,
            500.00 + $i,
            'Associated objects $i',
            'Notes for natural object $i'
        );
    ";
    $pdoSet->exec($sqlTM);
}

// Пример вставки данных в таблицу `position`
for ($i = 1; $i <= 10; $i++) {
    $sqlTM = "
        INSERT INTO position (earth_pos, sun_pos, moon_pos) 
        VALUES (
            'Earth Position $i',
            'Sun Position $i',
            'Moon Position $i'
        );
    ";
    $pdoSet->exec($sqlTM);
}


if (isset($_GET['bt1'])) {
    // Получаем все столбцы таблицы
    $sql = "SHOW COLUMNS FROM position";
    $stmt = $pdoSet->query($sql);
    $resultMF = $stmt->fetchAll();

    $sqlTM = "INSERT INTO position (";
    for ($iR = 1; $iR < count($resultMF); ++$iR) {
        $sqlTM .= $resultMF[$iR]["Field"];
        if ($iR < count($resultMF) - 1) {
            $sqlTM .= ', ';
        } else {
            $sqlTM .= ") VALUES (";
        }
    }

    for ($iR = 1; $iR < count($resultMF); ++$iR) {
        $sqlTM .= "'".$_GET[$resultMF[$iR]["Field"]]."'";
        if ($iR < count($resultMF) - 1) {
            $sqlTM .= ', ';
        } else {
            $sqlTM .= ")";
        }
    }

    $stmt = $pdoSet->query($sqlTM);
}

// Обновление записи в таблице private_individuals
if (isset($_GET['textId'])) {
    $sql = "SHOW COLUMNS FROM position";
    $stmt = $pdoSet->query($sql);
    $resultMF = $stmt->fetchAll();

    $sqlTM = "UPDATE position SET ";
    for ($iR = 1; isset($_GET["textEd" . $iR]); ++$iR) {
        $sqlTM .= $resultMF[$iR]["Field"] . "='" . $_GET["textEd" . $iR] . "'";
        if (isset($_GET["textEd" . ($iR + 1)])) {
            $sqlTM .= ', ';
        } else {
            $sqlTM .= " WHERE id = " . $_GET["textId"];
        }
    }

    $stmt = $pdoSet->query($sqlTM);
}

// Удаление записи в таблице private_individuals
if (isset($_GET['delid'])) {
    $sqlTM = "DELETE FROM position WHERE id = " . $_GET["delid"];
    $stmt = $pdoSet->query($sqlTM);
    $sqlTM = "DELETE FROM position WHERE id = " . $_GET["delid"];
    $stmt = $pdoSet->query($sqlTM);
}

// Добавление столбца в таблицу private_individuals
if (isset($_GET['addrow'])) {
    $sqlTM = "ALTER TABLE position ADD ".$_GET['addrow']."1 TEXT NOT NULL AFTER ".$_GET['addrow'];
    $stmt = $pdoSet->query($sqlTM);
}

// Удаление столбца из таблицы private_individuals
if (isset($_GET['delrow'])) {
    $sqlTM = "ALTER TABLE position DROP ".$_GET['delrow'];
    $stmt = $pdoSet->query($sqlTM);
}

// Основной запрос для выгрузки данных
if (isset($_GET['order'])) {
    $sql = "SELECT * FROM position ORDER BY ".$_GET['order']." ASC";
} else {
    $sql = "SELECT * FROM position ORDER BY id ASC";
}

$stmt = $pdoSet->query($sql);
$resultMF = $stmt->fetchAll(PDO::FETCH_NUM); // Получаем результаты в виде числовых индексов
?>
