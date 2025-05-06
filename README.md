# loCode – Local Developer Toolset

**loCode** is a self-hosted web application designed for developers who need a quick and reliable set of tools for everyday tasks—without relying on online services that could compromise data security. Built with **PHP Symfony** on the backend and **AlpineJS** on the frontend, loCode provides a fast, privacy-focused experience that runs entirely on your local machine or private server.

## 🛠 Features  
- **Hash Functions** – Generate MD5, SHA-256, and other cryptographic hashes.  
- **Serialization Tools** – Encode/decode PHP serialize/unserialize data.  
- **Base64 Utilities** – Encode and decode Base64 data.  
- **URL Parsing** – Break down URLs into components for easy analysis.  
- **JSON Formatter** – Pretty-print and validate JSON data.  
- **JWT Decoder** – Decode JWT tokens.  
- **Crontab** – Crontab generator.  
- **cURL** – cURL command generator.  
- **Token Generator** – Generate passwords or tokens.  
- **Apache** – Redirections, VirtualHost generator.
- **And More…** – Expanding toolset for developers’ daily needs.  

## 🔒 Why Self-Host?  
Online developer tools may store your pasted data, intentionally or unintentionally. Sensitive information like API keys, credentials, or proprietary code snippets could be logged or analyzed. **loCode** eliminates this risk by keeping everything local—no tracking, no analytics, no data leaks.  

## 🚀 Quick Start  

### Running with Docker  
You can quickly deploy **loCode** using Docker and `docker-compose`.  

1. Clone the repository:  
   ```sh
   git clone https://github.com/your-repo/loCode.git
   cd loCode
   ```  
2. Run the app using Docker Compose:  
   ```sh
   docker-compose up -d
   ```  
3. Open your browser and access the app at:  
   ```
   http://localhost:5180
   ```  

### Example `docker-compose.yml`  

If you want to run **loCode** on a different port than `5180`, use the following `docker-compose.yml`:  

```yaml
services:
   php:
      build:
      context: ./
      dockerfile: ./server/Dockerfile
      container_name: symfony_php
      working_dir: /var/www/html
      volumes:
      - ./app/:/var/www/html
      ports:
      - "5190:9000"
      command: php-fpm

   webserver:
      image: caddy:latest
      container_name: symfony_web
      ports:
      - "5180:80"
      volumes:
      - ./app/:/var/www/html
      - ./server/Caddyfile:/etc/caddy/Caddyfile
```

### Running Locally (Without Docker)  
1. Install dependencies:  
   ```bash
   composer install
   npm install
   npm run build
   ```  
2. Start the local server:  
   ```sh
   symfony server:start --port=5180
   ```  
3. Open `http://localhost:5180` in your browser.  

## 🖥 System Requirements  
- **PHP**: Version 8.0 or higher.  
- **Node.js**: Version 14 or higher.  
- **Composer**: Latest version.  
- **npm**: Latest version.  

## 🛠 Troubleshooting  
- **Issue**: Docker container fails to start.  
  **Solution**: Ensure Docker and Docker Compose are installed and running. Check for port conflicts.  
- **Issue**: `npm run build` fails.  
  **Solution**: Verify Node.js and npm versions meet the requirements.  

## 📌 Roadmap  
- HTML5 document validation  
- HEX,RGB converter  
- Improved UI/UX

## 🤝 Contribute
We welcome contributions from the developer community to make **loCode** even better. Whether it's adding new features, fixing bugs, or improving documentation, your help is appreciated. 

### How to Contribute
1. Fork the repository.
2. Create a new branch for your feature or bugfix.
3. Make your changes and commit them with clear commit messages.
4. Push your changes to your fork.
5. Create a pull request to the main repository.

Let's work together to build a powerful, privacy-focused toolset for developers. Thank you for your contributions!

**loCode** is built for developers who value privacy and efficiency. Keep your data in your hands—run it locally and work securely.

# License

This project is licensed under the MIT License - see the [LICENSE](https://opensource.org/licenses/MIT) file for details.