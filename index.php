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
							echo '<a href="dodajPost.php"><button>Dodaj artykuł</button></a></br>';
							echo '<a href="edytujPost.php"><button>Edytuj artykuły</button></a></br></br></br>';
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
		<div id="trescMain">
			<?php
				if (!$succ_login)
					echo 'Zaloguj się aby widzieć posty!</br>';
				else
				{
					echo '<center><div style="max-height: 510px; overflow-y: auto;">';
					$select = $db->query('SELECT userID, tytul, tresc, data FROM artykuly ORDER BY data DESC;');
					foreach($select as $row)
						echo '<table> <tr> <td style="border: 1px solid black; width: 400px; height: 60px; text-align: center;"> <b>'.$row['tytul'].'</b></br></br>'.$row['tresc'].'</br></br><hr><span style="float: left;">Autor: '.$row['userID'].'</span> <span style="float: right;">'.$row['data'].'</span> </td> </tr> </table></br>';
					echo '</div></center>';
				}
			?>
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