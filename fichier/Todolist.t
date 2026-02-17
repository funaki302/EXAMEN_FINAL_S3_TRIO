/ 16 fevrier 2026 /

besoins.php
 [] creer un formulaire de saisi de besoin{
  [] script besoins.js{
    []loadForm()
     - genere le formulaire de saisi de besoin
     - liste deroulante de villes, articles
     - saisi de quantite
     - submit -> appel fonction createBesoins()
  } 
  [] script met_villes.js{
    [] fonction getAllVilles()
     - fecth('/api/getAll/villes')
     - return json
  }
  [] script met_articles.js{
    [] fonction getAllArticles()
     - fecth ('/api/getAll/articles') 
     - return json
  }
  [] script met_besoins.js{
    [] fonction createBesoins()
     - fecth ('/api/create/besoins') avec $data
     - return true/false
  }
 }

 /17 fevrier 2026 /
nouvelles fonctionnalites :
 - je veux mettre un bouton 'Reinitialiser'{
  . lorsque je clique sur ce bouton, ca reinicialise toutes les donnees
  . c est a dire que toutes les donnees qui ne sont pas enregistrer comme 'origine'
    seront effacer directement
 }
 Suite a cela, pour reconnaitre les donnees 'origine' :
 - je veux ajouter une collone 'id_mode' dans chaque table de ma base de donnee
 - je veux creer une table 'mode' dont les donnees sont 'origine' & 'teste'
 - donc lors de la reinitialisation, tous les donnees 'teste' seront effacer
 - je veux donc ajouter un choix de 'mode' lors des insertions des besoins, des dons,
  des achats, des ditributions
  {
    . ajouter liste deroulante de 'mode' dans page besoins.php dans les formulaires 
      d insertions des besoins et des dons
    . ajouter liste deroulante de 'mode' dans page achats.php lorsque on effectue un achat 
  }
 - voici les tables concernees :
 BNGRC_besoins_villes
 BNGRC_dons_recus
 BNGRC_distributions
 BNGRC_achats
 BNGRC_transactions_argent 

ce sont les fonctionnalites que je veux implementer dans ma todolist :
- peux tu me suggerer une autre alternative pour effectuer la reinitialisation
des donnee si il y a une autre solution plus simple que celle que j ai proposee
- si non, procede a l implementation de la solution que j ai proposee en suivant les etapes suivantes :
 1. creer la table 'mode' et ajouter la collone 'id_mode' dans les tables concernees
 2. modifier les formulaires d insertions des besoins, des dons, des achats pour ajouter le choix de 'mode'
 3. creer une fonction de reinitialisation qui efface toutes les donnees 'teste' des tables concernees
