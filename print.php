<!doctype html>
<html lang="ru">
<head>
	<meta charset="UTF-8" />
	<title>Отчёт для печати</title>
	<meta name="description" content="Отчёт для печати" /> 
    <meta name="Keywords" content="ОТЧЁТ, ПЕЧАТЬ" />
  	<link rel="stylesheet" href="style/print.css" />
    <link rel="shortcut icon" href="image/favicon2.ico" type="image/x-icon" />
</head>
<body>
	<header>
		<h3>&nbsp;</h3>
	</header>
	<content>	
		<main>
					
			<form action="" method="get">
			<table class="c2">
				<tr>
					<td class="c2">Печать таблицы<br />position из MySQL<br /></td>
				</tr>
			</table>
			
			<table>
			
			<tr class="cH">
<?php
// блок инициализации
try {
    $pdoSet = new PDO('mysql:dbname=observatorbd;host=localhost', 'root', '');
    $pdoSet->query('SET NAMES utf8;');
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

// название столбцов таблицы position
$sql = "SHOW COLUMNS FROM position";
$stmt = $pdoSet->query($sql);
$resultMF = $stmt->fetchAll();

echo '<tr>';
foreach ($resultMF as $column) {
    echo '<td>' . $column["Field"] . '</td>';
}
echo '</tr>';

// вызов хранимой процедуры join_tables
$sql = "CALL join_tables('position', 'sector')";
$stmt = $pdoSet->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// проверка, что результаты не пустые
if ($results) {
    // вывод данных таблиц
    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['earth_pos']) . '</td>';
        echo '<td>' . htmlspecialchars($row['sun_pos']) . '</td>';
        echo '<td>' . htmlspecialchars($row['moon_pos']) . '</td>';
        echo '</tr>';
    }
}
?>
</tr>


<?php 
	$sql = "SELECT * FROM position ORDER BY id ASC";  // ASC - по возрастанию; DESC - по убыванию.
//echo $sql;
	$stmt = $pdoSet->query($sql);
	$resultMF = $stmt->fetchAll();
//var_dump($resultMF);

	for($iC=0; $iC<Count($resultMF); $iC++) {
		?><tr><?php
		$iCountLine = floor(Count($resultMF[$iC])/2);
		for($iR = 0; $iR < $iCountLine; ++$iR) {
			?><td><?php echo $resultMF[$iC][$iR];?></td><?php
		}
		?></tr><?php
	}
	
?>
				</table>
			</form>
		</main>
	</content>
	<footer>
		<div>&nbsp;</div> 
	</footer>	
</body>
</html>