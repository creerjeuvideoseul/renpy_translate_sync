<?

include("renpy_translation_include.php");


$langueCheck = "english";

$langueCheck = "spanish";

$cptLine = 0;
$cptLineGroupe = 0;
$langue = "";
$labelUnique = "";
$tmp = array();
$cas = 0;
$targetFile = "";
$SourceText = "";
$TranslateText = "";
$targetText = "";
$targetFileData = false;
$SymbolOfSeparate = ";";
$etat = 0; // non traduit 
$test1 = "";
$test2 = "";
$withCSVFile = false;

$tarifDeeplL = 100000; // # 1 € !
$cptWord = 0;
$cptCharacteres = 0;
 

$tabTexteLanguePrincipale = array();

$sql2 = "SELECT tt_line, tt_data FROM translation_text WHERE ta_id = 10 and tt_etat = 0 AND tt_case = 3 ORDER BY tt_line ASC ";
$result2 = $mysqli->query($sql2);
echo $sql2."<br>";
if ($result2->num_rows)
{
	while ($row2 = $result2->fetch_assoc()) {
		$tabTexteLanguePrincipale[$row2['tt_line']] = $row2['tt_data'];
	}
}
/*
echo "<pre>";
print_r($tabTexteLanguePrincipale);
echo "</pre>";*/
// Si $chemin est un dossier => on appelle la fonction explorer() pour chaque élément (fichier ou dossier) du dossier$chemin

if (file_exists($fileTarget))
	unlink($fileTarget);

$targetFileData = fopen($fileTarget, 'a'); // On ouvre le fichier de destination
 
