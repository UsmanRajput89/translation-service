# Project Setup

Follow these steps to get the project up and running:

1. **Clone the repository:**

   ```bash
    git clone https://github.com/UsmanRajput89/translation-service.git
    ```

2. **Navigate to the project directory:**
    ```bash
    cd translation-service
    ```
3. **Install dependencies:**
    ```bash
    composer install
    ```
4. **Set up your environment file:**
    ```bash
    cp .env.example .env
    ```

5. **Generate the application key:**
    ```bash
    php artisan key:generate
    ```

6. **Set up the database:**
    ```bash
    php artisan migrate
    ```
7. **Run My Custom Command to Populate Translations data:**
    ```bash
    php artisan app:seed-translations-command
    ```
8. **For Api Documentation:**
    ```bash
    php artisan l5-swagger:generate
    ```

9. **Run the application:**
    ```bash
    php artisan serve
    ```
    
You can view documentation at `/api/documentation` 

Happy Assessment!
