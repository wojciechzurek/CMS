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
				if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'])
				{
					$check = $db->prepare("SELECT login FROM uzytkownicy WHERE id = :id LIMIT 1;");
					$check->bindParam(":id", $_SESSION['login_id']);
					$check->execute();
					if ($check->rowCount() > 0)
					{
						$check = $check->fetch(PDO::FETCH_ASSOC);
						$login = $check['login'];
							
						echo '<p>Zalogowany jako: '.$login.' <a href="wyloguj.php">(Wyloguj)</a></p>';
						echo '<hr>';
						$succ_login = true;
						
						if ($_SESSION['is_admin'])
						{
							echo 'Menu Administratora:</br>';
							echo '<a href="dodajPrzedmiot.php"><button>Dodaj artykuł</button></a></br>';
							echo '<a href="zatwierdzOddanie.php"><button>Edytuj artykuły</button></a></br></br></br>';
						}

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
		<div id="wypozyczalniaMain">
			
		</div>
		<div id="stopka">
			&copy; Wojciech Żurek i Tomasz Rzeźniczak 22.01.2018
		</div>
	</body>
	<?php
		if (isset($_SESSION['login_err']))
		{
			$tekst = "";
			$good = false;
			if ($_SESSION['login_err'] == 'no data')
				$tekst = 'Brak hasła lub loginu!';
			else if ($_SESSION['login_err'] == 'no connect')
				$tekst = 'Brak połączenia z bazą!';
			else if ($_SESSION['login_err'] == 'Wrong pass')
				$tekst = 'Złe hasło!';
			else if ($_SESSION['login_err'] == 'No login')
				$tekst = 'Nie znaleziono takiego konta!';
			else if ($_SESSION['login_err'] == 'Nie admin')
				$tekst = 'Nie jesteś administratorem!';
			else if ($_SESSION['login_err'] == 'Wylogowano')
			{
				$good = true;
				$tekst = 'Zostałeś pomyślnie wylogowany!';
			}
			else if ($_SESSION['login_err'] == 'okej')
			{
				$good = true;
				$tekst = 'Zalogowano pomyślnie!';
			}
			
			echo '<script> swal({title: "'.($good?'Pomyślnie!':'Nie pomyślnie!').'", text: "'.$tekst.'", icon: "'.($good?'success':'error').'"}); </script>';
		
			unset($_SESSION['login_err']);
		}
	?>
</html>