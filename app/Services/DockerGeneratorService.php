<?php

namespace App\Services;

use ZipArchive;
use Illuminate\Support\Facades\File;

class DockerGeneratorService
{
    public function generate(array $config)
    {
        $projectName = strtolower($config['project_name']);
        $projectPath = storage_path("app/temp/{$projectName}");
        
        try {
          
            $this->createDirectoryStructure($projectPath);
            $this->generateDockerCompose($projectPath, $config);
            $this->generatePhpDockerfile($projectPath, $config);
            $this->generateNginxDockerfile($projectPath, $config);
            $this->installLaravel($projectPath);
            $this->generateAppKey($projectPath);
            $this->generateEnvFile($projectPath, $config);
            $this->setPermissions($projectPath);
            $zipPath = storage_path("app/temp/{$projectName}.zip");
            $this->createZip($projectPath, $zipPath);
            File::deleteDirectory($projectPath);
            return $zipPath;

        } catch (\Exception $e) {

            if (is_dir($projectPath)) {
                File::deleteDirectory($projectPath);
            }
            throw $e;

        }
    }
// Create ZIP file from generated project
    protected function createZip($source, $destination)
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('ZIP extension not loaded');
        }

        $zip = new ZipArchive();
        
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip file");
        }

        $source = str_replace('\\', '/', realpath($source));

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }
// generate app key using artisan command, fallback to manual generation if it fails
    protected function generateAppKey($path)
    {
        $htmlPath = $path . '/html';
        
        // Check if artisan exists
        if (!file_exists($htmlPath . '/artisan')) {
            throw new \Exception('Artisan file not found');
        }

        // Method 1: Use PHP to run artisan key:generate
        $command = sprintf(
            'cd "%s" && php artisan key:generate --force --no-interaction 2>&1',
            $htmlPath
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            // If artisan fails, generate manually
            return $this->generateAppKeyManually($path);
        }

        return true;
    }
// generating app key manually if artisan command fails (fallback)
    protected function generateAppKeyManually($path)
    {
        $htmlPath = $path . '/html';
        $envPath = $htmlPath . '/.env';

        if (!file_exists($envPath)) {
            throw new \Exception('.env file not found');
        }

        // Generate key using Laravel's method
        $key = 'base64:' . base64_encode(random_bytes(32));

        // Read and update .env
        $envContent = file_get_contents($envPath);
        $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
        
        file_put_contents($envPath, $envContent);

        // Update .env.example
        $envExamplePath = $htmlPath . '/.env.example';
        if (file_exists($envExamplePath)) {
            $exampleContent = file_get_contents($envExamplePath);
            $exampleContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $exampleContent);
            file_put_contents($envExamplePath, $exampleContent);
        }

        return $key;
    }
// creating folders for docker project
    protected function createDirectoryStructure($path)
    {
        $directories = [
            '',
            'celiums-laravel-php',
            'celiums-laravel-nginx',
            'html',
            'db_data',
        ];

        foreach ($directories as $dir) {
            $fullPath = $path . ($dir ? '/' . $dir : '');
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0777, true); // Use 0777 for full permissions
            }
            // Ensure the directory is writable
            @chmod($fullPath, 0777);
        }
    }
