<?php

namespace App\Jobs;

use App\Models\WebsiteDetails;
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
            $extractedSections = $this->extractRelevantHtmlSections($html, $this->url);

            // Step 3: Create a prompt for GPT-4 turbo
            $prompt = $this->createPrompt($extractedSections);

            // Step 4: Get the response from GPT-4 turbo
            $response = $this->getGPTResponse($prompt);

            // Step 5: Process the response
            $productData = $this->processResponse($response);

            // Step 6: Store the data in the database
            $this->storeProductData($productData);

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
    protected function extractRelevantHtmlSections(string $html, string $url): array
    {
        $crawler = new Crawler($html);

        // Extract Product Name
        $productName = $crawler->filter('.product-title, .name, h1')->first()->text() ?? 'N/A';

        // Extract Product Subtitle (if available)
        $productSubtitle = $crawler->filter('.product-subtitle, .subtitle, h2')->first()->text() ?? 'N/A';

        // Extract Price
        $price = $crawler->filter('.product-price, .price, .amount')->first()->text() ?? 'N/A';

        // Extract Description
        $description = $crawler->filter('.product-description, .description, p')->first()->text() ?? 'N/A';

        // Extract Website Domain
        $parsedUrl = parse_url($url);
        $websiteDomain = $parsedUrl['host'] ?? 'N/A';

        // Extract Page URL
        $pageUrl = $url; // The page URL is the input URL itself

        // Extract Website Logo URL (look for common logo classes or fallback to favicon)
        $logoUrl = $crawler->filter('img.logo, .site-logo img')->first()->attr('src') ?? 'N/A';
        if ($logoUrl !== 'N/A' && !preg_match('/^http(s)?:\/\//', $logoUrl)) {
            $logoUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/' . ltrim($logoUrl, '/');
        } else {
            $logoUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/favicon.ico';
        }

        // Extract Company Name (if available, often in header or footer)
        $companyName = $crawler->filter('.company-name, .brand, .site-name, .header-logo img[alt]')->first()->attr('alt') ?? 'N/A';

        return [
            'name' => $productName,
            'subTitle' => $productSubtitle,
            'price' => $price,
            'description' => $description,
            'domain' => $websiteDomain,
            'url' => $pageUrl,
            'logoUrl' => $logoUrl,
            'companyName' => $companyName
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
    - Product Subtitle
    - Price
    - Description
    - Website Domain
    - Page URL
    - Website Logo URL
    - Company Name

    HTML Content:
    Product Name: {$extractedSections['name']}
    Product Subtitle: {$extractedSections['subTitle']}
    Price: {$extractedSections['price']}
    Description: {$extractedSections['description']}
    Website Domain: {$extractedSections['domain']}
    Page URL: {$extractedSections['url']}
    Website Logo URL: {$extractedSections['logoUrl']}
    Company Name: {$extractedSections['companyName']}";
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
                $data['name'] = trim(str_replace('Product Name:', '', $line));
            } elseif (strpos($line, 'Product Subtitle:') !== false) {
                $data['subTitle'] = trim(str_replace('Product Subtitle:', '', $line));
            } elseif (strpos($line, 'Price:') !== false) {
                $data['price'] = trim(str_replace('Price:', '', $line));
            } elseif (strpos($line, 'Description:') !== false) {
                $data['description'] = trim(str_replace('Description:', '', $line));
            } elseif (strpos($line, 'Website Domain:') !== false) {
                $data['domain'] = trim(str_replace('Website Domain:', '', $line));
            } elseif (strpos($line, 'Page URL:') !== false) {
                $data['url'] = trim(str_replace('Page URL:', '', $line));
            } elseif (strpos($line, 'Website Logo URL:') !== false) {
                $data['logoUrl'] = trim(str_replace('Website Logo URL:', '', $line));
            } elseif (strpos($line, 'Company Name:') !== false) {
                $data['companyName'] = trim(str_replace('Company Name:', '', $line));
            }
        }

        return $data;
    }

    /**
     * Optionally store the product data in a database.
     *
     * @param array $productData
     */
    protected function storeProductData(array $productData)
    {
        // Example: Storing data in the Product model
        WebsiteDetails::create([
            'name' => $productData['name'],
            'subTitle' => $productData['subTitle'],
            'price' => $productData['price'],
            'description' => $productData['description'],
            'domain' => $productData['domain'],
            'url' => $productData['url'],
            'logoUrl' => $productData['logoUrl'],
            'companyName' => $productData['companyName'],
        ]);
    }
}
