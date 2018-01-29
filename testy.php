<?php
session_start();

try
{
    $db = new PDO('mysql:host=localhost;dbname=cms', 'root', '');
}
catch (PDOException $e)
{
    print "Błąd połączenia z bazą!: " . $e->getMessage() . "<br/>";
	die();
}

function generateRandomString($length = 10)
{
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

if (isset($_POST['wrzucArtykuly']))
{
	echo 'działa';
	for ($i = 0; $i < 500; $i++)
	{
		$insert = $db->prepare("INSERT INTO artykuly(userID, tytul, tresc, data) VALUES(:userID, :tytul, :tresc, NOW());");
		$userek = rand(1, 99999);
		$insert->bindParam(":userID", $userek);
		$tytul = generateRandomString(3);
		$insert->bindParam(":tytul", $tytul);
		$tresc = generateRandomString(rand(10, 130));
		$insert->bindParam(":tresc", $tresc);
		$insert->execute();
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"></meta>
		<title>CMS - TESTY</title>
	</head>
	<body>
		<form action='testy.php' method='POST'>
			<input type='submit' name="wrzucArtykuly" value='WRZUĆ 500 ARTYKUŁÓW'></input>
		</form>
	</body>
</html>