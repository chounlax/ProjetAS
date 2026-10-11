# Modèle Symfony sécurisé – gestion des accès et des habilitations

Application Symfony 7.4 prête à servir de base à de nouveaux projets : inscription, connexion, profil, changement de mot de passe et espace administrateur, avec les protections de sécurité courantes déjà en place.

## Fonctionnalités

| Besoin | Mise en œuvre |
| --- | --- |
| Inscription (nom, prénom, adresse, ville, code postal) | `RegistrationFormType` ; règles de validation déclarées sur l'entité `User` (Validation Constraints de Symfony). |
| Mot de passe robuste | Contrainte composée `App\Validator\StrongPassword` : 12 caractères minimum, minuscule, majuscule, chiffre, caractère spécial, `PasswordStrength`, `NotCompromisedPassword`. |
| Changement de mot de passe sans réutilisation | `/private/mot-de-passe` ; `App\Security\PasswordManager` garde l'empreinte de chaque mot de passe (entité `PasswordHistory`) et refuse un mot de passe déjà utilisé. |
| Protection contre la force brute | `login_throttling` du pare-feu : 5 tentatives échouées maximum par 15 minutes (par e-mail + IP, et par IP). |
| Suspension de compte | Champ `isActive` ; `App\Security\UserChecker` refuse la connexion, `SuspendedAccountSubscriber` déconnecte une session déjà ouverte. |
| Gestion des comptes, rôles et habilitations | `/admin/utilisateurs` (rôle `ROLE_ADMIN`) : ajouter, modifier, supprimer, suspendre, attribuer les rôles. |
| Historique des connexions | `LoginHistorySubscriber` enregistre chaque connexion réussie ou échouée (date, heure, IP, navigateur), affichée sur la fiche de l'utilisateur. |

## Installation

```bash
git clone https://github.com/chounlax/modele.git mon-projet
cd mon-projet
composer install
```

Créer un fichier `.env.local` (jamais commité) :

```
APP_SECRET=une_chaine_aleatoire_longue
DATABASE_URL="mysql://utilisateur:mot_de_passe@127.0.0.1:3306/ma_base?serverVersion=10.11.14-MariaDB&charset=utf8mb4"
```

Puis créer la base et les tables :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Créer le premier administrateur

Inscrivez-vous sur le site, puis :

```bash
php bin/console app:make-admin votre@adresse.fr
```

## Réutiliser le modèle pour un nouveau projet

- **Rôles** : ajouter une ligne dans `User::ROLES` (libellé => rôle) et, si besoin, la hiérarchie dans `config/packages/security.yaml`.
- **Zones protégées** : `access_control` dans `config/packages/security.yaml` (`/admin` réservé à `ROLE_ADMIN`, `/private` aux utilisateurs connectés).
- **Politique de mot de passe** : modifier `App\Validator\StrongPassword` ; elle s'applique partout (inscription, changement, administration).
- **Force brute** : `max_attempts` et `interval` de `login_throttling`.

## Déploiement

Chaque push sur `main` déclenche `.github/workflows/deploy.yml`, exécuté par un runner auto-hébergé sur la machine de test : `git pull`, `composer install`, migrations, vidage du cache.
