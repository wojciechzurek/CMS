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

$dodano = false;
$polubiono = false;
$odlubiono = false;
if (isset($_POST['tresc']) && $_POST['tresc'] != "")
{
	$insert = $db->prepare("INSERT INTO komentarze(idArtykulu, userID, tresc, data) VALUES(:artykul, :user, :tresc, NOW());");
	$insert->bindParam(":artykul", $_GET['id']);
	$insert->bindParam(":user", $_SESSION['login_id']);
	$insert->bindParam(":tresc", $_POST['tresc']);
	if ($insert->execute())
		$dodano = true;
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
							echo '<a href="edytujArtykul.php?id='.$_GET['id'].'"><button>Edytuj ten artykuł</button></a></br>';
							echo '<a href="usunArtykul.php?id='.$_GET['id'].'"><button>Usuwanie Artykułów</button></a></br></br></br>';
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
				
				
				// AKCJA
				if ($succ_login)
				{
					if (isset($_POST['akczja']))
					{
						$select = $db->prepare('SELECT id FROM polubienia WHERE idArtykulu=:idArtykulu AND idKomentarza=:idKomentarza AND idUzytkownika=:idUzytkownika;');
						$select->bindParam(":idArtykulu", $_GET['id']);
						$select->bindParam(":idKomentarza", $_POST['idKomentarza']);
						$select->bindParam(":idUzytkownika", $_SESSION['login_id']);
						$select->execute();
						if ($select->rowCount() > 0)
						{
							$odlubiono = true;

							$usuwanko = $db->prepare("DELETE FROM polubienia WHERE idArtykulu=:idArtykulu AND idKomentarza=:idKomentarza AND idUzytkownika=:idUzytkownika;");
							$usuwanko->bindParam(":idArtykulu", $_GET['id']);
							$usuwanko->bindParam(":idKomentarza", $_POST['idKomentarza']);
							$usuwanko->bindParam(":idUzytkownika", $_SESSION['login_id']);
							$usuwanko->execute();
							
							$update = $db->prepare("UPDATE komentarze SET lajki=lajki-1 WHERE id=:id AND idArtykulu=:idArtykulu;");
							$update->bindParam(":id", $_POST['idKomentarza']);
							$update->bindParam(":idArtykulu", $_GET['id']);
							$update->execute();
						}
						else
						{
							$insert = $db->prepare("INSERT INTO polubienia(idArtykulu, idKomentarza, idUzytkownika) VALUES(:idArtykulu, :idKomentarza, :idUzytkownika);");
							$insert->bindParam(":idArtykulu", $_GET['id']);
							$insert->bindParam(":idKomentarza", $_POST['idKomentarza']);
							$insert->bindParam(":idUzytkownika", $_SESSION['login_id']);
							if ($insert->execute())
							{
								$polubiono = true;

								$update = $db->prepare("UPDATE komentarze SET lajki=lajki+1 WHERE id=:id AND idArtykulu=:idArtykulu;");
								$update->bindParam(":id", $_POST['idKomentarza']);
								$update->bindParam(":idArtykulu", $_GET['id']);
								$update->execute();
							}
						}
					}
				}
			?>
		</div>
		<div id="trescMain">
			<a href="index.php"><button style="float: left"><<</button></a></br>
			<center><div style="max-width: 600px">
				<?php
					if (!$succ_login)
						echo 'Zaloguj się aby widzieć posty!</br>';
					else
					{
						$select = $db->prepare('SELECT userID, tytul, tresc, data FROM artykuly WHERE id=:id;');
						$select->bindParam(":id", $_GET['id']);
						$select->execute();
						if ($select->rowCount() > 0)
						{
							$select = $select->fetch(PDO::FETCH_ASSOC);
							echo "<h2>".$select['tytul']."</h2>";
							echo $select['tresc'].'</br></br>';
							echo "<span style='float: left'>Autor: ".$select['userID']."</span>";
							echo "<span style='float: right'>".$select['data']."</span></br>";
							echo '<hr>Komentarze użytkowników:</br>';
							echo '<div style="max-height: 310px; overflow-y: auto">';
							
							// MOJE POLUBIENIA
							$mojePolubienia = Array();

							$select = $db->prepare('SELECT idKomentarza FROM polubienia WHERE idArtykulu=:idArtykulu AND idUzytkownika=:idUzytkownika;');
							$select->bindParam(":idArtykulu", $_GET['id']);
							$select->bindParam(":idUzytkownika", $_SESSION['login_id']);
							$select->execute();
							if ($select->rowCount() > 0)
							{
								foreach($select as $row)
									array_push($mojePolubienia, $row['idKomentarza']);
							}
							//
							
							$select = $db->prepare('SELECT id, userID, lajki, tresc, data FROM komentarze WHERE idArtykulu=:id ORDER BY data DESC;');
							$select->bindParam(":id", $_GET['id']);
							$select->execute();
							if ($select->rowCount() > 0)
							{
								foreach($select as $row)
								{
									$lubieTo = in_array($row['id'], $mojePolubienia);
									echo '<table>
											<tr>
												<td style="border: 1px solid black; min-width: 400px; max-width: 400px; height: 60px; text-align: center;">
													</br>
													<span style="word-wrap: break-word;">'.$row['tresc'].'</span>
													</br>
													<b><span style="float: left; margin-left: 4px; margin-top: '.($lubieTo?10:16).'px; margin-right: 4px; color: DarkSlateBlue">'.$row['lajki'].'</span></b>
													<form action="pokazArtykul.php?id='.$_GET['id'].'" method="POST">
														<input name="akczja" type="text" value="lubieTo" hidden></input>
														<input name="idKomentarza" type="text" value="'.$row['id'].'" hidden></input>
														<input type="image" src="'.($lubieTo?'unlike.png':'like.png').'" style="float: left; margin-top: 8px; cursor: pointer" alt="Lubie to"></input>
													</form>
													</br>
													<hr>
													<span style="float: left;">Autor: '.$row['userID'].'</span>
													<span style="float: right;">'.$row['data'].'</span>
												</td>
											</tr>
										</table>
										</br>';
								}
							}
							else
								echo 'Brak komentarzy!';
							echo '</div></br>';
						}
						else
							echo 'Nie ma takiego artykułu!';
					}
				?>
			</div>
			<form action="pokazArtykul.php?id=<?php echo $_GET['id']; ?>" method="POST">
				Twój komentarz: <input type="text" name="tresc" size="70" maxlength="150"></input>
				<input type="submit" value="Dodaj"></input>
			</form>
			</center>
		</div>
		<div id="stopka">
			&copy; Wojciech Żurek i Tomasz Rzeźniczak 22.01.2018
		</div>
	</body>
	<?php
		if ($dodano)
			echo '<script> swal({title: "Pomyślnie!", text: "Pomyślnie dodano komentarz!", icon: "success"}); </script>';
		if ($polubiono)
			echo '<script> swal({title: "Pomyślnie!", text: "Pomyślnie polubiono komentarz!", icon: "success"}); </script>';
		if ($odlubiono)
			echo '<script> swal({title: "Pomyślnie!", text: "Pomyślnie odlubiono komentarz!", icon: "success"}); </script>';
	?>
</html>