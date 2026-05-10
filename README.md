# EduTrack & Archive System 🎓

> نظام إدارة وأرشفة أكاديمية ذكي — Smart Academic Management & Archive System

## Architecture

```
┌──────────────────────────────────────────────────────────┐
│                    Client Layer                          │
│              (Mobile / SPA / Frontend)                   │
└──────────────────────┬───────────────────────────────────┘
                       │ REST API (JSON)
┌──────────────────────┴───────────────────────────────────┐
│              Laravel Core Engine (:8000)                  │
│  ┌─────────┐  ┌──────────┐  ┌───────────┐  ┌──────────┐│
│  │ Sanctum │  │ Skinny   │  │ Services  │  │ Repos    ││
│  │  Auth   │  │ Contrlrs │  │  Layer    │  │  Layer   ││
│  └─────────┘  └──────────┘  └───────────┘  └──────────┘│
│  ┌─────────────────────────────────────────────────────┐ │
│  │           Background Jobs (Queue)                   │ │
│  │  HashJob → QrCodeJob → AiMetadataJob               │ │
│  └─────────────────────────────────────────────────────┘ │
└──────────────────────┬───────────────────────────────────┘
                       │ HTTP + Redis
┌──────────────────────┴───────────────────────────────────┐
│            Python FastAPI AI Service (:8001)              │
│  ┌──────────┐  ┌───────────┐  ┌────────────────────────┐│
│  │   NLP    │  │ Metadata  │  │ Text Similarity        ││
│  │ Process  │  │ Extractor │  │ (Cosine + Jaccard)     ││
│  └──────────┘  └───────────┘  └────────────────────────┘│
└──────────────────────────────────────────────────────────┘
```

## Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Core Engine** | Laravel 12 (PHP 8.2+) | REST API, Auth, Jobs, Business Logic |
| **AI Service** | Python FastAPI | NLP, Metadata Extraction, Similarity |
| **Database** | SQLite (dev) / PostgreSQL (prod) | Data Storage with JSON columns |
| **Queue** | Database (dev) / Redis (prod) | Background Job Processing |
| **Cache** | Database (dev) / Redis (prod) | Search & Dashboard Caching |
| **Auth** | Laravel Sanctum | API Token Authentication |

## Quick Start

### 1. Laravel Core Engine

```bash
# Install PHP dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Install Sanctum (required)
composer require laravel/sanctum

# Run migrations & seed
php artisan migrate:fresh --seed

# Start development server
composer run dev
# OR manually:
php artisan serve
```

### 2. Python AI Service

```bash
# Navigate to AI service
cd ai-service

# Create virtual environment
python -m venv venv
source venv/bin/activate  # Linux/Mac
# OR
.\venv\Scripts\activate   # Windows

# Install dependencies
pip install -r requirements.txt

# Start FastAPI server
uvicorn app.main:app --host 0.0.0.0 --port 8001 --reload
```

### 3. Test Users (after seeding)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@edutrack.local | password |
| Supervisor | supervisor@edutrack.local | password |
| Researcher (أحمد) | ahmed@edutrack.local | password |

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/auth/register` | Register new user |
| POST | `/api/v1/auth/login` | Login & get token |
| POST | `/api/v1/auth/logout` | Logout (revoke token) |
| GET | `/api/v1/auth/profile` | Get user profile |

### Research Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/research` | List research (paginated, searchable) |
| POST | `/api/v1/research` | Create research + upload file |
| GET | `/api/v1/research/{id}` | Get research details |
| PUT | `/api/v1/research/{id}` | Update research |
| DELETE | `/api/v1/research/{id}` | Delete research |
| POST | `/api/v1/research/check-duplicate` | Check file duplicate |

### Archive Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/archives` | List archive records |
| POST | `/api/v1/archives` | Archive approved research |
| GET | `/api/v1/archives/{number}` | Find by archive number |

### Dashboard
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/dashboard/stats` | Get cached statistics |
| POST | `/api/v1/dashboard/refresh` | Force refresh cache |

### AI Service (FastAPI)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `http://localhost:8001/health` | Health check |
| POST | `http://localhost:8001/api/v1/process-text` | NLP text processing |
| POST | `http://localhost:8001/api/v1/extract-metadata` | Metadata extraction |
| POST | `http://localhost:8001/api/v1/extract-keywords` | Keyword extraction |
| POST | `http://localhost:8001/api/v1/similarity` | Text similarity |
| GET | `http://localhost:8001/docs` | Swagger UI (interactive) |

## Running Tests

### PHP Tests
```bash
php artisan test
# OR specific tests:
php artisan test --filter=FileHashingServiceTest
php artisan test --filter=ArchiveServiceTest
php artisan test --filter=AhmedJourneyTest
php artisan test --filter=AuthApiTest
```

### Python Tests
```bash
cd ai-service
python -m pytest tests/ -v
```

## Project Structure

```
edutrack-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/               # Skinny API Controllers
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── ResearchController.php
│   │   │   │   ├── ArchiveController.php
│   │   │   │   └── DashboardController.php
│   │   │   └── ...                # Existing web controllers
│   │   ├── Requests/              # Form Request Validation
│   │   └── Resources/             # JSON API Resources
│   ├── Interfaces/                # Repository Contracts
│   ├── Jobs/                      # Background Jobs
│   │   ├── GenerateFileHashJob.php
│   │   ├── GenerateQrCodeJob.php
│   │   └── ExtractAiMetadataJob.php
│   ├── Models/                    # Eloquent Models
│   ├── Repositories/              # Data Access Layer
│   ├── Services/                  # Business Logic Layer
│   │   ├── ResearchService.php
│   │   ├── ArchiveService.php
│   │   ├── FileHashingService.php
│   │   ├── QrCodeService.php
│   │   └── AiIntegrationService.php
│   └── Providers/
│       └── RepositoryServiceProvider.php
├── ai-service/                    # Python FastAPI Microservice
│   ├── app/
│   │   ├── core/
│   │   │   ├── config.py
│   │   │   └── redis_client.py
│   │   ├── routers/
│   │   │   ├── nlp.py
│   │   │   ├── metadata.py
│   │   │   └── similarity.py
│   │   └── main.py
│   ├── tests/
│   │   └── test_endpoints.py
│   └── requirements.txt
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── tests/
│   ├── Unit/
│   │   ├── FileHashingServiceTest.php
│   │   └── ArchiveServiceTest.php
│   └── Feature/
│       ├── AhmedJourneyTest.php
│       └── AuthApiTest.php
└── config/
    └── ai_service.php
```

## Design Patterns Used

- **Repository Pattern**: Data access abstraction via interfaces
- **Service Pattern**: Business logic encapsulation
- **Dependency Injection**: Via Laravel's IoC container
- **Skinny Controllers**: Controllers only handle HTTP, delegate to services
- **Job Chaining**: Sequential background processing (Hash → QR → AI)
- **SOLID Principles**: Each class has a single responsibility
- **DRY**: Shared base repository, reusable services
