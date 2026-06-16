# Flux de détermination de la page d'accueil après connexion

## 📊 Architecture du système

### 1. Base de données : Table `ohrm_home_page`

La configuration des pages d'accueil est stockée en base de données:

| id | user_role_id | action | priority | enable_class |
|----|--------------|--------|----------|--------------|
| 1  | 1 (Admin)    | admin/viewSystemUsers | 100 | NULL |
| 2  | 2 (ESS)      | dashboard/index | 100 | NULL |
| 3  | 3 (Candidate)| candidature/index | 100 | CandidatureHomePageEnabler |

**Structure des champs:**
- `action`: Le chemin de la page **sans le thème!** ex: `dashboard/index`, `candidature/index`
- `user_role_id`: Associé à un rôle utilisateur (référence vers `ohrm_user_role`)
- `priority`: Plus élevé = prioritaire (en cas de plusieurs home pages pour un rôle)
- `enable_class`: Classe optionnelle pour vérifier dynamiquement si la page est activée

---

## 🔄 Flux complet après connexion

```
1. Utilisateur se connecte avec succès
   ↓
2. LoginController::handle() (ligne 114-128)
   → Vérifie si l'utilisateur est authentifié
   ↓
3. HomePageService::getHomePagePath()
   → Service qui délègue au UserRoleManager
   ↓
4. UserRoleManager::getHomePage()
   → Récupère les rôles de l'utilisateur connecté
   ↓
5. HomePageDao::getHomePagesInPriorityOrder($userRoleIds)
   → Requête SQL pour récupérer les home pages de la DB
   → Trie par priorité (DESC) puis par ID (DESC)
   ↓
6. Pour chaque home page trouvée (par ordre de priorité):
   - Si enable_class existe → instancie la classe et vérifie si enabled
   - Si enabled (ou pas de classe) → retourne ce `action`
   - Sinon continue avec la suivante
   ↓
7. LoginController redirige selon le mode:
   - Mode subdomain: /{action}
   - Mode fallback: /{theme}/{action}
```

---

## 📁 Fichiers impliqués

### 1. **LoginController.php**
`src/plugins/orangehrmAuthenticationPlugin/Controller/LoginController.php`

```php
public function handle(Request $request)
{
    if ($this->getAuthUser()->isAuthenticated()) {
        $homePagePath = $this->getHomePageService()->getHomePagePath();
        
        // Construire l'URL correctement selon le mode (subdomain ou fallback)
        $useSubdomain = $request->attributes->get('_use_subdomain', false);
        if ($useSubdomain) {
            // Mode subdomain: pas de theme dans l'URL
            return $this->redirect($homePagePath);
        } else {
            // Mode fallback: ajouter le theme
            return $this->redirect($request->attributes->get('theme') . "/" . $homePagePath);
        }
    }
    return parent::handle($request);
}
```

### 2. **HomePageService.php**
`src/plugins/orangehrmCorePlugin/Authorization/Service/HomePageService.php`

Simple service qui délègue au UserRoleManager:

```php
public function getHomePagePath(): ?string
{
    return $this->getUserRoleManager()->getHomePage();
}
```

### 3. **BasicUserRoleManager.php**
`src/plugins/orangehrmCorePlugin/Authorization/Manager/BasicUserRoleManager.php`

Logique principale (ligne 869-896):

```php
public function getHomePage(): ?string
{
    $action = null;

    // Récupère les IDs des rôles de l'utilisateur
    $userRoleIds = [];
    foreach ($this->userRoles as $role) {
        $userRoleIds[] = $role->getId();
    }
    
    // Récupère les home pages depuis la DB
    $defaultPages = $this->getHomePageDao()->getHomePagesInPriorityOrder($userRoleIds);

    // Parcourt par ordre de priorité
    foreach ($defaultPages as $defaultPage) {
        $enabled = true;
        $enableClass = $defaultPage->getEnableClass();
        $fallbackNamespace = 'OrangeHRM\\Core\\HomePage\\';

        // Si une classe d'activation existe, vérifier si enabled
        if (!empty($enableClass) && $this->getClassHelper()->classExists($enableClass, $fallbackNamespace)) {
            $enableClass = $this->getClassHelper()->getClass($enableClass, $fallbackNamespace);
            $enableClassInstance = new $enableClass();
            if ($enableClassInstance instanceof HomePageEnablerInterface) {
                $enabled = $enableClassInstance->isEnabled($this->getUser());
            }
        }
        
        if ($enabled) {
            $action = $defaultPage->getAction();
            break; // Retourne la première page enabled
        }
    }

    return $action;
}
```

