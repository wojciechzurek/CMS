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

$zedytowano = false;
if (isset($_POST['tytul']) && isset($_POST['tresc']))
{
	$update = $db->prepare("UPDATE artykuly SET tytul=:tytul, tresc=:tresc WHERE id=:idArtykulu;");
	$update->bindParam(":tytul", $_POST['tytul']);
	$update->bindParam(":tresc", $_POST['tresc']);
	$update->bindParam(":idArtykulu", $_GET['id']);
	if ($update->execute())
		$zedytowano = true;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8"></meta>
		<title>CMS</title>
		<link rel="stylesheet" type="text/css" href="style.css">
		<script src="js/sweetalert.min.js"></script>
	</head>
	<body>
		<div id="gora">
			<a href="index.php"><img width="1000px" vspace="2px" src="logo.png"></img></a>
		</div>
		<div id="panelUzytkownika">
			<?php
				$succ_login = false;
				$is_admin = false;
				if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'])
				{
					$check = $db->prepare("SELECT login, is_admin FROM uzytkownicy WHERE id = :id LIMIT 1;");
					$check->bindParam(":id", $_SESSION['login_id']);
					$check->execute();
					if ($check->rowCount() > 0)
					{
						$check = $check->fetch(PDO::FETCH_ASSOC);
						$login = $check['login'];
						$is_admin = $check['is_admin'];
							
						echo '<p>Zalogowany jako: '.$login.' <a href="wyloguj.php">(Wyloguj)</a></p>';
						echo '<hr>';
						$succ_login = true;
						
						if ($_SESSION['is_admin'])
						{
							echo 'Menu Administratora:</br>';
							echo '<a href="dodajPost.php"><button>Dodaj artykuł</button></a></br></br></br>';
						}
						else
							$is_admin = false;

						echo 'Menu użytkownika:</br>';
						echo '<a href="index.php"><button>Pokaż artykuły</button></a></br>';
					}
				}
				if (!$succ_login)
				{
					echo '<p>PANEL LOGOWANIA</p><hr>';

					echo '<form action="zaloguj.php" method="POST">
								Login: <input name="login" type="text" required></input></br>
								Hasło: <input name="pass" type="password" required></input></br>';

					echo '<input class="fbBtn" type="submit" value="Zaloguj!"></input>
						  </form>';
				}
			?>
		</div>
		<div id="trescMain">
			<a href="index.php"><button style="float: left"><<</button></a></br>
			<?php
				if (!$is_admin)
				{
					echo 'Musisz być administratorem aby edytować artykuł!';
				}
				else if ($succ_login)
				{
					echo '<h3>Edycja artykułu</h3></br>';
					$select = $db->prepare("SELECT tytul, tresc FROM artykuly WHERE id=:idArtykulu;");
					$select->bindParam(":idArtykulu", $_GET['id']);
					$select->execute();
					if ($select->rowCount() > 0)
					{
						$select = $select->fetch(PDO::FETCH_ASSOC);
						
						echo "<form action='edytujArtykul.php?id=".$_GET['id']."' method='POST'>";
						echo "Tytuł:</br><input type='text' name='tytul' value='".$select['tytul']."' required></input></br></br>";
						echo "Treść:</br><textarea name='tresc' cols='40' rows='10' required>".$select['tresc']."</textarea></br>";
						echo "<input type='submit'></input>";
						echo '</form>';
					}
					else
						echo 'Nie znaleziono artykułu!';
				}
				else
					echo 'Zaloguj się aby dodać artykuł';
			?>
		</div>
		<div id="stopka">
			&copy; Wojciech Żurek i Tomasz Rzeźniczak 22.01.2018
		</div>
	</body>
	<?php
		if ($zedytowano)
			echo '<script> swal({title: "Pomyślnie!", text: "Pomyślnie zedytowano artykuł!", icon: "success"}); </script>';
	?>
</html>