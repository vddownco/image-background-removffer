<?php

namespace App\Jobs;

use App\Models\WebsiteDetails;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
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
            //  Step 1: Get the body of the html or base64 screenshot
            $htmlBody = Browsershot::url($this->url)->waitUntilNetworkIdle()->bodyHtml();
            //$base64Data = Browsershot::url($this->url)->waitUntilNetworkIdle()->base64Screenshot();

            //  Step 2: Cleanup the HTML
            $cleanedHtml = $this->cleanupHtml($htmlBody);

            //  Step 3: Create the prompt for GPT
            $gptTextPrompt = $this->generateGptPrompt($cleanedHtml);
            //$gptVisionPrompt = $this->generateGptVisionPrompt($base64Data);

            //  Step 4: Get the product details from GPT
            $response = $this->getGPTResponse($gptTextPrompt);

            //  Step 5: Store the details in the database
            $productDataId = $this->storeProductData($response, $this->url);

            //  Step 6: Dispatch an event with ID



        } catch (\Exception $e) {
            Log::error('Error scraping product details: ' . $e->getMessage());
        }
    }

    /**
     * Remove unneccessary whitespaces, scripts and styles from html
     * @param string $uncleanedHtml
     * @return string
     */
    protected function cleanupHtml(string $uncleanedHtml): string
    {
        $tidyConfig = [
            'indent' => true,
            'output-xhtml' => true,
            'wrap' => 200
        ];
        // Clean up with Tidy
        $html = tidy_repair_string($uncleanedHtml, $tidyConfig, 'UTF8');

        $html = preg_replace('/\s+/', ' ', $html); // Remove excess whitespace
        $html = preg_replace('/<!--.*?-->/', '', $html); // Remove comments
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html); // Remove JavaScript
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html); // Remove CSS
        $html = preg_replace('/\sstyle=".*?"/i', '', $html); // Remove inline styles


        // Use DomCrawler to remove specific elements
        $crawler = new Crawler($html);

        // Remove <nav> , #nav, and .nav elements (e.g., navigation bars)
        $crawler->filter('nav')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });
        $crawler->filter('.nav')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });
        $crawler->filter('#nav')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove <footer> , #footer, and .footer elements (e.g., footer sections)
        $crawler->filter('footer')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });
        $crawler->filter('.footer')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });
        $crawler->filter('#footer')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Get the cleaned HTML after removal
        return $crawler->html();
    }

    /**
     * Create a prompt for GPT-4o-vision model.
     *
     * @param string $base64Data
     * @return array
     */
    protected function generateGptVisionPrompt(string $base64Data): array
    {
        return [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "text",
                        "text" => "You are an expert in visual data extraction. You will be given an image of an e-commerce product page. Your task is to analyze this image and extract specific product information in a structured JSON format. Please locate the website's logo within the image, capture it, and provide it as a base64-encoded string in the specified field. Use the following JSON schema to structure your response:

                        {
                            \'productName\': \'string\',
                            \'productSubTitle\': \'string\',
                            \'productPrice\': \'string\',
                            \'productDescription\': \'string\',
                            \'websiteLogoImageBase64Data\': \'base64 string\',
                            \'companyName\': \'string\'
                        }

                        Ensure the extracted data matches the required types, and provide only the specified properties.",
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => [
                            "url" => "data:image/jpeg;base64," . $base64Data
                        ],
                    ],
                ],
            ]
        ];
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
                "content" => "
                    Extract the following information from the provided HTML content and organize it into the specified schema. Only include the relevant data if available in the HTML.
                    Information to extract:
                        -Product Name: The main name or title of the product. If the product name exceeds two words, generate a suitable name that retains the core essence of the existing name.
                        -Product Subtitle: Any additional descriptive title or tagline related to the product. If a subtitle exists, rework it into a more catchy and meaningful version, no longer than five words. If no subtitle is present, generate a new catchy, meaningful subtitle based on the product description.
                        -Product Price: The price of the product, including any currency symbols or units.
                        -Product Description: A detailed description or summary of the product, typically found in a paragraph or list format.
                        -Product Image URL: The URL of the first image associated with the product, if available.
                        -Website Logo URL: The URL of the website's main logo image, often found in the header area.
                        -Company Name: The name of the company that owns or sells the product, generally found near the logo or in the footer.
                    Schema for Output:
                        {
                            product_name: Extracted product name or 'N/A',
                            product_subtitle: Extracted product subtitle or 'N/A',
                            product_price: Extracted product price or 'N/A',
                            product_description: Extracted product description or 'N/A',
                            product_image_url: Extracted URL for first product image or 'N/A'
                            website_logo_url: Extracted URL for website logo or 'N/A',
                            company_name: Extracted company name or 'N/A',
                        }
                    Notes for extraction:
                        - Ensure all values are extracted as plain text except for website_logo_url and product_image_url, which should contain a full URL if available.
                        - If any field is not present in the HTML, set it to 'N/A' in the output schema.
                        - If the product subtitle is 'N/A', generate a meaningful and catchy subtitle based on the description (no more than five words). Focus on capturing the product's essence or a key selling point.
                        - If the product already has a subtitle, transform it into a more catchy, impactful version. Make it shorter, no more than five words, and ensure it reflects the core features or value of the product.
                        - If the product name exceeds two words, generate a suitable name that retains the core essence of the existing name.
                "
            ],
            [
                "role" => "user",
                "content" => "HTML to Process:" . $htmlBody
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
                                "product_name" => ["type" => "string"],
                                "product_subtitle" => ["type" => "string"],
                                "product_price" => ["type" => "string"],
                                "product_description" => ["type" => "string"],
                                "product_image_url" => ["type" => "string"],
                                "website_logo_url" => ["type" => "string"],
                                "company_name" => ["type" => "string"],
                            ],
                            "required" => ["product_name", "product_subtitle", "product_price", "product_description", "product_image_url", "website_logo_url", "company_name"],
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
        $productImageUrl = (substr($productData['product_image_url'], 0, 2) === "//") ? 'https:' . $productData['product_image_url'] : $productData['product_image_url'];
        $logoImageUrl = (substr($productData['website_logo_url'], 0, 2) === "//") ? 'https:' . $productData['website_logo_url'] : $productData['website_logo_url'];

        $record = WebsiteDetails::create([
            'name' => $productData['product_name'],
            'subTitle' => $productData['product_subtitle'],
            'price' => $productData['product_price'],
            'description' => $productData['product_description'],
            'domain' => $websiteDomain,
            'url' => $url,
            'productImageUrl' => $productImageUrl,
            'logoUrl' => $logoImageUrl,
            'companyName' => $productData['company_name'],
        ]);

        dump($record);

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