// generating yml file for docker-compose
   protected function generateDockerCompose($path, $config)
    {
        $projectName = strtolower($config['project_name']);
        $domainName = $config['domain_name'];
        $appPort = $config['app_port'];
        $phpmyadminPort = $config['phpmyadmin_port'];
        $dbUsername = $config['db_username'];
        $dbPassword = $config['db_password'];
        $dbRootPassword = $config['db_root_password'];
        $mysqlVersion = $config['mysql_version'];
        
        $redis = $config['include_redis'] ?? true;
        $mailhog = $config['include_mailhog'] ?? true;

        // Start with services (no version needed in Docker Compose v2)
        $content = "services:\n";
        
        // Backend Service
        $content .= "  {$projectName}backend:\n";
        $content .= "    container_name: {$projectName}backend\n";
        $content .= "    build:\n";
        $content .= "      context: ./\n";
        $content .= "      dockerfile: ./celiums-laravel-php/Dockerfile\n";
        $content .= "    restart: unless-stopped\n";
        $content .= "    environment:\n";
        $content .= "      PHP_SITE_NAME: " . ucfirst($projectName) . "\n";
        $content .= "      PHP_HOST_NAME: {$domainName}\n";
        $content .= "    volumes:\n";
        $content .= "      - ./html:/var/www/html\n";
        $content .= "    networks:\n";
        $content .= "      - {$projectName}_network\n";
        $content .= "\n";

        // Nginx Service
        $content .= "  {$projectName}nginx:\n";
        $content .= "    container_name: {$projectName}nginx\n";
        $content .= "    build:\n";
        $content .= "      context: ./\n";
        $content .= "      dockerfile: ./celiums-laravel-nginx/Dockerfile\n";
        $content .= "    restart: unless-stopped\n";
        $content .= "    environment:\n";
        $content .= "      NGINX_SERVER_ROOT: /var/www/html/public\n";
        $content .= "      NGINX_SERVER_NAME: {$domainName}\n";
        $content .= "      NGINX_BACKEND_HOST: {$projectName}backend\n";
        $content .= "      NGINX_UPSTREAM_NAME: {$projectName}backend\n";
        $content .= "      NGINX_VHOST_PRESET: laravel\n";
        $content .= "    volumes:\n";
        $content .= "      - ./html:/var/www/html\n";
        $content .= "    ports:\n";
        $content .= "      - \"{$appPort}:80\"\n";
        $content .= "    depends_on:\n";
        $content .= "      - {$projectName}backend\n";
        $content .= "    networks:\n";
        $content .= "      - {$projectName}_network\n";
        $content .= "\n";

        // MySQL Database Service
        $content .= "  {$projectName}db:\n";
        $content .= "    image: mysql:{$mysqlVersion}\n";
        $content .= "    container_name: {$projectName}db\n";
        $content .= "    restart: unless-stopped\n";
        $content .= "    volumes:\n";
        $content .= "      - ./db_data:/var/lib/mysql\n";
        $content .= "    environment:\n";
        $content .= "      MYSQL_ROOT_PASSWORD: {$dbRootPassword}\n";
        $content .= "      MYSQL_DATABASE: {$projectName}_db\n";
        $content .= "      MYSQL_USER: {$dbUsername}\n";
        $content .= "      MYSQL_PASSWORD: {$dbPassword}\n";
        $content .= "    healthcheck:\n";
        $content .= "      test: [\"CMD\", \"mysqladmin\", \"ping\", \"-h\", \"localhost\"]\n";
        $content .= "      interval: 10s\n";
        $content .= "      timeout: 5s\n";
        $content .= "      retries: 5\n";
        $content .= "    networks:\n";
        $content .= "      - {$projectName}_network\n";
        $content .= "\n";

        // PHPMyAdmin Service
        $content .= "  {$projectName}phpmyadmin:\n";
        $content .= "    image: phpmyadmin/phpmyadmin:latest\n";
        $content .= "    container_name: {$projectName}phpmyadmin\n";
        $content .= "    restart: unless-stopped\n";
        $content .= "    ports:\n";
        $content .= "      - \"{$phpmyadminPort}:80\"\n";
        $content .= "    environment:\n";
        $content .= "      PMA_HOST: {$projectName}db\n";
        $content .= "      PMA_USER: {$dbUsername}\n";
        $content .= "      PMA_PASSWORD: {$dbPassword}\n";
        $content .= "      UPLOAD_LIMIT: 256M\n";
        $content .= "    depends_on:\n";
        $content .= "      - {$projectName}db\n";
        $content .= "    networks:\n";
        $content .= "      - {$projectName}_network\n";
        $content .= "\n";

        // Redis Service
        if ($redis) {
            $content .= "  {$projectName}redis:\n";
            $content .= "    image: redis:alpine\n";
            $content .= "    container_name: {$projectName}redis\n";
            $content .= "    restart: unless-stopped\n";
            $content .= "    ports:\n";
            $content .= "      - \"6379:6379\"\n";
            $content .= "    volumes:\n";
            $content .= "      - ./redis_data:/data\n";
            $content .= "    command: redis-server --appendonly yes\n";
            $content .= "    healthcheck:\n";
            $content .= "      test: [\"CMD\", \"redis-cli\", \"ping\"]\n";
            $content .= "      interval: 10s\n";
            $content .= "      timeout: 3s\n";
            $content .= "      retries: 3\n";
            $content .= "    networks:\n";
            $content .= "      - {$projectName}_network\n";
            $content .= "\n";
        }

        // MailHog Service
        if ($mailhog) {
            $content .= "  {$projectName}mailhog:\n";
            $content .= "    image: mailhog/mailhog:latest\n";
            $content .= "    container_name: {$projectName}mailhog\n";
            $content .= "    restart: unless-stopped\n";
            $content .= "    ports:\n";
            $content .= "      - \"1025:1025\"\n";
            $content .= "      - \"8025:8025\"\n";
            $content .= "    networks:\n";
            $content .= "      - {$projectName}_network\n";
            $content .= "\n";
        }

        // Networks
        $content .= "networks:\n";
        $content .= "  {$projectName}_network:\n";
        $content .= "    driver: bridge\n";

        // Write to file
        file_put_contents($path . '/docker-compose.yml', $content);
    }

