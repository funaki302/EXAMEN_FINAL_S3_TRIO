/ 27 janvier 2026 9h19 /
[] lire README.md de la demo original
[] faire marcher la demo original
[] construire la structure MVC
    [] dossiers (app, public, vendor)
    [] leur composants

[] DATA
  [] creer une database template_MVC{
    [] creer les tables:
      - user
      - discussion
      - messages
      -
      -
    [] inserer des donnees provisoires
  }
    

[] integrer la demo template dans MVC
  [ok] ajuster config.php
  [ok] ajuster routes.php{
    [ok] rigirer url '/' vers login.php 
  }
  [ok] renommer les fichier html en php

-----PAGES-----
[] creer une page login{
  [ok] formulaire de login 
     (name, email, phone, pwd)
  [] verification des informations via js{
    [] phone gasy
    [] email normal, pas deja utiliser
    [] name > 2 lettres
    [] pwd non vide
  }
  [ok] validation{
    [ok] verifier si le user existe deja
    [ok] sinon inscription auto
  ===== rediriger vers index.php
  } 
}

[] CRUD de user{
  [ok] User.php (model)
  [ok] UserController.php
}

[] afficher dans users.php la liste des users{
  [ok] recuperer les donnees via UserController
  [ok] afficher dans un tableau
}

[] index.php{
  [ok] afficher le nom de l utilisateur connecte
}

[] logout.php{
  [] detruire la session
  [] rediriger vers login.php
}

[] data.sql{
  [] creer tables: - discussion
                   - messages 
}

model{
  [ok] creer User.php
  [] creer Discussion.php
  [] creer Messages.php
}

controller{
  [ok] creer UserController.php
  [] creer DiscussionController.php
  [] creer MessagesController.php
}

[] messages.php{
  [] afficher la liste des discussions de l utilisateur connecte
  [] possibilite d ajouter un message
  [] possibilite d ajouter une nouvelle conversation
} 

