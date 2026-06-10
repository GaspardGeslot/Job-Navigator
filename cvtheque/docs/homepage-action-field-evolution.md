# Évolution du champ `action` dans `ohrm_home_page`

## 📜 Ancien système (avant les sous-domaines)

### Structure de l'URL avant

**Toutes les URLs contenaient le thème:**
```
/{theme}/{module}/{action}
```

**Exemples:**
- `http://localhost/cvtheque/web/index.php/constructys/directory/viewMatchedCompanies`
- `http://localhost/cvtheque/web/index.php/olecio/dashboard/index`

### Champ `action` dans la base de données

Le champ `action` dans `ohrm_home_page` contenait **directement l'URL complète avec le thème**:

| id | user_role_id | action | priority |
|----|--------------|--------|----------|
| 1  | 1 (Admin)    | **constructys**/directory/viewMatchedCompanies | 100 |
| 2  | 2 (ESS)      | **constructys**/dashboard/index | 100 |
| 3  | 1 (Admin)    | **olecio**/admin/viewSystemUsers | 100 |

### Flux de redirection (ancien système)

```php
// Dans LoginController (ANCIEN CODE)
public function handle(Request $request)
{
    if ($this->getAuthUser()->isAuthenticated()) {
        $homePagePath = $this->getHomePageService()->getHomePagePath();
        // $homePagePath = "constructys/directory/viewMatchedCompanies"
        
        return $this->redirect($homePagePath);
        // Redirige vers: /constructys/directory/viewMatchedCompanies
    }
    return parent::handle($request);
}
```

**Fonctionnement:**
1. Récupère `action` depuis la DB: `"constructys/directory/viewMatchedCompanies"`
2. Redirige directement vers cette URL
3. Le router match la route `/{theme}/{module}/{action}`
4. ✅ Tout fonctionne!

---

## 🔄 Nouveau système (avec sous-domaines)

### Structure des URLs maintenant

**Deux modes possibles:**

#### Mode subdomain
```
/{module}/{action}
```
**Exemple:** `http://constructys.localhost/cvtheque/web/index.php/directory/viewMatchedCompanies`

#### Mode fallback
```
/{theme}/{module}/{action}
```
**Exemple:** `http://localhost/cvtheque/web/index.php/constructys/directory/viewMatchedCompanies`

### Problème avec l'ancien champ `action`

Si le champ `action` contient toujours le thème:

```php
// Mode subdomain avec constructys.localhost
$homePagePath = "constructys/directory/viewMatchedCompanies";  // De la DB
return $this->redirect($homePagePath);

// ❌ Résultat: http://constructys.localhost/.../constructys/directory/viewMatchedCompanies
//              Le thème "constructys" est en double!
```

**Le router essaie de parser:**
```
Path: /constructys/directory/viewMatchedCompanies
Router pense: theme = constructys, module = directory, action = viewMatchedCompanies
```

Mais on est déjà sur `constructys.localhost`, donc le thème est détecté deux fois! ❌

**Exemple visuel du problème:**

```
Mode subdomain:
┌─────────────────────────────────────────────────────────────────┐
│ URL: constructys.localhost/constructys/directory/viewCompanies │
│                            ^^^^^^^^^^^                          │
│                            Doublon!                             │
└─────────────────────────────────────────────────────────────────┘

Ce qui devrait être:
┌─────────────────────────────────────────────────────────────────┐
│ URL: constructys.localhost/directory/viewCompanies             │
│      Thème détecté par le sous-domaine                         │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Solutions possibles

### Solution 1: Nettoyer les données dans le code (temporaire)

**Avantage:** Rétrocompatible avec les données existantes  
**Inconvénient:** Code plus complexe

```php
// Dans LoginController.php
if ($useSubdomain) {
    // Si subdomain: retirer le thème de l'action
    $cleanedPath = $homePagePath;
    $validThemes = ['constructys', 'olecio', 'maraudes'];
    
    foreach ($validThemes as $validTheme) {
        if (strpos($homePagePath, $validTheme . '/') === 0) {
            // Retire "constructys/" du début
            $cleanedPath = substr($homePagePath, strlen($validTheme) + 1);
            break;
        }
    }
    return $this->redirect($cleanedPath);
}
```

**Exemple:**
```php
$homePagePath = "constructys/directory/viewMatchedCompanies";
$validTheme = "constructys";