### 4. **HomePageDao.php**
`src/plugins/orangehrmCorePlugin/Authorization/Dao/HomePageDao.php`

Accès à la base de données:

```php
public function getHomePagesInPriorityOrder(array $userRoleIds): array
{
    $q = $this->createQueryBuilder(HomePage::class, 'h');
    $q->leftJoin('h.userRole', 'ur');
    $q->andWhere($q->expr()->in('ur.id', ':userRoleIds'))
        ->setParameter('userRoleIds', $userRoleIds);
    $q->addOrderBy('h.priority', ListSorter::DESCENDING);
    $q->addOrderBy('h.id', ListSorter::DESCENDING);

    return $q->getQuery()->execute();
}
```

### 5. **HomePage.php (Entity)**
`src/plugins/orangehrmCorePlugin/entity/HomePage.php`

Entité Doctrine mappée sur la table `ohrm_home_page`.

---

## 🎯 Comment créer une home page pour un nouveau thème (ex: Maraudes)

### Option 1: Via SQL (Recommandé pour tester rapidement)

```sql
-- Vérifier d'abord les IDs des rôles
SELECT id, name FROM ohrm_user_role;

-- Exemple: Home page pour le rôle "ESS" (Employee Self Service)
INSERT INTO ohrm_home_page (user_role_id, action, priority, enable_class)
VALUES (
    2,  -- ID du rôle ESS (vérifiez dans ohrm_user_role)
    'dashboard/index',  -- Chemin de la page (SANS le theme!)
    100,  -- Priorité (plus élevé = plus prioritaire)
    NULL  -- Pas de classe d'activation
);

-- Exemple: Home page pour un candidat
INSERT INTO ohrm_home_page (user_role_id, action, priority, enable_class)
VALUES (
    3,  -- ID du rôle Candidate
    'candidature/index',
    100,
    'CandidatureHomePageEnabler'  -- Classe qui vérifie si le module candidature est activé
);

-- Exemple: Home page pour un admin
INSERT INTO ohrm_home_page (user_role_id, action, priority, enable_class)
VALUES (
    1,  -- ID du rôle Admin
    'admin/viewSystemUsers',
    100,
    NULL
);
```

### Option 2: Via migration/installation

Créez un script de migration qui ajoute ces entrées automatiquement lors du déploiement.

### Option 3: Interface d'administration

Si vous avez une interface d'admin, vous pouvez créer une page pour gérer ces configurations dynamiquement.

---

## 📝 Exemples de configuration par thème

### Constructys (Candidats)

```sql
-- Home page des candidats sur constructys
INSERT INTO ohrm_home_page (user_role_id, action, priority, enable_class)
VALUES (3, 'candidature/index', 100, 'CandidatureHomePageEnabler');
```

**Résultat après connexion:**
- Mode subdomain: `http://constructys.localhost/cvtheque/web/index.php/candidature/index`
- Mode fallback: `http://localhost/cvtheque/web/index.php/constructys/candidature/index`

### Olecio (Admins)

```sql
-- Home page des admins sur olecio
INSERT INTO ohrm_home_page (user_role_id, action, priority, enable_class)
VALUES (1, 'admin/viewSystemUsers', 100, NULL);
```