// Generate Dockerfile for PHP container
    protected function generatePhpDockerfile($path, $config)
    {
          $phpVersion = $config['php_version'];
        
          $content = "FROM wodby/laravel-php:{$phpVersion}

          COPY ./html /var/www/html

          WORKDIR /var/www/html

          # Set permissions
          RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
          RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true
          ";

                  file_put_contents($path . '/celiums-laravel-php/Dockerfile', $content);
              }

              protected function generateNginxDockerfile($path, $config)
              {
                  $content = "FROM wodby/nginx:1.27

          COPY ./html /var/www/html

          WORKDIR /var/www/html
          ";

        file_put_contents($path . '/celiums-laravel-nginx/Dockerfile', $content);
    }
// env file generating 
    protected function generateEnvFile($path, $config)
    {
        $projectName = strtolower($config['project_name']);
        $appPort = $config['app_port'];
        $dbUsername = $config['db_username'];
        $dbPassword = $config['db_password'];
        
        $envPath = $path . '/html/.env';
        
        if (!file_exists($envPath)) {
            throw new \Exception('.env file not found. Laravel installation may have failed.');
        }

        $envContent = file_get_contents($envPath);

        $lines = explode("\n", $envContent);
        $updatedLines = [];

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            if (strpos($trimmedLine, 'APP_NAME=') === 0) {
                $updatedLines[] = 'APP_NAME="' . ucfirst($projectName) . '"';
            }
            elseif (strpos($trimmedLine, 'APP_URL=') === 0) {
                $updatedLines[] = 'APP_URL=http://localhost:' . $appPort;
            }
            elseif (strpos($trimmedLine, 'DB_CONNECTION=') === 0 || strpos($trimmedLine, '# DB_CONNECTION=') === 0) {
                $updatedLines[] = 'DB_CONNECTION=mysql';
            }
            elseif (strpos($trimmedLine, 'DB_HOST=') === 0 || strpos($trimmedLine, '# DB_HOST=') === 0) {
                $updatedLines[] = 'DB_HOST=' . $projectName . 'db';
            }
            elseif (strpos($trimmedLine, 'DB_PORT=') === 0 || strpos($trimmedLine, '# DB_PORT=') === 0) {
                $updatedLines[] = 'DB_PORT=3306';
            }
            elseif (strpos($trimmedLine, 'DB_DATABASE=') === 0 || strpos($trimmedLine, '# DB_DATABASE=') === 0) {
                $updatedLines[] = 'DB_DATABASE=' . $projectName . '_db';
            }
            elseif (strpos($trimmedLine, 'DB_USERNAME=') === 0 || strpos($trimmedLine, '# DB_USERNAME=') === 0) {
                $updatedLines[] = 'DB_USERNAME=' . $dbUsername;
            }
            elseif (strpos($trimmedLine, 'DB_PASSWORD=') === 0 || strpos($trimmedLine, '# DB_PASSWORD=') === 0) {
                $updatedLines[] = 'DB_PASSWORD=' . $dbPassword;
            }
            else {
                $updatedLines[] = $line;
            }
        }

        $envContent = implode("\n", $updatedLines);

        if ($config['include_redis'] ?? false) {
            if (strpos($envContent, 'REDIS_HOST=') === false) {
                $envContent .= "\n\n# Redis Configuration\n";
                $envContent .= "REDIS_CLIENT=predis\n";
                $envContent .= "REDIS_HOST={$projectName}redis\n";
                $envContent .= "REDIS_PASSWORD=null\n";
                $envContent .= "REDIS_PORT=6379\n";
            }
        }

        if ($config['include_mailhog'] ?? false) {
            $mailReplacements = [
                '/^MAIL_MAILER=.*/m' => 'MAIL_MAILER=smtp',
                '/^MAIL_HOST=.*/m' => 'MAIL_HOST=' . $projectName . 'mailhog',
                '/^MAIL_PORT=.*/m' => 'MAIL_PORT=1025',
                '/^MAIL_USERNAME=.*/m' => 'MAIL_USERNAME=null',
                '/^MAIL_PASSWORD=.*/m' => 'MAIL_PASSWORD=null',
                '/^MAIL_ENCRYPTION=.*/m' => 'MAIL_ENCRYPTION=null',
            ];

            foreach ($mailReplacements as $pattern => $replacement) {
                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    $key = explode('=', $replacement)[0];
                    $envContent .= "\n" . $replacement;
                }
            }

            if (strpos($envContent, 'MAIL_FROM_ADDRESS=') === false) {
                $envContent .= "\nMAIL_FROM_ADDRESS=hello@{$projectName}.com";
            }
            if (strpos($envContent, 'MAIL_FROM_NAME=') === false) {
                $envContent .= "\nMAIL_FROM_NAME=\"\${APP_NAME}\"";
            }
        }

        file_put_contents($envPath, $envContent);

        // Also update .env.example
        file_put_contents($path . '/html/.env.example', $envContent);

        return true;
    }