// Calcul:
strpos("constructys/directory/...", "constructys/")  // = 0 (au début)
strlen("constructys")  // = 11
11 + 1  // = 12 (inclut le "/")
substr("constructys/directory/...", 12)  // = "directory/viewMatchedCompanies"
```

### Solution 2: Nettoyer la base de données (recommandé!)

**Avantage:** Code plus simple et logique  
**Inconvénient:** Migration nécessaire

#### Étape 1: Vérifier les données

```sql
-- Voir toutes les home pages
SELECT id, action FROM ohrm_home_page;

-- Voir lesquelles contiennent un thème
SELECT 
    id,
    action,
    CASE 
        WHEN action LIKE 'constructys/%' THEN 'CONTIENT constructys'
        WHEN action LIKE 'olecio/%' THEN 'CONTIENT olecio'
        WHEN action LIKE 'maraudes/%' THEN 'CONTIENT maraudes'
        ELSE 'OK - Pas de thème'
    END as statut
FROM ohrm_home_page;
```

#### Étape 2: Nettoyer les données

```sql
-- Retirer 'constructys/' du début
UPDATE ohrm_home_page 
SET action = SUBSTRING(action, 13)  -- 13 = longueur de 'constructys/' + 1
WHERE action LIKE 'constructys/%';

-- Retirer 'olecio/' du début
UPDATE ohrm_home_page 
SET action = SUBSTRING(action, 8)  -- 8 = longueur de 'olecio/' + 1
WHERE action LIKE 'olecio/%';

-- Retirer 'maraudes/' du début
UPDATE ohrm_home_page 
SET action = SUBSTRING(action, 10)  -- 10 = longueur de 'maraudes/' + 1
WHERE action LIKE 'maraudes/%';
```

#### Étape 3: Vérifier le résultat

```sql
SELECT id, action FROM ohrm_home_page;
```

**Résultat attendu:**

| id | user_role_id | action | priority |
|----|--------------|--------|----------|
| 1  | 1 (Admin)    | directory/viewMatchedCompanies | 100 |
| 2  | 2 (ESS)      | dashboard/index | 100 |
| 3  | 1 (Admin)    | admin/viewSystemUsers | 100 |

#### Étape 4: Simplifier le code

Une fois la DB nettoyée, simplifier `LoginController.php`:

```php
public function handle(Request $request)
{
    if ($this->getAuthUser()->isAuthenticated()) {
        $homePagePath = $this->getHomePageService()->getHomePagePath();
        $useSubdomain = $request->attributes->get('_use_subdomain', false);
        
        if ($useSubdomain) {
            // Mode subdomain: pas de thème dans l'URL
            return $this->redirect($homePagePath);
        } else {
            // Mode fallback: ajouter le thème
            return $this->redirect($request->attributes->get('theme') . "/" . $homePagePath);
        }
    }
    return parent::handle($request);
}
```

---

## 📊 Comparaison des deux approches

### Avec thème dans l'action (ancien)

```
Table: ohrm_home_page
┌────┬──────────┬───────────────────────────────────────┐
│ id │ role_id  │ action                                │
├────┼──────────┼───────────────────────────────────────┤
│ 1  │ 1        │ constructys/directory/viewCompanies   │
│ 2  │ 1        │ olecio/admin/viewSystemUsers          │
└────┴──────────┴───────────────────────────────────────┘

