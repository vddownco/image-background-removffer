<?php

namespace App\Jobs;

use App\Models\WebsiteDetails;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Http;

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
            //  Step 1: Get the body of the html
            $htmlBody = Browsershot::url($this->url)->waitUntilNetworkIdle()->bodyHtml();

            //  Step 2: Create the prompt for GPT
            $gptPrompt = $this->generateGptPrompt($htmlBody);

            //  Step 3: Get the product details from GPT
            $response = $this->getGPTResponse($gptPrompt);

            // Step 4: Store the details in the database
            $productDataId = $this->storeProductData($response, $this->url);

            // Step 5: Dispatch an event with ID



        } catch (\Exception $e) {
            Log::error('Error scraping product details: ' . $e->getMessage());
        }
    }

    /**
     * Create a prompt for GPT-4o-mini model.
     *
     * @param string $htmlBody
     * @return array
     */
    protected function generateGptPrompt(string $htmlBody): array
    {
        return [
            [
                "role" => "system",
                "content" => "You are an expert at structured data extraction from HTML. You will be given a HTML from a ecommerce product page and should extract these 'Product Name, Product Subtitle, Product Price, Product Description, Website Logo Image URL, Company Name' information from this HTML and convert it into the given structure."
            ],
            [
                "role" => "user",
                "content" => $htmlBody
            ]
        ];
    }

    /**
     * Get the response from GPT-4o-mini.
     *
     * @param string $prompt
     * @return array
     */
    protected function getGPTResponse(array $prompt): array
    {
        $response = Http::withToken(config('services.openai.secret'))
            ->post('https://api.openai.com/v1/chat/completions', [
                "model" => "gpt-4o-mini",
                "messages" => $prompt,
                "response_format" => [
                    "type" => "json_schema",
                    "json_schema" => [
                        "name" => "product_data_extraction",
                        "schema" => [
                            "type" => "object",
                            "properties" => [
                                "productName" => ["type" => "string"],
                                "productSubTitle" => ["type" => "string"],
                                "productPrice" => ["type" => "string"],
                                "productDescription" => ["type" => "string"],
                                "websiteLogoImageURL" => ["type" => "string"],
                                "companyName" => ["type" => "string"],
                            ],
                            "required" => ["productName", "productSubTitle", "productPrice", "productDescription", "websiteLogoImageURL", "companyName"],
                            "additionalProperties" => false
                        ],
                        "strict" => true
                    ]
                ]
            ])->json('choices.0.message.content');

        $arrayData = json_decode($response, true);

        return $arrayData;
    }

    /**
     * Optionally store the product data in a database.
     *
     * @param array $productData
     * @return string
     */
    protected function storeProductData(array $productData, string $url): string
    {
        // Extract Website Domain
        $parsedUrl = parse_url($url);
        $websiteDomain = $parsedUrl['host'] ?? 'N/A';

        $record = WebsiteDetails::create([
            'name' => $productData['name'],
            'subTitle' => $productData['subTitle'],
            'price' => $productData['price'],
            'description' => $productData['description'],
            'domain' => $websiteDomain,
            'url' => $url,
            'logoUrl' => $productData['logoUrl'],
            'companyName' => $productData['companyName'],
        ]);

        return $record->id;
    }

    // =================================Some AI generated codes for reference=====================================

    //     if ($logoUrl !== 'N/A' && !preg_match('/^http(s)?:\/\//', $logoUrl)) {
    //         $logoUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/' . ltrim($logoUrl, '/');
    //     } else {
    //         $logoUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/favicon.ico';
    //     }

    /**
     * Process GPT's response and extract relevant product data.
     *
     * @param string $response
     * @return array
     */
    // protected function processResponse(string $response): array
    // {
    //     $lines = explode("\n", $response);
    //     $data = [];

    //     foreach ($lines as $line) {
    //         if (strpos($line, 'Product Name:') !== false) {
    //             $data['name'] = trim(str_replace('Product Name:', '', $line));
    //         } elseif (strpos($line, 'Product Subtitle:') !== false) {
    //             $data['subTitle'] = trim(str_replace('Product Subtitle:', '', $line));
    //         } elseif (strpos($line, 'Price:') !== false) {
    //             $data['price'] = trim(str_replace('Price:', '', $line));
    //         } elseif (strpos($line, 'Description:') !== false) {
    //             $data['description'] = trim(str_replace('Description:', '', $line));
    //         } elseif (strpos($line, 'Website Domain:') !== false) {
    //             $data['domain'] = trim(str_replace('Website Domain:', '', $line));
    //         } elseif (strpos($line, 'Page URL:') !== false) {
    //             $data['url'] = trim(str_replace('Page URL:', '', $line));
    //         } elseif (strpos($line, 'Website Logo URL:') !== false) {
    //             $data['logoUrl'] = trim(str_replace('Website Logo URL:', '', $line));
    //         } elseif (strpos($line, 'Company Name:') !== false) {
    //             $data['companyName'] = trim(str_replace('Company Name:', '', $line));
    //         }
    //     }

    //     return $data;
    // }
}