// Helper function to add or update a section in .env file, used for Redis and Mailhog configuration
    protected function addOrUpdateSection($content, $sectionName, $newSection)
    {
        if (strpos($content, "# $sectionName") !== false) {
            return $content;
        }

        return $content . $newSection;
    }

// Install Laravel using composer, with error handling and fallback
  protected function installLaravel($path)
  {
      $htmlPath = $path . '/html';
      
      @chmod($htmlPath, 0777);
      
      if (!is_writable($htmlPath)) {
          throw new \Exception('HTML directory is not writable. Check permissions.');
      }

      $composerPath = $this->findComposer();
      
      if (!$composerPath) {
          throw new \Exception('Composer is not installed or not found in PATH.');
      }

      $command = sprintf(
          'cd "%s" && %s create-project --prefer-dist --no-interaction laravel/laravel . 2>&1',
          $htmlPath,
          $composerPath
      );

      exec($command, $output, $returnCode);

      if ($returnCode !== 0) {
          throw new \Exception('Failed to install Laravel: ' . implode("\n", $output));
      }

      if (!file_exists($htmlPath . '/artisan')) {
          throw new \Exception('Laravel installation failed - artisan file not found');
      }

      $this->setStoragePermissionsImmediate($htmlPath);

      return true;
  }
// Set permissions for storage 
  protected function setStoragePermissionsImmediate($htmlPath)
  {
      $dirs = [
          $htmlPath . '/storage',
          $htmlPath . '/storage/app',
          $htmlPath . '/storage/app/public',
          $htmlPath . '/storage/framework',
          $htmlPath . '/storage/framework/cache',
          $htmlPath . '/storage/framework/cache/data',
          $htmlPath . '/storage/framework/sessions',
          $htmlPath . '/storage/framework/testing',
          $htmlPath . '/storage/framework/views',
          $htmlPath . '/storage/logs',
          $htmlPath . '/bootstrap',
          $htmlPath . '/bootstrap/cache',
      ];

      foreach ($dirs as $dir) {
          if (!is_dir($dir)) {
              @mkdir($dir, 0777, true);
          }
          
          @chmod($dir, 0777);
          
            if (is_dir($dir)) {
                $files = glob($dir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @chmod($file, 0666);
                    } elseif (is_dir($file)) {
                        @chmod($file, 0777);
                    }
                }
            }
      }

      $this->chmodRecursiveForce($htmlPath . '/storage', 0777, 0666);
      $this->chmodRecursiveForce($htmlPath . '/bootstrap/cache', 0777, 0666);
  }
