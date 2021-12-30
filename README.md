# renpy_translate_sync
Synchronise translate file

Dans un projet renpy vous avez la langue principal du jeu dans les scripts.
Et les langues secondaires dans /tl/
Si vous touchez un seul caractère dans la ligne de la langue principale, la correspondance ne se fait plus dans le fichier de traduction.
Cela va créer une nouvelle variable.

Exemple :

Avant la correction du script principale, il y avait un "S" en trop à "je dirais".

# game/script_part_day11.rpy:899
translate english day11_office_mc_56a5f78b:

    # c "Sinon je crie en alertant tout le monde, je dirais que tu m'as forcé! Ça t'apprendra!"
    c "Otherwise, I start screaming warning everybody, I'll say that you forced me! This will give you a lesson!"



Une fois corriger dans le script, on se retrouve avec une nouvelle variable "vide" à traduire.

# game/script_part_day11.rpy:902
translate english day11_office_mc_955f9fd7:

    # c "Sinon je crie en alertant tout le monde, je dirai que tu m'as forcé! Ça t'apprendra!"
    c ""

Si vous avez fait 200 corrections dans le script, c'est très embétant de copier coller à la main la traduction.

------------------------------------------------------------------
Le script va éviter de le faire manuellement.
------------------------------------------------------------------
Etape 1 : On monte le fichier de traduction en base de donnée $fileTranslate (renpy_translation_include.php)
Il faut lancer dans un serveur web type UWAMP : http://localhost/1-renpy_translation_read_rpy.php

Etape 2 : On monte toutes les phrases et leur traduction dans un tableau php $tabTexteLanguePrincipale
On va chercher toutes les lignes vides.
On va regarder grace à la fonction "similar_text()" de php si il existe pas une phrase très proche sémantiquement. 
On prend la phrase qui à le plus de % de proximité.
On colle la traduction.
On reconstruit le fichier "$fileTarget" "script_part_day11_en_fill.rpy" avec les zones remplis. 
Il suffit alors de merger avec WinMerge. Normalement, seule les lignes qui étaient vides sont modifiées.