**Résultat après connexion:**
- Mode subdomain: `http://olecio.localhost/cvtheque/web/index.php/admin/viewSystemUsers`
- Mode fallback: `http://localhost/cvtheque/web/index.php/olecio/admin/viewSystemUsers`

### Maraudes (À configurer)

```sql
-- Exemple: Home page ESS pour maraudes
INSERT INTO ohrm_home_page (user_role_id, action, priority, enable_class)
VALUES (2, 'dashboard/index', 100, NULL);

-- Exemple: Home page candidat pour maraudes
INSERT INTO ohrm_home_page (user_role_id, action, priority, enable_class)
VALUES (3, 'candidature/index', 100, 'CandidatureHomePageEnabler');
```

**Résultat après connexion:**
- Mode subdomain: `http://maraudes.localhost/cvtheque/web/index.php/dashboard/index`
- Mode fallback: `http://localhost/cvtheque/web/index.php/maraudes/dashboard/index`

---

## 🔧 Classes d'activation (enable_class)

Les classes d'activation permettent de vérifier dynamiquement si une home page doit être utilisée.

### Interface à implémenter

```php
namespace OrangeHRM\Core\HomePage;

interface HomePageEnablerInterface
{
    public function isEnabled(User $user): bool;
}
```

### Exemple: CandidatureHomePageEnabler

```php
namespace OrangeHRM\Core\HomePage;

use OrangeHRM\Authentication\Auth\User;

class CandidatureHomePageEnabler implements HomePageEnablerInterface
{
    public function isEnabled(User $user): bool
    {
        // Vérifier si le module candidature est activé
        // Vérifier si l'utilisateur a le rôle approprié
        // etc.
        return true;
    }
}
```

---

## ⚠️ Points d'attention

### 1. **Le champ `action` ne doit PAS contenir le thème**

❌ Mauvais: `constructys/candidature/index`  
✅ Bon: `candidature/index`

Le thème est ajouté automatiquement selon le mode (subdomain ou fallback).

### 2. **Priorité des home pages**

Si un utilisateur a plusieurs rôles, le système:
1. Récupère toutes les home pages de tous ses rôles
2. Trie par `priority` DESC, puis par `id` DESC
3. Retourne la première qui est `enabled`

### 3. **Compatibilité subdomain/fallback**

Le système détecte automatiquement le mode via `$request->attributes->get('_use_subdomain')` qui est injecté par `SubdomainThemeSubscriber`.

---

## 🧪 Tester la configuration

### 1. Vérifier les home pages en DB

```sql
SELECT 
    hp.id,
    ur.name as role_name,
    hp.action,
    hp.priority,
    hp.enable_class
FROM ohrm_home_page hp
LEFT JOIN ohrm_user_role ur ON hp.user_role_id = ur.id
ORDER BY hp.priority DESC, hp.id DESC;
```

### 2. Tester la redirection

1. Se déconnecter si connecté
2. Aller sur la page de login du thème
3. Se connecter avec un utilisateur ayant le rôle approprié
4. Vérifier que la redirection se fait vers la bonne URL

### 3. Debug en cas de problème

Ajouter des logs dans `BasicUserRoleManager::getHomePage()`:

```php
foreach ($defaultPages as $defaultPage) {
    error_log('[HomePage] Testing: ' . $defaultPage->getAction() . ' for role: ' . $defaultPage->getUserRole()->getName());
    // ... reste du code
}
```

---

## 📚 Résumé

**Flow:**
1. DB (`ohrm_home_page`) → Configuration par rôle
2. DAO → Récupère les configs triées par priorité
3. Manager → Applique la logique d'activation
4. Service → Interface simple
5. Controller → Redirige selon le mode (subdomain/fallback)

**Configuration:**
- Les home pages sont **indépendantes du thème**
- Un rôle peut avoir plusieurs home pages (priorité détermine laquelle utiliser)
- Les `enable_class` permettent une logique conditionnelle

**Pour Maraudes:**
Il suffit d'ajouter les entrées appropriées dans `ohrm_home_page` avec les `action` et `user_role_id` souhaités!

