# loCode – Local Developer Toolset

**loCode** is a self-hosted web application designed for developers who need a quick and reliable set of tools for everyday tasks—without relying on online services that could compromise data security. Built with **PHP Symfony** on the backend and **AlpineJS** on the frontend, loCode provides a fast, privacy-focused experience that runs entirely on your local machine or private server.

![Locode Screenshot](/docs/screenshot01.jpg)

## 🛠 Features  
- **Notes** – Local scratchpad and file manager for quick snippets and references.
- **JSON Formatter** – Pretty-print, validate, and clean up JSON data.
- **Hash Functions** – Generate MD5, SHA-1, SHA-256, SHA-512, and other hashes.
- **Base64 Utilities** – Encode and decode Base64 strings.
- **URL Parser** – Break down URLs into protocol, host, path, query, and fragments.
- **Token Generator** – Generate random passwords, API keys, and secure tokens.
- **JWT Decoder** – Decode JWT headers and payloads locally.
- **Crontab Generator** – Build cron expressions and command entries.
- **cURL Generator** – Generate cURL commands with methods, headers, auth, cookies, redirects, output files, and TLS options.
- **HTTP Tools** – Inspect HAR files and encode/decode HTTP cookies.
- **SSH Tools** – Generate SSH key pairs, rsync commands, SSH tunnels, port forwards, SOCKS proxies, and route helper commands.
- **HTML Tools** – Validate HTML, beautify/minify markup, and extract color values from HTML/CSS.
- **PHP Tools** – Encode/decode serialized PHP data and convert between PHP arrays and JSON.
- **Apache Tools** – Generate redirects, VirtualHost configuration, and Basic Auth `.htaccess`/`.htpasswd` snippets.
- **And More…** – Expanding toolset for developers’ daily needs.  

## 🔒 Why Self-Host?  
Online developer tools may store your pasted data, intentionally or unintentionally. Sensitive information like API keys, credentials, or proprietary code snippets could be logged or analyzed. **loCode** eliminates this risk by keeping everything local—no tracking, no analytics, no data leaks.  

## 🚀 Quick Start  

### Running with Docker
You can quickly deploy **loCode** using Docker.

1. Run the container:
   ```sh
   docker run -d --name locode \
     -p 5180:80 \
     -v locode-notes:/app/notes \
     ghcr.io/luzel/locode:latest
   ```
2. Open your browser and access the app at:
   ```
   http://localhost:5180
   ```  

> **Note:** `docker run` automatically pulls the image if it is not available locally. If the image was just published and Docker returns `denied`, wait a minute and retry. You can also check the registry separately with `docker pull ghcr.io/luzel/locode:latest`. If it still fails, clear stale GHCR credentials with `docker logout ghcr.io`.

The `locode-notes` volume stores Markdown files created in **Notes** outside the container. Keep this volume mounted when recreating or upgrading the container so your notes persist.

### Updating the Docker image

Pull the latest image, remove the existing container, and recreate it with the same volume:

```sh
docker pull ghcr.io/luzel/locode:latest
docker rm -f locode
docker run -d --name locode \
  -p 5180:80 \
  -v locode-notes:/app/notes \
  ghcr.io/luzel/locode:latest
```

The `locode-notes` volume is not removed when the container is deleted, so existing notes remain available after the upgrade.
If the original container used `APP_SECRET` or other environment variables, include the same `-e` options when recreating it.

To remove old, untagged image layers after verifying the updated container:

```sh
docker image prune -f
```

To keep a stable Symfony secret between restarts, pass `APP_SECRET`:

```sh
docker run -d --name locode \
  -p 5180:80 \
  -e APP_SECRET="$(openssl rand -hex 16)" \
  -v locode-notes:/app/notes \
  ghcr.io/luzel/locode:latest
```

### Running Locally (Without Docker)  
1. Install dependencies:  
   ```bash
   composer install
   npm install
   npm run build
   ```  
2. Start the local server:  
   ```bash
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
- ~~HTML5 document validation~~
- ~~HEX/RGB converter~~
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
