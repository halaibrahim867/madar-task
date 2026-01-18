# Madaar Solutions – Mini RAG System with WebSocket Streaming (Laravel)

A **Mini Retrieval-Augmented Generation (RAG) System** built with Laravel that allows authenticated users to upload PDF files and chat with a language model (LLM) that answers questions based on the uploaded content, with **real-time streamed responses** via WebSocket.

---

## Table of Contents

1. [System Architecture & Design](#system-architecture--design)  
2. [End-to-End Data Flow](#end-to-end-data-flow)  
3. [Local Setup & Run Instructions](#local-setup--run-instructions)  
4. [How to Use](#how-to-use)  
   - [Authenticate](#authenticate)  
   - [Upload a PDF](#upload-a-pdf)  
   - [Connect to WebSocket & Chat](#connect-to-websocket--chat)  
5. [Environment Variables & API Keys](#environment-variables--api-keys)  
6. [Dependencies & Libraries](#dependencies--libraries)  
7. [Sample Flow / Example](#sample-flow--example)  
8. [Troubleshooting & Notes](#troubleshooting--notes)  

---

## System Architecture & Design

- **Authentication:** Laravel Sanctum (or Passport) ensures all API and WebSocket requests are authenticated.  
- **PDF Handling:** Users upload PDFs → text extraction → chunking → embeddings → store in Qdrant (vector database).  
- **Vector Storage:** Qdrant stores embeddings for semantic search. Each PDF is scoped per user.  
- **WebSocket Chat:** Users connect to `/ws/chat` → query sent → relevant PDF chunks retrieved → query LLM → response streamed in real-time.  
- **Design Principles:** Clean Code, SOLID, API versioning (`/api/v1/...`), modular and extensible.  
- **Security:** Unauthorized WebSocket connections rejected instantly, logged, and monitored.  

---

## End-to-End Data Flow

1. **User Authentication:** User logs in → receives API token.  
2. **PDF Upload:** User uploads PDF → validated → text extracted → chunked → embeddings generated.  
3. **Vector Storage:** Embeddings upserted into Qdrant, scoped by user.  
4. **Query via WebSocket:** User sends a message → system retrieves relevant chunks → sends to LLM → streams response back in real-time.  
5. **Response Handling:** WebSocket client receives partial response continuously until completion.  

---

## Local Setup & Run Instructions

### Requirements

- PHP 7.3+  
- Composer  
- MySQL/PostgreSQL  
- Node.js (optional for front-end testing)  
- Docker (for Qdrant)  

### Steps

```bash
# Clone the repository
git clone https://github.com/halaibrahim867/madar-task.git
cd madar-task

# Install PHP dependencies
composer install

# Install Node dependencies (optional)
npm install
npm run dev   # or npm run build for production

# Copy environment variables
cp .env.example .env

# Generate Laravel app key
php artisan key:generate

# Configure DB in .env then run migrations
php artisan migrate --seed
php artisan storage:link

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# Start queue worker
php artisan queue:work --tries=3
