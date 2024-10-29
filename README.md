# Image Background Remover

This laravel application easily remove the background from your images using the [Hugging face ml model](https://huggingface.co/briaai/RMBG-1.4) "briaai/RMBG-1.4". This project is built using Laravel 11, [transformers-php](https://github.com/CodeWithKyrian/transformers-php), webcamJs and the Laravel PWA package by silviolleite for PWA support.

## Getting Started

These instructions will help you set up and run the application on your local machine.

### Prerequisites

-   [PHP 8.3+](https://www.php.net/)
-   [Composer](https://getcomposer.org/download/)
-   [Node.js and npm](https://nodejs.org/en)

### Installation

1.  Clone the Repository
    ```bash
    git clone https://github.com/NishakMohomed/image-background-remover.git
    cd image-background-remover
    ```
2.  Install Composer Packages
    ```bash
    composer install
    ```
3.  Install Npm Packages And Compile
    ```bash
    npm install && npm run build
    ```
4.  Configure Environment

    Copy the .env.example file to .env:

    ```bash
    cp .env.example .env
    ```

    This application doesn't need any databse connection, I am using php cache to store image file path. If you wish to make any database queries later open the .env file and set your application and database configurations.

5.  Generate Application Key
    ```bash
    php artisan key:generate
    ```
6.  Download briaai/RMBG-1.4 Model Into Cache Directory, By Default "your-project-root/.transformers-cache"
    ```bash
    ./vendor/bin/transformers download briaai/RMBG-1.4
    ```
7.  Link The Storage
    ```bash
    php artisan storage:link
    ```
8.  Start the Queue
    ```bash
    php artisan queue:work
    ```
9.  Start Reverb
    ```bash
    php artisan reverb:start
    ```
10. Run The Application
    ```bash
    composer run dev
    ```
11. Access the Application
    Visit http://localhost:8000 to access the app.

## Usage

Upload an image or take picture using webcam, camera, and the app will remove the background using the Hugging Face "briaai/RMBG-1.4" model.

## Resources and References

-   [Transformers PHP Documentation](https://github.com/CodeWithKyrian/transformers-php)
-   [silviolleite/laravelpwa package for pwa support](https://github.com/silviolleite/laravel-pwa)

## License

This project is open-source and available under the [MIT license](https://opensource.org/licenses/MIT).