Problèmes:
- ❌ Doublon de thème en mode subdomain
- ❌ Besoin de nettoyage dans le code
- ❌ Action spécifique à un thème (pas flexible)
- ❌ Redondance: le thème est déjà connu par le contexte
```

### Sans thème dans l'action (nouveau)

```
Table: ohrm_home_page
┌────┬──────────┬────────────────────────────────┐
│ id │ role_id  │ action                         │
├────┼──────────┼────────────────────────────────┤
│ 1  │ 1        │ directory/viewCompanies        │
│ 2  │ 1        │ admin/viewSystemUsers          │
└────┴──────────┴────────────────────────────────┘

Avantages:
- ✅ Fonctionne en mode subdomain ET fallback
- ✅ Code plus simple et maintenable
- ✅ Actions réutilisables entre thèmes
- ✅ Plus logique architecturalement
- ✅ Séparation des responsabilités (le thème est géré ailleurs)
```

---

## 💡 Pourquoi c'était fait comme ça avant?

### Hypothèse 1: Multi-tenant par thème

Peut-être que chaque thème avait ses propres home pages différentes:
- Admin sur `constructys` → `directory/viewCompanies`
- Admin sur `olecio` → `admin/viewSystemUsers`

Dans ce cas, avoir le thème dans l'action permettait de différencier facilement les routes par thème.

### Hypothèse 2: Simplicité initiale

Au début, sans sous-domaines, c'était plus simple:
- Toutes les URLs avaient le thème dans le path
- Le champ `action` = l'URL complète
- Pas de logique conditionnelle
- Redirection directe sans transformation

---

## 🎯 Recommandation de migration

### Phase 1: Court terme (actuel)
✅ **Déjà fait:** Code de nettoyage dans `LoginController.php` pour assurer la compatibilité avec les données existantes.

### Phase 2: Moyen terme (à faire rapidement)

1. **Backup de la base de données**
```bash
mysqldump -u username -p database_name ohrm_home_page > backup_home_page.sql
```

2. **Nettoyer la base de données**
```sql
-- Script de migration
UPDATE ohrm_home_page 
SET action = SUBSTRING(action, 13)
WHERE action LIKE 'constructys/%';

UPDATE ohrm_home_page 
SET action = SUBSTRING(action, 8)
WHERE action LIKE 'olecio/%';

UPDATE ohrm_home_page 
SET action = SUBSTRING(action, 10)
WHERE action LIKE 'maraudes/%';
```

3. **Tester**
- Tester la connexion en mode subdomain
- Tester la connexion en mode fallback
- Vérifier les logs

4. **Simplifier le code** (optionnel si tout fonctionne)

### Phase 3: Long terme

1. **Documenter** dans le code que le champ `action` ne doit jamais contenir le thème
2. **Ajouter une validation** pour empêcher l'insertion de thèmes dans `action`
3. **Tests automatisés** pour vérifier l'intégrité des données

---

## 🧪 Tests de validation

### Test 1: Vérifier les données en DB

```sql
-- Ce SELECT ne doit retourner AUCUNE ligne
SELECT id, action 
FROM ohrm_home_page 
WHERE action LIKE 'constructys/%' 
   OR action LIKE 'olecio/%' 
   OR action LIKE 'maraudes/%';
```

**Résultat attendu:** `Empty set (0 rows)`

### Test 2: Tester la redirection en subdomain

```bash
# Mode subdomain
curl -L http://constructys.localhost/cvtheque/web/index.php/auth/login
# Après login, devrait rediriger vers:
# http://constructys.localhost/cvtheque/web/index.php/directory/viewMatchedCompanies
# PAS vers: .../constructys/directory/viewMatchedCompanies
```

### Test 3: Tester la redirection en fallback

```bash
# Mode fallback
curl -L http://localhost/cvtheque/web/index.php/constructys/auth/login
# Après login, devrait rediriger vers:
# http://localhost/cvtheque/web/index.php/constructys/directory/viewMatchedCompanies
```

### Test 4: Vérifier les logs

```bash
tail -f src/log/orangehrm.log | grep "LoginController"
```

**Logs attendus en mode subdomain:**
```
[LoginController] Home redirect - Theme: constructys, UseSubdomain: YES, HomePage: directory/viewMatchedCompanies
```

**Logs attendus en mode fallback:**
```
[LoginController] Home redirect - Theme: constructys, UseSubdomain: NO, HomePage: directory/viewMatchedCompanies
```

---

## 📝 Guide de migration complet

### Script SQL complet avec vérifications

```sql
-- ============================================
-- Script de migration: Nettoyage du champ action
-- ============================================

