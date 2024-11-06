<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeProductDetails implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $url)
    {
        // 
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Step 1: Fetch HTML content
            $html = $this->fetchHtml($this->url);

            // Step 2: Extract relevant sections from HTML
            $extractedSections = $this->extractRelevantHtmlSections($html);

            // Step 3: Create a prompt for GPT-4 turbo
            $prompt = $this->createPrompt($extractedSections);

            // Step 4: Get the response from GPT-4 turbo
            $response = $this->getGPTResponse($prompt);

            // Step 5: Process the response
            $productData = $this->processResponse($response);

            // Optional: Store the data in the database
            // $this->storeProductData($productData);

        } catch (\Exception $e) {
            Log::error('Error scraping product details: ' . $e->getMessage());
        }
    }

    /**
     * Fetch HTML content from the URL.
     *
     * @param string $url
     * @return string
     */
    protected function fetchHtml(string $url): string
    {
        $response = Http::get($url);
        return $response->body(); // Return HTML content
    }

    /**
     * Extract relevant sections of HTML (name, price, description).
     *
     * @param string $html
     * @return array
     */
    protected function extractRelevantHtmlSections(string $html): array
    {
        $crawler = new Crawler($html);

        $productName = $crawler->filter('h1, .product-title, .name')->first()->text() ?? 'N/A';
        $price = $crawler->filter('.price, .amount, .product-price')->first()->text() ?? 'N/A';
        $description = $crawler->filter('.description, .product-description, p')->first()->text() ?? 'N/A';

        return [
            'name' => $productName,
            'price' => $price,
            'description' => $description
        ];
    }

    /**
     * Create a prompt for GPT-4 turbo model.
     *
     * @param array $extractedSections
     * @return string
     */
    protected function createPrompt(array $extractedSections): string
    {
        return "Extract the following details from this product HTML:

        - Product Name
        - Price
        - Description
        - Website Domain
        - Page URL

        HTML Content:
        Product Name: {$extractedSections['name']}
        Price: {$extractedSections['price']}
        Description: {$extractedSections['description']}";
    }

    /**
     * Get the response from GPT-4 turbo.
     *
     * @param string $prompt
     * @return string
     */
    protected function getGPTResponse(string $prompt): string
    {
        $apiKey = env('OPENAI_API_KEY');
        $response = Http::withHeaders([
            'Authorization' => "Bearer $apiKey",
        ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-2024-08-06',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an assistant for extracting product information from HTML.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 200,
                ]);

        return json_decode($response->body(), true)['choices'][0]['message']['content'];
    }

    /**
     * Process GPT's response and extract relevant product data.
     *
     * @param string $response
     * @return array
     */
    protected function processResponse(string $response): array
    {
        $lines = explode("\n", $response);
        $data = [];

        foreach ($lines as $line) {
            if (strpos($line, 'Product Name:') !== false) {
                $data['product_name'] = trim(str_replace('Product Name:', '', $line));
            } elseif (strpos($line, 'Price:') !== false) {
                $data['price'] = trim(str_replace('Price:', '', $line));
            } elseif (strpos($line, 'Description:') !== false) {
                $data['description'] = trim(str_replace('Description:', '', $line));
            }
        }

        return $data;
    }

    /**
     * Optionally store the product data in a database.
     *
     * @param array $productData
     */
    // protected function storeProductData(array $productData)
    // {
    //     // Example: Storing data in the Product model
    //     Post::create([
    //         'name' => $productData['product_name'],
    //         'price' => $productData['price'],
    //         'description' => $productData['description'],
    //     ]);
    // }
}
