# Rapport Technique - Customer Review Analysis Platform

**Projet:** Plateforme d'Analyse d'Avis Clients avec IA  
**Étudiant:** GitAlberto  
**Date:** 30 janvier 2026  
**Version:** 1.0

---

## Table des Matières

1. [Architecture du Système](#1-architecture-du-système)
2. [Modèle de Données](#2-modèle-de-données)
3. [Méthode d'Intelligence Artificielle](#3-méthode-dintelligence-artificielle)
4. [Tests API](#4-tests-api)
5. [Captures d'Écran](#5-captures-décran)

---

## 1. Architecture du Système

### 1.1 Vue d'Ensemble

L'application suit une **architecture client-serveur séparée** (découplée) avec communication via API REST.

```
┌─────────────────────────────────────────────────────┐
│                   UTILISATEUR                        │
└──────────────────┬──────────────────────────────────┘
                   │
                   │ HTTP/HTTPS
                   │
┌──────────────────▼──────────────────────────────────┐
│              FRONTEND (Vue 3)                        │
│  ┌────────────────────────────────────────────┐    │
│  │  • Vue Router (Navigation)                  │    │
│  │  • Pinia (State Management)                 │    │
│  │  • Axios (HTTP Client)                      │    │
│  │  • Chart.js (Visualisations)                │    │
│  └────────────────────────────────────────────┘    │
│                                                      │
│  Port: 5173 (dev)                                   │
└──────────────────┬──────────────────────────────────┘
                   │
                   │ API REST (JSON)
                   │ Bearer Token Auth
                   │
┌──────────────────▼──────────────────────────────────┐
│             BACKEND (Laravel 12)                     │
│                                                      │
│  ┌────────────────────────────────────────────┐    │
│  │           COUCHE API (Routes)               │    │
│  │     • /api/register, /api/login             │    │
│  │     • /api/reviews (CRUD)                   │    │
│  │     • /api/dashboard/stats                  │    │
│  └──────────────┬─────────────────────────────┘    │
│                 │                                    │
│  ┌──────────────▼─────────────────────────────┐    │
│  │      COUCHE CONTROLLERS                     │    │
│  │  • AuthController                           │    │
│  │  • ReviewController                         │    │
│  │  • AnalysisController                       │    │
│  │  • DashboardController                      │    │
│  └──────────────┬─────────────────────────────┘    │
│                 │                                    │
│  ┌──────────────▼─────────────────────────────┐    │
│  │       COUCHE SERVICES                       │    │
│  │  • AiAnalysisService (OpenAI)               │    │
│  │  • StatisticsService                        │    │
│  └──────────────┬─────────────────────────────┘    │
│                 │                                    │
│  ┌──────────────▼─────────────────────────────┐    │
│  │         COUCHE MODELS                       │    │
│  │  • User (Eloquent ORM)                      │    │
│  │  • Review (Eloquent ORM)                    │    │
│  └──────────────┬─────────────────────────────┘    │
│                 │                                    │
│  Port: 8000                                         │
└─────────────────┼───────────────────────────────────┘
                  │
                  │ SQL Queries
                  │
┌─────────────────▼───────────────────────────────────┐
│           BASE DE DONNÉES (SQLite)                   │
│  • users                                             │
│  • reviews                                           │
│  • personal_access_tokens                            │
└──────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────┐
│          SERVICE EXTERNE (OpenAI API)                 │
│  • Modèle: GPT-3.5-turbo                             │
│  • Analyse de sentiment                              │
│  • Extraction de topics                              │
└──────────────────────────────────────────────────────┘
```

### 1.2 Stack Technologique

#### Backend
- **Framework:** Laravel 12 (PHP 8.2+)
- **Base de données:** SQLite (fichier local)
- **Authentification:** Laravel Sanctum (tokens API)
- **ORM:** Eloquent
- **IA:** OpenAI PHP Client (GPT-3.5-turbo)

#### Frontend
- **Framework:** Vue 3 (Composition API)
- **Build Tool:** Vite 5.0
- **Routing:** Vue Router 4.6
- **State Management:** Pinia
- **HTTP Client:** Axios
- **Visualisations:** Chart.js
- **Styling:** CSS Custom Properties (Glassmorphism)

### 1.3 Flux de Communication

#### Flux d'Authentification

```
┌─────────┐                ┌─────────┐               ┌──────────┐
│Frontend │                │ Backend │               │ Database │
└────┬────┘                └────┬────┘               └────┬─────┘
     │                          │                         │
     │ POST /api/login          │                         │
     │ {email, password}        │                         │
     ├─────────────────────────►│                         │
     │                          │                         │
     │                          │ SELECT * FROM users     │
     │                          │ WHERE email = ?         │
     │                          ├────────────────────────►│
     │                          │                         │
     │                          │ ◄───────────────────────┤
     │                          │ User data               │
     │                          │                         │
     │                          │ CREATE token            │
     │                          ├────────────────────────►│
     │                          │                         │
     │ ◄─────────────────────────                         │
     │ {user, token}            │                         │
     │                          │                         │
     │ Store token in           │                         │
     │ localStorage             │                         │
     │                          │                         │
```

#### Flux de Création d'Avis avec Analyse IA

```
┌─────────┐      ┌─────────┐      ┌────────┐      ┌──────────┐
│Frontend │      │ Backend │      │ OpenAI │      │ Database │
└────┬────┘      └────┬────┘      └───┬────┘      └────┬─────┘
     │                │                │                │
     │ POST /api/reviews                │                │
     │ {content}      │                │                │
     ├───────────────►│                │                │
     │                │                │                │
     │                │ POST /v1/chat  │                │
     │                │ {prompt}       │                │
     │                ├───────────────►│                │
     │                │                │                │
     │                │ ◄───────────────                │
     │                │ {sentiment,    │                │
     │                │  score, topics}│                │
     │                │                │                │
     │                │ INSERT INTO reviews             │
     │                ├────────────────────────────────►│
     │                │                                 │
     │ ◄───────────────                                 │
     │ {review}       │                                 │
     │                │                                 │
```

### 1.4 Sécurité

#### Authentification
- **Laravel Sanctum:** Tokens Bearer stockés dans `personal_access_tokens`
- **Hachage:** Bcrypt pour les mots de passe
- **Validation:** Middleware `auth:sanctum` sur toutes les routes protégées

#### Autorisation
- **Rôles:** `admin` et `user`
- **Contrôle d'accès:**
  - Les utilisateurs voient uniquement leurs propres avis
  - Les admins ont accès à tous les avis et au dashboard
  - Vérification propriétaire sur update/delete

#### Protection CORS
- Configuration CORS dans Laravel pour autoriser le frontend (localhost:5173)

---

## 2. Modèle de Données

### 2.1 Schéma Entité-Association (ERD)

```
┌─────────────────────────────┐
│          USERS              │
├─────────────────────────────┤
│ PK  id: bigint              │
│     name: string            │
│     email: string (unique)  │
│     password: string (hash) │
│     role: enum(admin,user)  │
│     created_at: timestamp   │
│     updated_at: timestamp   │
└──────────┬──────────────────┘
           │
           │ 1:N
           │
           │
┌──────────▼──────────────────┐
│         REVIEWS             │
├─────────────────────────────┤
│ PK  id: bigint              │
│ FK  user_id: bigint         │
│     content: text           │
│     sentiment: enum         │
│       (positive,neutral,    │
│        negative)            │
│     score: integer (0-100)  │
│     topics: JSON array      │
│     created_at: timestamp   │
│     updated_at: timestamp   │
└─────────────────────────────┘


┌─────────────────────────────────────┐
│   PERSONAL_ACCESS_TOKENS            │
├─────────────────────────────────────┤
│ PK  id: bigint                      │
│     tokenable_type: string          │
│     tokenable_id: bigint            │
│     name: string                    │
│     token: string (64 chars, hash)  │
│     abilities: text                 │
│     last_used_at: timestamp         │
│     expires_at: timestamp           │
│     created_at: timestamp           │
│     updated_at: timestamp           │
└─────────────────────────────────────┘
```

### 2.2 Description des Tables

#### Table `users`

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| `id` | BIGINT | PK, AUTO_INCREMENT | Identifiant unique |
| `name` | VARCHAR(255) | NOT NULL | Nom complet de l'utilisateur |
| `email` | VARCHAR(255) | NOT NULL, UNIQUE | Email (identifiant de connexion) |
| `password` | VARCHAR(255) | NOT NULL | Mot de passe haché (bcrypt) |
| `role` | ENUM | NOT NULL, DEFAULT 'user' | Rôle: 'admin' ou 'user' |
| `created_at` | TIMESTAMP | NULL | Date de création |
| `updated_at` | TIMESTAMP | NULL | Date de dernière modification |

**Index:**
- PRIMARY KEY sur `id`
- UNIQUE sur `email`

#### Table `reviews`

| Champ | Type | Contraintes | Description |
|-------|------|-------------|-------------|
| `id` | BIGINT | PK, AUTO_INCREMENT | Identifiant unique |
| `user_id` | BIGINT | FK, NOT NULL | Référence vers users.id |
| `content` | TEXT | NOT NULL | Contenu de l'avis |
| `sentiment` | ENUM | NOT NULL | Sentiment: 'positive', 'neutral', 'negative' |
| `score` | INTEGER | NOT NULL | Score 0-100 |
| `topics` | JSON | NULL | Tableau de sujets détectés |
| `created_at` | TIMESTAMP | NULL | Date de création |
| `updated_at` | TIMESTAMP | NULL | Date de dernière modification |

**Index:**
- PRIMARY KEY sur `id`
- FOREIGN KEY `user_id` REFERENCES `users(id)` ON DELETE CASCADE
- INDEX sur `user_id` (pour optimiser les requêtes par utilisateur)
- INDEX sur `sentiment` (pour les filtres)

#### Table `personal_access_tokens`

Gérée automatiquement par Laravel Sanctum pour stocker les tokens d'authentification API.

### 2.3 Relations

- **User → Reviews:** Un utilisateur peut avoir plusieurs avis (1:N)
- **Review → User:** Chaque avis appartient à un utilisateur (N:1)
- **Cascade Delete:** Si un utilisateur est supprimé, tous ses avis sont supprimés

### 2.4 Données de Test

#### Utilisateurs pré-configurés

| ID | Name | Email | Password | Role |
|----|------|-------|----------|------|
| 1 | Admin User | admin@example.com | password | admin |
| 2 | Normal User | user@example.com | password | user |

---

## 3. Méthode d'Intelligence Artificielle

### 3.1 Vue d'Ensemble

L'application utilise **OpenAI GPT-3.5-turbo** pour analyser automatiquement le sentiment et extraire les sujets des avis clients.

### 3.2 Service `AiAnalysisService`

#### Architecture

```php
class AiAnalysisService
{
    public function analyzeReview(string $text): array
    {
        // 1. Appel API OpenAI
        // 2. Parsing de la réponse JSON
        // 3. Validation des données
        // 4. Fallback si erreur
    }
    
    private function fallbackAnalysis(string $text): array
    {
        // Analyse par mots-clés si OpenAI indisponible
    }
}
```

### 3.3 Intégration OpenAI

#### Configuration

```env
OPENAI_API_KEY=sk-proj-...
```

#### Modèle Utilisé
- **Modèle:** `gpt-3.5-turbo`
- **Température:** 0.3 (pour des résultats cohérents et déterministes)
- **Format de réponse:** JSON structuré

#### Prompt Engineering

Le système utilise deux messages pour guider GPT:

**Message Système:**
```
You are a sentiment analysis expert. You can analyze customer reviews 
in both French and English. Always respond in JSON format.
```

**Message Utilisateur (template):**
```
Analyze this customer review (in French or English) and provide a 
JSON response with the following structure:
{
  "sentiment": "positive|neutral|negative",
  "score": <number between 0-100>,
  "topics": ["topic1", "topic2", "topic3"]
}

Review: {$text}

Respond ONLY with valid JSON, no additional text. Detect topics in 
the same language as the review.
```

### 3.4 Format de Réponse IA

#### Exemple 1: Avis Positif (Français)

**Input:**
```
"Excellent produit, très satisfait de mon achat ! La livraison était rapide."
```

**Output OpenAI:**
```json
{
  "sentiment": "positive",
  "score": 92,
  "topics": ["qualité", "satisfaction", "livraison"]
}
```

#### Exemple 2: Avis Négatif (Anglais)

**Input:**
```
"Terrible customer service, very slow delivery and poor quality."
```

**Output OpenAI:**
```json
{
  "sentiment": "negative",
  "score": 18,
  "topics": ["customer service", "delivery", "quality"]
}
```

### 3.5 Validation des Données

Après réception de la réponse OpenAI, le système effectue plusieurs validations:

```php
// 1. Validation du sentiment
$validSentiments = ['positive', 'neutral', 'negative'];
if (!in_array($analysis['sentiment'], $validSentiments)) {
    $analysis['sentiment'] = 'neutral';
}

// 2. Normalisation du score (0-100)
$analysis['score'] = max(0, min(100, intval($analysis['score'])));

// 3. Validation des topics
if (!isset($analysis['topics']) || !is_array($analysis['topics'])) {
    $analysis['topics'] = [];
}
```

### 3.6 Méthode de Fallback

Si l'API OpenAI est indisponible ou retourne une erreur, le système bascule automatiquement vers une **analyse par mots-clés**.

#### Algorithme de Fallback

1. **Mots-clés positifs:** excellent, great, good, magnifique, parfait, génial...
2. **Mots-clés négatifs:** bad, terrible, awful, mauvais, nul, horrible...
3. **Comptage:** Nombre d'occurrences de chaque catégorie
4. **Calcul du sentiment:**
   - Plus de positifs → `positive`
   - Plus de négatifs → `negative`
   - Égalité → `neutral`
5. **Score:** Basé sur la différence de comptage
6. **Topics:** Détection de mots-clés thématiques (livraison, qualité, prix, service)

#### Exemple de Fallback

**Input:**
```
"Excellent service, mais prix un peu élevé"
```

**Output Fallback:**
```json
{
  "sentiment": "positive",
  "score": 70,
  "topics": ["service", "prix"]
}
```

### 3.7 Support Bilingue

L'IA détecte automatiquement la langue de l'avis (français ou anglais) et:
- Analyse le sentiment dans les deux langues
- Extrait les topics **dans la langue détectée**
- Le fallback supporte également les deux langues

### 3.8 Performance

- **Temps de réponse moyen (OpenAI):** 1-3 secondes
- **Temps de réponse (Fallback):** < 100ms
- **Coût:** ~0.002$ par analyse (GPT-3.5-turbo)

---

## 4. Tests API

### 4.1 Stratégie de Tests

L'application utilise **PHPUnit** (framework de tests Laravel) pour tester les endpoints API.

### 4.2 Configuration des Tests

```php
// phpunit.xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Base de données SQLite en mémoire pour isoler les tests.

### 4.3 Tests Implémentés

#### Structure des Tests

```
tests/
├── Feature/
│   ├── AuthControllerTest.php
│   ├── ReviewControllerTest.php
│   ├── AnalysisControllerTest.php
│   └── DashboardControllerTest.php
├── Unit/
│   ├── AiAnalysisServiceTest.php
│   └── StatisticsServiceTest.php
└── TestCase.php
```

### 4.4 Exemples de Tests

#### Test 1: Inscription Utilisateur

```php
public function test_user_can_register()
{
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure([
                 'user' => ['id', 'name', 'email', 'role'],
                 'token'
             ]);
    
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com'
    ]);
}
```

#### Test 2: Création d'Avis avec Analyse

```php
public function test_user_can_create_review_with_ai_analysis()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
                     ->postJson('/api/reviews', [
                         'content' => 'Excellent produit, très satisfait !'
                     ]);

    $response->assertStatus(201)
             ->assertJsonStructure([
                 'message',
                 'review' => [
                     'id', 'content', 'sentiment', 
                     'score', 'topics'
                 ]
             ]);
    
    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'content' => 'Excellent produit, très satisfait !'
    ]);
}
```

#### Test 3: Autorisation (User vs Admin)

```php
public function test_user_cannot_view_other_users_reviews()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $review = Review::factory()->create(['user_id' => $user2->id]);
    
    $response = $this->actingAs($user1)
                     ->getJson("/api/reviews/{$review->id}");
    
    $response->assertStatus(403);
}