-- 1. Backup (optionnel mais recommandé)
CREATE TABLE ohrm_home_page_backup AS SELECT * FROM ohrm_home_page;

-- 2. Voir l'état actuel
SELECT 
    id,
    action as action_avant,
    CASE 
        WHEN action LIKE 'constructys/%' THEN SUBSTRING(action, 13)
        WHEN action LIKE 'olecio/%' THEN SUBSTRING(action, 8)
        WHEN action LIKE 'maraudes/%' THEN SUBSTRING(action, 10)
        ELSE action
    END as action_apres
FROM ohrm_home_page;

-- 3. Appliquer les changements
UPDATE ohrm_home_page 
SET action = SUBSTRING(action, 13)
WHERE action LIKE 'constructys/%';

UPDATE ohrm_home_page 
SET action = SUBSTRING(action, 8)
WHERE action LIKE 'olecio/%';

UPDATE ohrm_home_page 
SET action = SUBSTRING(action, 10)
WHERE action LIKE 'maraudes/%';

-- 4. Vérifier le résultat
SELECT id, action FROM ohrm_home_page;

-- 5. Vérifier qu'aucun thème ne reste
SELECT COUNT(*) as lignes_avec_theme
FROM ohrm_home_page 
WHERE action LIKE 'constructys/%' 
   OR action LIKE 'olecio/%' 
   OR action LIKE 'maraudes/%';
-- Devrait retourner: 0

-- 6. Si tout est OK, supprimer le backup (optionnel)
-- DROP TABLE ohrm_home_page_backup;
```

---

## ⚠️ Points d'attention

### 1. Le champ `action` ne doit JAMAIS contenir le thème

❌ **MAUVAIS:**
```
constructys/directory/viewMatchedCompanies
olecio/admin/viewSystemUsers
```

✅ **BON:**
```
directory/viewMatchedCompanies
admin/viewSystemUsers
```

### 2. Le thème est ajouté dynamiquement

Le thème est géré par:
- Le sous-domaine (mode subdomain)
- Le code PHP qui préfixe l'action (mode fallback)

### 3. Compatibilité ascendante

Le code actuel supporte les deux formats pour assurer une transition en douceur:
- Ancien format (avec thème) → Nettoyé automatiquement
- Nouveau format (sans thème) → Utilisé tel quel

### 4. Maintenance future

Pour ajouter une nouvelle home page, toujours utiliser le format **sans thème**:

```sql
INSERT INTO ohrm_home_page (user_role_id, action, priority)
VALUES (1, 'dashboard/index', 100);  -- ✅ Pas de thème
```

---

## 📚 Résumé

| Aspect | Ancien système | Nouveau système |
|--------|---------------|-----------------|
| **Format action** | `constructys/directory/view` | `directory/view` |
| **Ajout du thème** | Dans la DB | Dans le code (dynamique) |
| **Mode subdomain** | ❌ Ne fonctionne pas | ✅ Fonctionne |
| **Mode fallback** | ✅ Fonctionne | ✅ Fonctionne |
| **Flexibilité** | ❌ Thème fixé par rôle | ✅ Thème déterminé par contexte |
| **Maintenabilité** | ❌ Code de nettoyage complexe | ✅ Code simple |

**Conclusion:** Le nouveau système (sans thème dans `action`) est plus flexible, maintenable et compatible avec l'architecture multi-domaine.

