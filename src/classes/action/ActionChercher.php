<?php

namespace iutnc\netvod\action;

use iutnc\netvod\bd\ConnectionFactory;
use iutnc\netvod\NetVOD\Serie;
use iutnc\netvod\render\RenderSerie;

class ActionChercher extends Action
{

    public function execute(): string
    {
        $html = "";
        $html .= ActionCatalogue::triAffichageRecherche();
        //doit afficher la liste des series retenu a la selection
        $html .= "<form id='accueil' method='post' enctype='multipart/form-data' action = ''>";
        $html .= $this->afficherListe();
        $html .= '</form></center></ul>';
        return $html;
    }

    private function afficherListe(): string
    {
        $html = "";
        $listeSerie = $this->genererListeSerie();
        foreach ($listeSerie as $serie) {
            $html .= "";
            $renderSerie = new RenderSerie($serie);
            $strSerie = $renderSerie->render();

            $id_serie = $serie->__get('id');
            $html .= "<li><button formaction='index.php?action=serie&id=$id_serie'>$strSerie</button></li>";
        }
        return $html;
    }

    private function genererListeSerie()
    {
        $db = ConnectionFactory::makeConnection();
        $query = "SELECT * FROM serie ";
        $result = $db->prepare($query);
        $result->execute();

        $terme = strtolower($_GET["terme"]);
        $listeSerie = [];
        while ($datas = $result->fetch(\PDO::FETCH_ASSOC)) {

            // Selon le choix de recherche, lancer la recherche des séries
            if ($_GET["searchType"] == "titre") {
                $titre = strtolower($datas['titre']);
                if ($this->estDansPhrase($titre, $terme)) {
                    $newSerie = new Serie($datas['titre'], $datas['img'], $datas['descriptif'], $datas['annee'], $datas['date_ajout'], $datas['id']);
                    $listeSerie[] = $newSerie;
                }
            } elseif ($_GET["searchType"] == "description") {
                $description = strtolower($datas['descriptif']);
                if ($this->estDansPhrase($description, $terme)) {
                    $newSerie = new Serie($datas['titre'], $datas['img'], $datas['descriptif'], $datas['annee'], $datas['date_ajout'], $datas['id']);
                    $listeSerie[] = $newSerie;
                }
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