public function test_admin_can_view_all_reviews()
{
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    
    $review = Review::factory()->create(['user_id' => $user->id]);
    
    $response = $this->actingAs($admin)
                     ->getJson("/api/reviews/{$review->id}");
    
    $response->assertStatus(200);
}
```

### 4.5 Commandes de Test

```bash
# Exécuter tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage

# Tests spécifiques
php artisan test --filter ReviewControllerTest

# Tests avec détails
php artisan test --verbose
```

### 4.6 Résultats Attendus

```
PASS  Tests\Feature\AuthControllerTest
✓ user can register
✓ user can login with valid credentials
✓ user cannot login with invalid credentials
✓ user can logout

PASS  Tests\Feature\ReviewControllerTest
✓ user can create review
✓ user can list their reviews
✓ user cannot view other users reviews
✓ admin can view all reviews
✓ user can update their own review
✓ user can delete their own review

PASS  Tests\Feature\DashboardControllerTest
✓ admin can access dashboard stats
✓ regular user cannot access dashboard

PASS  Tests\Unit\AiAnalysisServiceTest
✓ ai service returns valid analysis
✓ fallback analysis works when openai fails

Tests:  15 passed
Time:   2.45s
```

---

## 5. Captures d'Écran

### 5.1 Authentification

#### Login Page
![Page de Connexion](./screenshots/01_login.png)

**Fonctionnalités visibles:**
- Design glassmorphique dark mode
- Formulaire avec email et mot de passe
- Bouton de connexion avec gradient
- Lien vers inscription
- Logo avec gradient text

---

#### Register Page
![Page d'Inscription](./screenshots/02_register.png)

**Fonctionnalités visibles:**
- Formulaire d'inscription (nom, email, mot de passe, confirmation)
- Validation côté client
- Design cohérent avec login

---

### 5.2 Gestion des Avis

#### Reviews List (Utilisateur)
![Liste des Avis - Vue Utilisateur](./screenshots/03_reviews_user.png)

**Fonctionnalités visibles:**
- Cartes glassmorphiques pour chaque avis
- Score circulaire animé avec conic-gradient
- Badge de sentiment (positive/neutral/negative) avec gradient
- Topics sous forme de tags
- Filtres par sentiment
- Barre de recherche
- Pagination
- Bouton "Créer un avis"

---

#### Reviews List (Admin)
![Liste des Avis - Vue Admin](./screenshots/04_reviews_admin.png)

**Différences admin:**
- Affichage de TOUS les avis (tous utilisateurs)
- Nom de l'auteur visible sur chaque carte
- Permission de modifier/supprimer tous les avis

---

#### Create Review Form
![Formulaire de Création d'Avis](./screenshots/05_create_review.png)

**Fonctionnalités visibles:**
- Textarea pour le contenu
- Bouton "Analyser et Enregistrer"
- Design glassmorphique
- Validation (minimum 10 caractères)

---

#### Review Analysis Result
![Résultat d'Analyse IA](./screenshots/06_analysis_result.png)

**Fonctionnalités visibles:**
- Message de succès avec animation checkmark
- Résultat de l'analyse:
  - Sentiment détecté (badge coloré)
  - Score de satisfaction (barre de progression animée)
  - Topics extraits (liste de tags)
- Bouton pour voir tous les avis

---

### 5.3 Dashboard Administrateur

#### Dashboard Stats
![Dashboard Administrateur](./screenshots/07_dashboard_admin.png)

**Fonctionnalités visibles:**
- **Statistiques clés** (cartes avec icônes gradient):
  - Total d'avis
  - Score moyen
  - Avis positifs/négatifs
- **Graphique de distribution des sentiments** (Chart.js pie chart)
- **Top Topics** (graphique à barres animé)
- **Timeline des avis récents** (liste des 10 derniers)

---

### 5.4 Responsive Design

#### Mobile View - Login
![Vue Mobile - Connexion](./screenshots/08_mobile_login.png)

**Optimisations mobile:**
- Layout adapté aux petits écrans
- Navigation hamburger
- Formulaires empilés verticalement

---

#### Mobile View - Reviews List
![Vue Mobile - Liste des Avis](./screenshots/09_mobile_reviews.png)

**Optimisations mobile:**
- Cartes en colonne unique
- Filtres compacts
- Scores et badges adaptés

---

### 5.5 États et Feedbacks

#### Loading State
![État de Chargement](./screenshots/10_loading.png)

**Fonctionnalités:**
- Spinner animé avec gradient
- Message "Analyse en cours..."

---

#### Error State
![État d'Erreur](./screenshots/11_error.png)

**Fonctionnalités:**
- Message d'erreur en rouge
- Icône d'alerte
- Suggestions de correction

---

## Conclusion

Ce rapport technique documente l'architecture complète, le modèle de données, la méthode d'intelligence artificielle, les tests API et les interfaces utilisateur de la plateforme d'analyse d'avis clients.

### Points Clés

- ✅ Architecture moderne client-serveur découplée
- ✅ API REST complète avec 11 endpoints
- ✅ Authentification sécurisée avec Laravel Sanctum
- ✅ Intelligence artificielle (OpenAI GPT-3.5-turbo) avec fallback
- ✅ Support bilingue (français/anglais)
