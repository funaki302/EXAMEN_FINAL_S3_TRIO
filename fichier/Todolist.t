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