// forcing for permission
  protected function chmodRecursiveForce($path, $dirMode = 0777, $fileMode = 0666)
  {
      if (!file_exists($path)) {
          return;
      }

      @chmod($path, $dirMode);

      if (!is_dir($path)) {
          return;
      }

      try {
          $iterator = new \RecursiveIteratorIterator(
              new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
              \RecursiveIteratorIterator::SELF_FIRST
          );

          foreach ($iterator as $item) {
              if ($item->isDir()) {
                  @chmod($item->getPathname(), $dirMode);
              } else {
                  @chmod($item->getPathname(), $fileMode);
              }
          }
      } catch (\Exception $e) {
          if (PHP_OS_FAMILY !== 'Windows') {
              @exec(sprintf('chmod -R 777 %s 2>/dev/null', escapeshellarg($path)));
          }
      }
  }
// find composer in common locations or using which command
  protected function findComposer()
  {
      $composerPaths = [
          'composer',                          
          '/usr/local/bin/composer',
          '/usr/bin/composer', 
          $_SERVER['HOME'] . '/.composer/composer', 
          'C:\\ProgramData\\ComposerSetup\\bin\\composer.bat',
      ];

      foreach ($composerPaths as $path) {
          if (@is_executable($path) || shell_exec("which $path 2>/dev/null")) {
              return $path;
          }
      }

      $which = shell_exec('which composer 2>/dev/null');
      if (!empty($which)) {
          return trim($which);
      }

      return false;
  }
// set permission
  protected function setPermissions($path)
  {
      $htmlPath = $path . '/html';

      if (!is_dir($htmlPath)) {
          throw new \Exception('HTML directory not found');
      }

      $writableDirs = [
          $htmlPath . '/storage',
          $htmlPath . '/bootstrap/cache',
      ];

      foreach ($writableDirs as $dir) {
          if (is_dir($dir)) {
              $this->chmodRecursiveForce($dir, 0777, 0666);
          }
      }

      $this->setDefaultPermissions($htmlPath);

      return true;
  }

  protected function chmodRecursive($path, $dirMode = 0775, $fileMode = 0664)
  {
        if (!is_dir($path)) {
            return @chmod($path, $fileMode);
        }

        $dir = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @chmod($item->getPathname(), $dirMode);
            } else {
                @chmod($item->getPathname(), $fileMode);
            }
        }

        @chmod($path, $dirMode);
  }

    protected function setDefaultPermissions($path)
  {
      $iterator = new \RecursiveIteratorIterator(
          new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
          \RecursiveIteratorIterator::SELF_FIRST
      );

      $storageBootstrapPaths = [
          realpath($path . '/storage'),
          realpath($path . '/bootstrap/cache'),
      ];

      foreach ($iterator as $item) {
          $itemPath = $item->getPathname();
          $realPath = realpath($itemPath);

          // Skip storage and bootstrap/cache directories (already handled)
          $isSpecialDir = false;
          foreach ($storageBootstrapPaths as $specialPath) {
              if ($specialPath && strpos($realPath, $specialPath) === 0) {
                  $isSpecialDir = true;
                  break;
              }
          }

          if ($isSpecialDir) {
              continue;
          }

          // Set normal permissions for other files/dirs
          if ($item->isDir()) {
              @chmod($itemPath, 0755);
          } else {
              @chmod($itemPath, 0644);
          }
      }
  }

}