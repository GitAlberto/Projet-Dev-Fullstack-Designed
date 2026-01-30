# Documentation API - Customer Review Analysis Platform

## 📋 Vue d'ensemble

API REST pour la gestion et l'analyse automatique d'avis clients avec intelligence artificielle.

**Base URL:** `http://127.0.0.1:8000/api`  
**Format:** JSON  
**Authentification:** Laravel Sanctum (Bearer Token)

---

## 🔐 Authentification

L'API utilise Laravel Sanctum pour l'authentification. Après connexion, utilisez le token retourné dans l'en-tête `Authorization`.

### Format de l'en-tête
```http
Authorization: Bearer {votre_token}
```

---

## 📍 Endpoints

### 1. Inscription

Créer un nouveau compte utilisateur.

**Endpoint:** `POST /api/register`  
**Authentification:** Non requise

#### Requête

```json
{
  "name": "Jean Dupont",
  "email": "jean@example.com",
  "password": "motdepasse123",
  "password_confirmation": "motdepasse123"
}
```

#### Réponse Succès (201 Created)

```json
{
  "user": {
    "id": 1,
    "name": "Jean Dupont",
    "email": "jean@example.com",
    "role": "user",
    "created_at": "2026-01-30T18:00:00.000000Z",
    "updated_at": "2026-01-30T18:00:00.000000Z"
  },
  "token": "1|abc123def456..."
}
```

#### Erreurs

- `422 Unprocessable Entity` - Validation échouée
  ```json
  {
    "message": "The email has already been taken.",
    "errors": {
      "email": ["The email has already been taken."]
    }
  }
  ```

---

### 2. Connexion

Authentifier un utilisateur existant.

**Endpoint:** `POST /api/login`  
**Authentification:** Non requise

#### Requête

```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

#### Réponse Succès (200 OK)

```json
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "created_at": "2026-01-22T06:30:00.000000Z",
    "updated_at": "2026-01-22T06:30:00.000000Z"
  },
  "token": "2|xyz789abc456..."
}
```

#### Erreurs

- `401 Unauthorized` - Identifiants incorrects
  ```json
  {
    "message": "Invalid credentials"
  }
  ```

---

### 3. Déconnexion

Révoquer le token d'authentification actuel.

**Endpoint:** `POST /api/logout`  
**Authentification:** ✅ Requise

#### Réponse Succès (200 OK)

```json
{
  "message": "Logged out successfully"
}
```

---

### 4. Utilisateur Courant

Obtenir les informations de l'utilisateur authentifié.

**Endpoint:** `GET /api/me`  
**Authentification:** ✅ Requise

#### Réponse Succès (200 OK)

```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "role": "admin",
  "created_at": "2026-01-22T06:30:00.000000Z",
  "updated_at": "2026-01-22T06:30:00.000000Z"
}
```

---

### 5. Liste des Avis

Obtenir la liste des avis avec filtres et pagination.

**Endpoint:** `GET /api/reviews`  
**Authentification:** ✅ Requise

#### Paramètres de requête (optionnels)

| Paramètre | Type | Description |
|-----------|------|-------------|
| `sentiment` | string | Filtrer par sentiment: `positive`, `neutral`, `negative`, `all` |
| `search` | string | Rechercher dans le contenu des avis |
| `page` | integer | Numéro de page (défaut: 1) |

#### Exemple de requête

```http
GET /api/reviews?sentiment=positive&search=excellent&page=1
```

#### Réponse Succès (200 OK)

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "user_id": 2,
      "content": "Excellent produit, très satisfait de mon achat !",
      "sentiment": "positive",
      "score": 92,
      "topics": ["qualité", "satisfaction"],
      "created_at": "2026-01-30T10:00:00.000000Z",
      "updated_at": "2026-01-30T10:00:00.000000Z",
      "user": {
        "id": 2,
        "name": "Jean Dupont"
      }
    }
  ],
  "first_page_url": "http://127.0.0.1:8000/api/reviews?page=1",
  "from": 1,
  "last_page": 1,
  "per_page": 10,
  "to": 1,
  "total": 1
}
```

#### Règles d'Autorisation

- **Utilisateurs normaux:** Voient uniquement leurs propres avis
- **Administrateurs:** Voient tous les avis

---

### 6. Créer un Avis

Créer un nouvel avis avec analyse IA automatique.

**Endpoint:** `POST /api/reviews`  
**Authentification:** ✅ Requise

#### Requête

```json
{
  "content": "Le service client est vraiment décevant, attente trop longue."
}
```

#### Réponse Succès (201 Created)

```json
{
  "message": "Review created and analyzed successfully",
  "review": {
    "id": 2,
    "user_id": 2,
    "content": "Le service client est vraiment décevant, attente trop longue.",
    "sentiment": "negative",
    "score": 25,
    "topics": ["service", "attente"],
    "created_at": "2026-01-30T18:30:00.000000Z",
    "updated_at": "2026-01-30T18:30:00.000000Z",
    "user": {
      "id": 2,
      "name": "Jean Dupont"
    }
  }
}
```

#### Validation

- `content` : requis, chaîne, minimum 10 caractères

#### Erreurs

- `422 Unprocessable Entity` - Validation échouée
  ```json
  {
    "message": "The content field is required.",
    "errors": {
      "content": ["The content field is required."]
    }
  }
  ```

---

### 7. Détails d'un Avis

Obtenir les détails complets d'un avis spécifique.

**Endpoint:** `GET /api/reviews/{id}`  
**Authentification:** ✅ Requise

#### Réponse Succès (200 OK)

