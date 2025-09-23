# BileMo

Project 7: Create a web service exposing an API - OpenClassrooms course: Application developer - PHP/Symfony

This project consists of building a **RESTful API** with **Symfony** that allows partner platforms to access the catalog of high-end mobile phones offered by **BileMo**.  

⚠️ BileMo **doesn't sell directly** on its website. The business model is **B2B only**: external platforms can consume the API to integrate BileMo’s product catalog into their own services.  

### Available Features
- Retrieve the list of BileMo products  
- Retrieve the details of a specific BileMo product  
- Retrieve the list of users linked to a client account  
- Retrieve the details of a specific user linked to a client account  
- Add a new user linked to a client account  
- Delete a user linked to a client account  

🔐 Only **registered API clients** can access the endpoints. Authentication is required via **JWT Token**.

The API returns data in **JSON**, respects levels **1, 2, and 3 of Richardson’s maturity model**, and supports **response caching** for performance optimization.  

---

## Prerequisites

Before installing the project, ensure you have the following installed on your system:

- [**PHP >= 8.1**](https://www.php.net/downloads.php)  
- [**Composer**](https://getcomposer.org/download/)  
- [**Symfony CLI**](https://symfony.com/download) (optional but recommended)  
- [**PostgreSQL**](https://www.postgresql.org/download) or another database engine supported by Doctrine ORM  
- [**Git**](https://git-scm.com/downloads)  
- An API testing client such as **Postman**

---

## Installation

Follow these steps to set up the project on your local machine:

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/bilemo.git
cd bilemo
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment Variables
Duplicate the `.env` file to create a `.env.local` file:
```bash
cp .env .env.local
```

Edit `.env.local` to configure your database connection (replace with your own credentials):
```
DATABASE_URL="postgresql://postgres:motdepasse@127.0.0.1:5432/bilemo?serverVersion=17.2&charset=utf8"
```

### 4. Create the Database
```bash
php bin/console doctrine:database:create
```

### 5. Run Migrations
```bash
php bin/console doctrine:migrations:migrate
```

### 6. Load Fixtures (optional, to add demo data)
```bash
php bin/console doctrine:fixtures:load
```

### 7. Start the Local Server
Using Symfony CLI:
```bash
symfony server:start
```

The API will be available at:  
👉 [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## Authentication

The API requires secure authentication. 
JWT (JSON Web Token) – validate a signed token for each request

Clients must include their token in the request header:
```bash
Authorization: Bearer <your_token>
```

---

## Contact

For any questions or suggestions, feel free to contact: **kbartholomot@gmail.com**  
