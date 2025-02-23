# API VideoGames

Une API RESTful développée avec Symfony 7 qui permet de gérer une base de données de jeux vidéo.

## Fonctionnalités

- CRUD complet pour les jeux vidéo
- Système d'authentification pour sécuriser les opérations sensibles
- Upload d'images pour les couvertures de jeux
- Newsletter pour informer les utilisateurs des sorties à venir
- Cache système pour optimiser les performances
- Documentation Swagger/OpenAPI
- Fixtures pour les données de test

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL/MariaDB
- Symfony CLI

## Identifiants Users(Fixtures)
| Email                     | Mot de passe | Rôles                                    |
|---------------------------|--------------|------------------------------------------|
| admin@example.com       | admin          | ROLE_ADMIN                             |
| user@example.com   | user          | ROLE_USER                             |

## Installation

1. Cloner le projet
```bash
git clone https://github.com/fabien-design/dyi-api-VideoGames.git
cd dyi-api-VideoGames
```

2. Demarrer le serveur 
```bash
symfony serve
```

3. Créer et remplir la bdd
```bash
    php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate -y
	php bin/console doctrine:fixtures:load -y
```

### Accès Doc : 
```bash
/api/v1/doc
```

### Activer le scheduler d'envoi d'email hebdomadaire
```bash
symfony console messenger:consume
```
et selectionner : scheduler_WeeklyMail
