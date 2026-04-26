# Guide FreeSQLDatabase - Étape par Étape

## Étape 1 : Créer ta base de données

1. Sur **freesqldatabase.com**, clique sur **"Get your free database now"**
2. Remplis le formulaire avec ton email
3. Tu vas recevoir un email avec tes identifiants

## Étape 2 : Récupérer les identifiants (IMPORTANT)

Dans l'email, tu trouveras ces informations :

```
Database Name: sql12345678
Host: sql12.freesqldatabase.com
Port: 3306
Username: sql12345678
Password: abcdefgh1234
```

**Garde-les précieusement !** Tu en auras besoin pour Render.

## Étape 3 : Importer ta base de données

### Option A : Via phpMyAdmin (le plus simple)

1. Va sur : `https://www.phpmyadmin.co/`
2. Connecte-toi avec :
   - **Serveur** : `sql12.freesqldatabase.com` (ton host)
   - **Utilisateur** : `sql12345678` (ton username)
   - **Mot de passe** : ton password
3. Une fois connecté :
   - Clique sur ta base de données (ex: `sql12345678`)
   - Va dans l'onglet **"Importer"**
   - Clique sur **"Choisir un fichier"**
   - Sélectionne le fichier `uber_cueillette.sql` de ton projet
   - Clique sur **"Exécuter"**

### Option B : Via la ligne de commande MySQL

```bash
mysql -h sql12.freesqldatabase.com -u sql12345678 -p sql12345678 < uber_cueillette.sql
```

Puis entre ton mot de passe quand demandé.

## Étape 4 : Vérifier l'importation

Dans phpMyAdmin, tu dois voir apparaître ces tables :
- `admin`
- `agriculteur`
- `candidature`
- `gouvernorat`
- `offre`
- `ouvrier`
- `type_fruit`

## Étape 5 : Configurer Render avec ces identifiants

1. Va sur ton service Render : [dashboard.render.com](https://dashboard.render.com)
2. Clique sur ton service `uber-cueillette`
3. Va dans l'onglet **"Environment"**
4. Ajoute ces variables :

```
DB_HOST=sql12.freesqldatabase.com
DB_PORT=3306
DB_NAME=sql12345678
DB_USER=sql12345678
DB_PASSWORD=abcdefgh1234
```

> ⚠️ Remplace les valeurs par TES vrais identifiants reçus par email !

5. Clique sur **"Save Changes"**
6. Render va redémarrer ton application automatiquement

## Étape 6 : Tester

Attends 2-3 minutes que Render redéploie, puis visite ton URL :
```
https://uber-cueillette.onrender.com
```

Essaye de te connecter ou de t'inscrire pour vérifier que la base de données fonctionne.

---

## Problèmes courants

| Problème | Solution |
|----------|----------|
| "Access denied" | Vérifie que le mot de passe est correct dans les variables d'environnement |
| "Unknown database" | Vérifie que DB_NAME correspond bien au nom de ta base |
| "Can't connect" | Vérifie DB_HOST et DB_PORT (généralement 3306) |
| Tables vides | Recommence l'importation du fichier SQL |

---

## Besoin d'aide ?

Si tu bloques à une étape, dis-moi :
1. Où tu en es exactement ?
2. Quel message d'erreur tu vois ?
3. As-tu reçu l'email avec les identifiants ?

