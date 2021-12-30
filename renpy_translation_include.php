<?

$fileTranslate = "script_part_day11_en.rpy";
$fileTarget = "script_part_day11_en_fill.rpy";

// Paramétrage application :
$dirServer = "C:\UwAmp\www\\";
$dirParse = "translate\\";
// $langueCheck = "french";

$langueCheck = "english";
// $langueCheck = "spanish";

// Paramétrage DEEPL
$DeepLSRC = "EN";
$DeepLTarget = "EN";
$prenomAModifier = array();
$idUSER = 10;

$baliseCheck = array(
	"{t}"=>"{/t}", 
	"{ce}"=>"{/ce}", 
	"{c}"=>"{/c}", 
	"{l}"=>"{/l}"
); 
 
function display_error($txt) {
	return "<br><div style='font-size:30px; color:red;'>".$txt."</div>";
}

function ConvertisseurTime($Time){
     if($Time < 3600){ 
       $heures = 0; 
       
       if($Time < 60){$minutes = 0;} 
       else{$minutes = round($Time / 60);} 
       
       $secondes = floor($Time % 60); 
       } 
       else{ 
       $heures = round($Time / 3600); 
       $secondes = round($Time % 3600); 
       $minutes = floor($secondes / 60); 
       } 
       
       $secondes2 = round($secondes % 60); 
      
       $TimeFinal = "$heures h $minutes min $secondes2 s"; 
       return $TimeFinal; 
    }

// Connexion bDD :
$mysqli = new mysqli("localhost", "USERxxxx", "MDPxxxx", "renpy_translate");
if (mysqli_connect_errno()) {
    printf("Échec de la connexion : %s\n", mysqli_connect_error());
    exit();
}
 