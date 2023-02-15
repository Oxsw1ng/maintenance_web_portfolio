<?php

namespace iutnc\netvod\action;

use iutnc\netvod\NetVOD\Serie;
use iutnc\netvod\render\RenderSerie;
use iutnc\netvod\bd\ConnectionFactory;

class ActionCatalogue extends Action
{


    public function execute(): string
    {
        $html = "";
        $html .= $this->triAffichageRecherche();
        //doit afficher la liste des series retenu a la selection
        $html .= "<form id='accueil' method='post' enctype='multipart/form-data' action = ''>";
        $html .= $this->afficherListe();
        $html .= '</form></center></ul>';

        return $html;
    }

    //public static function tri(\PDO $db, string $query): string
    //{
     //   $html = "";
     //   $_SESSION["queryTriFiltre"] = $query;
     //   $result = $db->prepare($query);
    //    $result->execute();
    //    $html .= "<form id='accueil' class='serie' method='post' enctype='multipart/form-data' action = ''>";
    //    while ($datas = $result->fetch(\PDO::FETCH_ASSOC)) {
    //        $serie = new Serie($datas['titre'], $datas['img'], $datas['descriptif'], $datas['annee'], $datas['date_ajout'], $datas['id']);
    //        $render = new RenderSerie($serie);
     //       $id_serie = $datas['id'];
    //        $data = $render->render();
    //        $html .= "<li><button formaction='index.php?action=serie&id=$id_serie'>$data</button></li>";
    //    }
     //   $result->closeCursor();
     //   $html .= '</form></center></ul>';

    //    return $html;
   // }

    public static function resultatTriFiltre() : string {

        $retour = "";

        try {
            $db = ConnectionFactory::makeConnection();
        } catch (\Exception $e) {
            $html .= "<p> Connection à la base de données impossible</p>";
        }

        $tri = "";
        $genre = "";
        // Rajouter le filtre si il est set dans le tri
        $ifFiltreSet = "";

        if (isset($_POST['tri'])) {
            $tri = $_POST['tri'];
        }
        if(isset($_POST['filtrer'])) {

            if ($tri == "") {
                $tri='genre';
            }
            $genre=$_POST['filtrer'];

            if ($genre != "")  $ifFiltreSet = "where genre LIKE '$genre'";

        }

        switch ($tri) {

            case 'genre':
                $retour = "SELECT * FROM serie $ifFiltreSet";
                break;
            case 'titre':
                $retour = "SELECT * FROM serie $ifFiltreSet ORDER BY titre ASC";
                break;
            case 'dateAjout':
                $retour = "SELECT * FROM serie $ifFiltreSet ORDER BY date_ajout ASC";
                break;
            case 'annee':
                $retour = "SELECT * FROM serie $ifFiltreSet ORDER BY annee ASC";
                break;
            case 'noteMoyenne':
                $retour = "SELECT * FROM serie $ifFiltreSet ORDER BY noteMoyenne ASC";
                break;
            default:
                $retour = "SELECT * FROM serie $ifFiltreSet";
                break;
        }

        return $retour;

    }

    public function triAffichageRecherche() : string {
        $html = "
                <form id=\"f1\" method=\"post\" action='?action=catalogue'>
                    <label for='filtre-select' font-size='5px'> Chercher dans : </label>
                    <select name='searchType' id='search-select'>
                        <option value='titre'> Titre </option>
                        <option value='description'> Description</option>
                    </select>
                    <input id=\"searchbar\" type = \"search\" name = \"terme\">
                    <br>
                    <div style=\"text-align: center\">
                  <label for='tri-select'> Trier par : </label>
                  <select name='tri' id='tri-select'>
                    <option value=''> </option>
                    <option value='titre'>Titre</option>
                    <option value='dateAjout'>Date d'ajout sur la plateforme</option>
                    <option value='annee'>Annee</option>
                    <option value='noteMoyenne'>Note moyenne</option>
                  </select>
                  <label for='filtre-select'> filtrer par genre : </label>
                  <select name='filtrer' id='filtre-select'>
                    <option value=''></option>
                    <option value='action'>Action </option>
                    <option value='aventure'>aventure</option>
                    <option value='thriller'>Thriller</option>
                    <option value='horreur'>Horreur</option>
                    <option value='romance'>romance</option>
                  </select>
                  <br>
                  </div>
                  <div style=\"text-align: center\">
                  <button type=\"submit\" name=\"action\" value=\"catalogue\">Chercher</button>
                    </div>
                </form>";
  

        return $html;
    }

    private function afficherListe(): string
    {
        $html = "";
        $listeSerie = $this->genererListeSerie($this->resultatTriFiltre());
        foreach ($listeSerie as $serie) {
            $html .= "";
            $renderSerie = new RenderSerie($serie);
            $strSerie = $renderSerie->render();

            $id_serie = $serie->__get('id');
            $html .= "<li><button formaction='index.php?action=serie&id=$id_serie'>$strSerie</button></li>";
        }
        return $html;
    }

    private function genererListeSerie(string $query)
    {
        $db = ConnectionFactory::makeConnection();
        $result = $db->prepare($query);
        $result->execute();

        if (isset($_POST["terme"])) {
            $terme = strtolower($_POST["terme"]);
            $listeSerie = [];
            while ($datas = $result->fetch(\PDO::FETCH_ASSOC)) {

            // Selon le choix de recherche, lancer la recherche des séries
            if ($_POST["searchType"] == "titre") {
                $titre = strtolower($datas['titre']);
                if ($this->estDansPhrase($titre, $terme)) {
                    $newSerie = new Serie($datas['titre'], $datas['img'], $datas['descriptif'], $datas['annee'], $datas['date_ajout'], $datas['id']);
                    $listeSerie[] = $newSerie;
                }
            } elseif ($_POST["searchType"] == "description") {
                $description = strtolower($datas['descriptif']);
                if ($this->estDansPhrase($description, $terme)) {
                    $newSerie = new Serie($datas['titre'], $datas['img'], $datas['descriptif'], $datas['annee'], $datas['date_ajout'], $datas['id']);
                    $listeSerie[] = $newSerie;
                    }
                }
                
            }
        } else {
            while ($datas = $result->fetch(\PDO::FETCH_ASSOC)) {
                $newSerie = new Serie($datas['titre'], $datas['img'], $datas['descriptif'], $datas['annee'], $datas['date_ajout'], $datas['id']);
                $listeSerie[] = $newSerie;
            }
        }
        return $listeSerie;
    }

    private function estDansPhrase(string $titre, string $terme)
    {
        $motsTitre = explode(' ', strtolower($titre));
        $motsTerme = explode(' ', strtolower($terme));
        $goodSerie = false;
        foreach ($motsTerme as $motTerme) {
            //parcours les mots de la desc
            $good = false;
            //si good vrai , c'est que le mot de la descr est dans le titre
            foreach ($motsTitre as $motTitre) {
                //compare chaque mot de la description avec un mot du titre
                if (preg_match("/".$motTerme."/i", $motTitre) === 1) {
                    $good = true;
                }
            }
            if ($good) {
                $goodSerie = true;
            }
        }
        return $goodSerie;

    }
}