if (substr($fileTranslate, -3) == "rpy")
{
	echo "<b>".htmlentities($fileTranslate)."</b><br>";

	/*Ouverture du fichier en lecture seule*/
	$handle = fopen($fileTranslate, 'r');

	$cptLine = 0;

	/*Si on a réussi à ouvrir le fichier*/
	if ($handle) {
		/*Tant que l'on est pas à la fin du fichier*/
		while (!feof($handle))	{

			/*On lit la ligne courante*/
			$buffer = fgets($handle);
			$cptLine ++;

			// CAS 1  :
			/*
# game/script_part1.rpy:1999
translate english day5_living_room_test_lucie_2d0d402c:

# t "J'ai aussi ressenti un moment fort hier, et je pense qu'on devrait aller plus loin!"
t ""
			*/
			if (preg_match("@^# game/@", $buffer)) // Si c'est la ligne de début du groupe d'une traduction :
			{
				if (isset($_GET['debug'])) echo "CAS 1 : LINE 1 : ".htmlentities($buffer)."<br>";
				$idUniqueInProgress = $buffer; // # game/screens.rpy:321 Stock la signature
				$cptLineGroupe = 0;
				$langue = "";
				$targetText = "";
				$labelUnique = "";
				$cas = 1;
				$cptLineGroupe ++; // ligne 1 faite.
			}


			if ($cas == 1 && $cptLineGroupe == 1 && preg_match("@^(translate )([^ ]*) ([^ ]*)$@", $buffer, $tmp)) // 2 eme ligne : translate english day5_living_room_test_lucie_2d0d402c:
			{
/*
0 => string 'translate english day_history_1_729787bd:' (length=43)
1 => string 'translate ' (length=10)
2 => string 'english' (length=7)
3 => string 'day_history_1_729787bd:
*/
				$langue = $tmp['2'];
				if ($langueCheck != $langue) {
					print "LANGUE DIFFERENCE DE CELUI ATTENDU : $langueCheck != $langue";
					die();
				}

				$labelUnique = $tmp['3']; // Label unique ----------- day_history_1_729787bd
				$cptLineGroupe ++; // ligne 2 faite.
				//if (isset($_GET['debug'])) echo "CAS 1 : LINE 2 : ".htmlentities($langue)." ".htmlentities($labelUnique)."OK<br>";
				unset($tmp);
			}

			if ($cas == 1 && $cptLineGroupe == 2 && preg_match("@^    # @", $buffer, $tmp)) // on cherche la 3 eme ligne. le old ou le # t 
			{
				//if (isset($_GET['debug'])) echo "CAS 1 : LINE 3 : Ancienne valeur # : ".htmlentities($buffer)."<br>";
				$SourceText = $buffer;
				$cptLineGroupe ++;
			}

			if ($cas == 1 && $cptLineGroupe == 3 && (preg_match("@^    ([a-zA-Z0-9\_\.]*) @", $buffer) OR preg_match("@^    \"@", $buffer))) // on cherche la 3 eme ligne. le old ou le # t SAUF NEW !
			{
				//if (isset($_GET['debug'])) echo "CAS 1 : LINE 4 : Traduction : ".htmlentities($buffer)."<br><br>";
				$targetText = $buffer;
				$cptLineGroupe ++;

			}

			// if (isset($_GET['debug'])) echo "=> ".htmlentities($buffer)."<br>";

// CAS 2 :
/*
translate english strings:

# game/screens.rpy:321
old "Nouvelle partie"
new "New game"

# game/script_part1.rpy:179
old "Ne pas lui donner tout de suite"
new ""
*/
			// LINE 1 ON commence par :  
			if ($buffer && preg_match("@^translate ".$langueCheck." strings:@", $buffer)) // 2 eme ligne : translate english strings:
			{
				/*
				  0 => string 'translate english strings:' (length=28)
				  1 => string 'translate ' (length=10)
				  2 => string 'english' (length=7)
				  3 => string 'strings:
				*/
				$labelUnique = "";
				$targetText = "";
				$cptLineGroupe = 0; // ligne 2 faite.
				$cptLineGroupe ++; // ligne 1 faite.
				$cas = 2;
				//if (isset($_GET['debug'])) echo "CAS 2 : LINE 1 : ".htmlentities($langueCheck)." strings OK<br>";
				unset($tmp);
			}

			if ($cas == 2 && $cptLineGroupe == 1 && preg_match("@^    # ([a-zA-Z0-9\_\.]{1,})/@", $buffer)) // Si c'est la ligne de début du groupe d'une traduction :
			{
				//if (isset($_GET['debug'])) echo "CAS 2 : LINE 2 : ".htmlentities($buffer)."<br>";
				$labelUnique = $buffer;// # game/screens.rpy:321 --------- Stock la signature
				$cptLineGroupe ++; // ligne 1 faite.
			}
			if ($cas == 2 && $cptLineGroupe == 2 && preg_match("@^    old \"@", $buffer)) // on cherche la 3 eme ligne. le old ou le # t 
			{
				//if (isset($_GET['debug'])) echo "CAS 2 : LINE 3 : Ancienne valeur  : ".htmlentities($buffer)."<br>";
				//$SourceText = preg_replace("@^old \"@", "", substr(trim($buffer), 0, -1)); // on retire le " de fin et le old "

				$SourceText = $buffer; 

				$cptLineGroupe ++;
			}
			if ($cas == 2 && $cptLineGroupe == 3 && preg_match("@^    new \"@", $buffer)) // on cherche la 3 eme ligne. le old ou le # t 
			{
				//if (isset($_GET['debug'])) echo "CAS 2 : LINE 4 : La traduction  : ".htmlentities($buffer)."<br>";
				// $targetText = preg_replace("@new \"@", "", substr(trim($buffer), 0, -1)); // on retire le " de fin et le new "
				$cptLineGroupe ++;
				$targetText = $buffer;
				
			}


/*
Savoir si c'est déjà traduit :
# t "(*En pensée*) {t}*J'ai bien dormi! Je suis en pleine forme! \nQuel jour sommes-nous? Dimanche! Mais c'est aujourd'hui que Lucie arrive! Il ne faut pas trop que je traîne!*{/t} "
t "traduit" 
=> t ""
ou
# game/script_part1.rpy:201
old "La reprendre"
new "azeaze"
*/
			// Si aucune traduction :
			if ($targetText)
			{
				$test1 = trim($targetText);
				$test2 = trim($SourceText);

				if ($test1 == 'new ""' OR preg_match('@^([a-zA-Z0-9\_\.]{1,}) ""@', $test1) OR preg_match('@^""$@', $test1)) // Vide à traduire.
					$etat = 0;
				elseif ("# ".$test1 == $test2) // Si remplit DANS la meme langue. 
					$etat = 1;
				elseif (preg_replace("@new \"@", "", $test1) == preg_replace("@old \"@", "", $test2)) // ===# new "Window"===old "Window"===
					$etat = 1;
				else // Déjà traduit ou remplit.
					$etat = 2;

				$targetText = preg_replace("(\r\n|\n|\r)", "", $targetText); // on supprime les caractères \n qui pose problème à la traduction
			}
			else
				$etat = 0;


			// echo " $SourceText && !$targetText && $etat == 0 && $cptLineGroupe >= 4<br>";
			if ($SourceText && $etat == 0 && $cptLineGroupe >= 4) // Il n'y a pas de traduction et qu'on a une phrase FR.
			{
				// Soit, c'est exactement la meme chose (le FR).

				// Soit, c'est différent, on compare les 40 premiers caractèeres FR / FR et/ou les 40 derniers.

				// SI LA REPONSE EST VIDE DANS LE FICHIRE SOURCE, ON VA CHECKER EN BASE DE DONNEE si y'a pas une information proche.
				// On va chercher dans le fichier source, qui a été monté en bdd, la ligne francaise qui correspond à la phase à traduire actuellement.
				// User = 10 + etat = 0 (qui est la phrases FRANCAISE)
				
			/*	$sql2 = "SELECT tt_line FROM translation_text WHERE ta_id = 10 and tt_etat = 0 AND tt_data like '%".addslashes(substr(trim($SourceText), 2))."%' AND tt_line != '".$lineSource."' ORDER BY tt_line ASC LIMIT 1 ";
				$result2 = $mysqli->query($sql2);
				if (isset($_GET['debug'])) 
					echo $sql2."<br>";
				if ($result2->num_rows)
				{
					while ($row2 = $result2->fetch_assoc()) {
						
						// On va chercher la ligne suivante qui contient la traduction anglaise.
						$sql3 = "SELECT tt_translate FROM translation_text WHERE ta_id = 10 and tt_etat = 2 AND tt_line = '".($row2['tt_line']+1)."' LIMIT 1 ";
						$result3 = $mysqli->query($sql3);
						if (isset($_GET['debug'])) 
							echo $sql3."<br>";
						while ($row3 = $result3->fetch_assoc()) {
							$buffer = $row3['tt_translate']."\r\n"; // La réponse du document.
						}  
						// Il faut remplacer dans la réponse : le truc qu'on a récupérer de la base de donnée.
					}
				}
				else
				{*/
					$LineFound = 0;
					$SaveBddScore = 0;

					echo "<strong>RECHERCHE SIMILAR</strong><br>";

					foreach ($tabTexteLanguePrincipale as $BddLine => $BddTextFr)
					{
						if (addslashes(substr(trim($BddTextFr), 2)) != addslashes(substr(trim($SourceText), 2)) && similar_text($BddTextFr, addslashes(substr(trim($SourceText), 2))) > 30)
						{
							$BddScore = similar_text($BddTextFr, addslashes(substr(trim($SourceText), 2)));
							// echo "<strong>PHRASE SIMILAIRE ligne $BddLine TROUVE ".$BddScore."%</strong> : de <br>|$SourceText| Trouvé : <br>|$BddTextFr| <br>";

							if ($SaveBddScore < $BddScore)
							{
								$SaveBddScore = $BddScore;
								$LineFound = $BddLine; 
								$txtConclusion = "<strong>PHRASE SIMILAIRE ligne $BddLine TROUVE ".$BddScore."%</strong> : de <br>|$SourceText| Trouvé : <br>|$BddTextFr| <br>";
							}
						}
					}
					
					if ($LineFound > 0) //  && $SaveBddScore > 80
					{
						//echo "LIGNE RETENUE : $LineFound (score $SaveBddScore %)<br>";
						echo "$txtConclusion<br>";

						// On va chercher la ligne suivante qui contient la traduction anglaise.
						$sql3 = "SELECT tt_translate FROM translation_text WHERE ta_id = 10 and tt_etat = 2 AND tt_line = '".($LineFound+1)."' LIMIT 1 ";
						$result3 = $mysqli->query($sql3);
						echo $sql3."<br>";
						while ($row3 = $result3->fetch_assoc()) {
							$buffer = $row3['tt_translate']."\r\n"; // La réponse du document.
						}  
					}
					else
					{
						echo "<span style='color:red;font-size:20px;'>AUCUN MATCH</span> $SourceText <br>";
					}
				/*}*/
			}
 

		    //echo "==>$cptLineGroupe ".htmlentities($buffer)."<br>";

			fputs($targetFileData, $buffer); // On copie dans le fichier cible.

			if ($cptLineGroupe == 4)
				$cptLineGroupe = 0; // 1 est important, car on ne reviendra pas dans le bloc 1. On reprend direct avec un game.
			
		} 
		echo "<br><span style='font-size:25px;'>".$cptWord." mots et ". $cptCharacteres." charactères.<br><br></span><br>";

		/*On ferme le fichier*/
		fclose($handle);
		//fclose($targetFileData);
		set_time_limit(0);
	}
	else {
		print display_error("Impossible d'ouvrir le fichier source : ".$dirParse.$fileTranslate);
		die();
	}
}



echo "<br><br><span style='font-size:45px;'>".$cptWord." mots et ". $cptCharacteres." charactères.<br><br></span>";
  