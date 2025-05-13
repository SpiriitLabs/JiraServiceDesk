# 📦 FrankenPHP Standalone Binary Deployment Guide

This guide explains how to deploy the __FrankenPHP standalone binary__ in a production environment, running on __Ubuntu 24__ (or Debian), with __Supervisor__ for process management and MariaDB as the __external database__.

## Prerequisites:

- __Ubuntu 24__ or Debian Server (recommended).
- __FrankenPHP__ Standalone Binary.
- __Supervisor__ for process management.
- __MariaDB__ as the external database (or any compatible database).
- Basic knowledge of __environment variables__ and __bash scripting__.

## 📂 File Structure

Here’s the __file structure__ for your deployment:

```
/your/application/folder/
│
├── app-template                 # FrankenPHP binary executable
├── .env                # Environment variables file
├── start.sh            # Script to export env variables and start the application
├── download-release.sh # Script to download the latest release
└── supervisor.conf     # Supervisor configuration file (not part of the actual app)
```

## 🛠 Installation Steps
### 1. Install Dependencies

Ensure __Supervisor__ is installed:

```sh
sudo apt-get update
sudo apt-get install supervisor
```

Also, install __wget__ and __curl__ for the download script:

```sh
sudo apt-get install wget curl
```

### 2. Set Up Application Folder

Create a directory for your application and upload your FrankenPHP binary there:

```sh
mkdir -p /your/application/folder
cd /your/application/folder
```

### 3. Configure .env File

Create your .env file in the application folder, which should contain the necessary environment variables for the application to function:

```txt
# Example .env file:
APP_ENV=prod
APP_SECRET=your-secret-key
DATABASE_URL=mysql://username:password@hostname:port/dbname
MAILER_DSN=smtp://mail.example.com
FROM_EMAIL=noreply@example.com
JIRAAPI_V3_USER=your-jira-user
JIRAAPI_V3_PERSONAL_ACCESS_TOKEN=your-access-token
JIRAAPI_V3_HOST=https://jira.example.com
JIRA_ACCOUNT_ID=your-jira-account-id
```

Make sure secrets like passwords or access tokens are not stored in public repositories.

## 📝 Configuration Files

### 4. Supervisor Configuration

Create a `supervisor.conf` file to manage the application process. This file will ensure your application and its consumer process are correctly run in the background.

```conf
[program:app]
command=/your/application/folder/start.sh /your/application/folder/.env /your/application/folder/app php-server
numprocs=1
autostart=true
autorestart=true
process_name=%(program_name)s_%(process_num)02d
redirect_stderr=false
stdout_logfile=/your/application/folder/stdout.log
stderr_logfile=/your/application/folder/stderr.log

[program:app-consumer]
command=/your/application/folder/start.sh /your/application/folder/.env /your/application/folder/app php-cli bin/console messenger:consume webhook async
numprocs=1
startsecs=0
autostart=true
autorestart=true
process_name=%(program_name)s_%(process_num)02d
```

### 5. Start Script (start.sh)

Create the start.sh script to load environment variables and run the application:

```sh
#!/bin/bash

ENV_FILE="$1"
shift

# Load environment variables from the .env file
set -o allexport
source $ENV_FILE
set +o allexport

# Execute the command passed to the script (php-server or other commands)
exec "$@"
```

Make sure the `start.sh` script is executable:

```sh
chmod +x start.sh
```

### 6. Download Latest Release Script (download-release.sh)

Create a script to automatically download the latest release of your FrankenPHP application.

```sh
#!/bin/bash

# === Configuration ===
GITHUB_USER="RomainMILLAN"
REPO_NAME="JiraServiceDesk"
CURRENT_APP_PATH="/your/application/folder/app"
ASSET_NAME="app-template"

# === Get latest release metadata ===
API_URL="https://api.github.com/repos/${GITHUB_USER}/${REPO_NAME}/releases/latest"
echo "Fetching latest release info from $API_URL ..."
RELEASE_JSON=$(curl -s "$API_URL")

# === Use awk to find the correct download URL ===
DOWNLOAD_URL=$(echo "$RELEASE_JSON" | awk -v name="$ASSET_NAME" '
  $0 ~ "\"name\": \""name"\"" { found = 1 }
  found && $0 ~ "\"browser_download_url\":" {
    gsub(/[",]/, "", $2)
    print $2
    exit
  }')

if [[ -z "$DOWNLOAD_URL" ]]; then
  echo "Error: '${ASSET_NAME}' file not found in the latest release."
  exit 1
fi

echo "Download URL: $DOWNLOAD_URL"

# === Download the file using wget ===
TEMP_FILE="${CURRENT_APP_PATH}.tmp"
echo "Downloading with wget to: $TEMP_FILE"
wget -q --show-progress -O "$TEMP_FILE" "$DOWNLOAD_URL"

# === Replace the existing file ===
if [[ $? -eq 0 ]]; then
  echo "Replacing existing file: $CURRENT_APP_PATH"
  mv "$TEMP_FILE" "$CURRENT_APP_PATH"
  chmod +x "$CURRENT_APP_PATH"
  echo "✅ Update complete."
else
  echo "❌ Download failed."
  rm -f "$TEMP_FILE"
  exit 1
fi
```

Make sure the `download-release.sh` script is executable:

```sh
chmod +x download-release.sh
```

### 7. Configure Supervisor to Manage Processes

Add your __Supervisor configuration__ by creating a new config file for your program:

```sh
sudo nano /etc/supervisor/conf.d/your-application.conf
```

Then, add the configuration you created earlier.

Once you’ve done that, update __Supervisor__:

```sh
sudo supervisorctl reread
sudo supervisorctl update
```

You can now start the services:

```sh
sudo supervisorctl start app:*
```

## 🔄 Deployment and Updating

When a new release is available, simply run the `download-release.sh` script to update the binary:

```sh
./download-release.sh
```

This will automatically download the latest release and replace the old executable.

To restart the application with the new binary:

```sh
sudo supervisorctl restart app:*
```

## 🧑‍💻 Running the Application

The web application and consumer can be managed using __Supervisor__ commands:

- To __start__ the application:

```sh
sudo supervisorctl start app:*
```

- To __stop__ the application:

```sh
sudo supervisorctl stop app:*
```

- To __restart__ the application:

```sh
sudo supervisorctl restart app:*
```

## 🔐 Security Considerations

Ensure that the `.env` file is not committed to any version control system.

Limit access to the `.env` file to authorized users only.

Consider running the application behind a __reverse proxy__ (e.g., Nginx) for additional security, including __SSL/TLS encryption__.

## 📅 Backup and Failover Strategy

Consider implementing a __backup strategy__ for both your application and database:

- __Database Backup__: Automate MariaDB backups with mysqldump or similar tools.
- __Application Backups__: Store copies of the application binary and .env file securely.

For failover, you can set up a secondary database or application server that can take over in case of a failure.

## ⚠️ Note on PHP-CLI and Options

If you're using the __php-cli__ with __FrankenPHP__, please note that passing options with -- does not currently work as expected. This issue has been reported, and you can track its progress in the FrankenPHP GitHub issue.