```json
{
  "id": 1,
  "user_id": 2,
  "content": "Excellent produit, très satisfait de mon achat !",
  "sentiment": "positive",
  "score": 92,
  "topics": ["qualité", "satisfaction"],
  "created_at": "2026-01-30T10:00:00.000000Z",
  "updated_at": "2026-01-30T10:00:00.000000Z",
  "user": {
    "id": 2,
    "name": "Jean Dupont",
    "email": "jean@example.com"
  }
}
```

#### Erreurs

- `403 Forbidden` - Utilisateur non autorisé à voir cet avis
  ```json
  {
    "message": "Unauthorized"
  }
  ```
- `404 Not Found` - Avis inexistant

---

### 8. Modifier un Avis

Mettre à jour un avis existant (ré-analyse automatique si le contenu change).

**Endpoint:** `PUT /api/reviews/{id}`  
**Authentification:** ✅ Requise

#### Requête

```json
{
  "content": "Produit correct mais prix un peu élevé"
}
```

#### Réponse Succès (200 OK)

```json
{
  "message": "Review updated successfully",
  "review": {
    "id": 1,
    "user_id": 2,
    "content": "Produit correct mais prix un peu élevé",
    "sentiment": "neutral",
    "score": 55,
    "topics": ["prix", "qualité"],
    "created_at": "2026-01-30T10:00:00.000000Z",
    "updated_at": "2026-01-30T18:45:00.000000Z"
  }
}
```

#### Règles d'Autorisation

- L'utilisateur doit être propriétaire de l'avis OU administrateur

#### Erreurs

- `403 Forbidden` - Non autorisé
- `422 Unprocessable Entity` - Validation échouée

---

### 9. Supprimer un Avis

Supprimer définitivement un avis.

**Endpoint:** `DELETE /api/reviews/{id}`  
**Authentification:** ✅ Requise

#### Réponse Succès (200 OK)

```json
{
  "message": "Review deleted successfully"
}
```

#### Règles d'Autorisation

- L'utilisateur doit être propriétaire de l'avis OU administrateur

#### Erreurs

- `403 Forbidden` - Non autorisé
- `404 Not Found` - Avis inexistant

---

### 10. Analyser un Texte

Analyser un texte avec l'IA sans créer d'avis (endpoint utilitaire).

**Endpoint:** `POST /api/analyze`  
**Authentification:** ✅ Requise

#### Requête

```json
{
  "text": "Amazing product! Fast delivery and great quality."
}
```

#### Réponse Succès (200 OK)

```json
{
  "sentiment": "positive",
  "score": 95,
  "topics": ["delivery", "quality"]
}
```

---

### 11. Statistiques Dashboard

Obtenir les statistiques globales (admin uniquement).

**Endpoint:** `GET /api/dashboard/stats`  
**Authentification:** ✅ Requise (Admin)

#### Réponse Succès (200 OK)

```json
{
  "stats": {
    "total_reviews": 156,
    "avg_score": 68.5,
    "sentiment_distribution": {
      "positive": 89,
      "neutral": 42,
      "negative": 25
    },
    "top_topics": {
      "qualité": 78,
      "prix": 65,
      "livraison": 52,
      "service": 41
    },
    "recent_reviews": [
      {
        "id": 156,
        "content": "Super expérience !",
        "sentiment": "positive",
        "score": 95,
        "created_at": "2026-01-30T18:00:00.000000Z",
        "user": {
          "id": 12,
          "name": "Marie Martin"
        }
      }
    ]
  }
}
```

#### Erreurs

- `403 Forbidden` - Accès réservé aux administrateurs
  ```json
  {
    "message": "Unauthorized"
  }
  ```

---

## 🔒 Codes de Statut HTTP

| Code | Description |
|------|-------------|
| 200 | Succès - Requête traitée avec succès |
| 201 | Créé - Ressource créée avec succès |
| 401 | Non authentifié - Token manquant ou invalide |
| 403 | Interdit - Utilisateur non autorisé pour cette action |
| 404 | Non trouvé - Ressource inexistante |
| 422 | Entité non traitable - Erreur de validation |
| 500 | Erreur serveur - Erreur interne |

---

## 🧪 Comptes de Test

Pour tester l'API, utilisez ces comptes pré-configurés :

### Compte Administrateur
- **Email:** `admin@example.com`
- **Mot de passe:** `password`
- **Rôle:** `admin`
- **Accès:** Tous les avis + Dashboard

### Compte Utilisateur
- **Email:** `user@example.com`
- **Mot de passe:** `password`
- **Rôle:** `user`
- **Accès:** Ses propres avis uniquement

---

## 🌐 Fonctionnalités IA

### Analyse Automatique

Chaque avis créé ou modifié est automatiquement analysé par OpenAI GPT-3.5-turbo.

**Données extraites :**
- **Sentiment :** `positive`, `neutral`, ou `negative`
- **Score :** 0-100 (satisfaction globale)
- **Topics :** Tableau de sujets détectés

**Support bilingue :** Français et anglais

**Fallback :** Si l'API OpenAI est indisponible, un système d'analyse basique par mots-clés prend le relais.

---

## 📦 Exemples cURL

### Connexion
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

### Créer un avis
```bash
curl -X POST http://127.0.0.1:8000/api/reviews \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"content":"Excellent produit !"}'
```

### Liste des avis
```bash
curl -X GET "http://127.0.0.1:8000/api/reviews?sentiment=positive" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🛠️ Environnement de Développement

### Démarrer le serveur

```bash
cd review-platform-backend
php artisan serve
```

Le serveur démarre sur `http://127.0.0.1:8000`

### Configuration

Copier `.env.example` vers `.env` et configurer :
- `OPENAI_API_KEY` : Votre clé API OpenAI
- `DB_CONNECTION=sqlite` : Base de données SQLite

---

**Version:** 1.0  
**Dernière mise à jour:** 30 janvier 2026
