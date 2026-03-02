<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UrlScraperService
{
    protected $blocklist = [
        'pornhub.com',
        'xvideos.com',
        'xnxx.com',
        'redtube.com',
        'onlyfans.com',
        // Add more blocked domains as needed
    ];

    public function scrapeUrl(string $url): array
    {
        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'error' => 'Invalid URL format'
            ];
        }

        // Check if this looks like a product URL (not a category or search page)
        if (!$this->looksLikeProductUrl($url)) {
            return [
                'success' => false,
                'error' => 'This appears to be a category or search page, not a product page. Please use a link to a specific product.'
            ];
        }

        // Normalize Amazon URLs (convert mobile to desktop)
        $url = $this->normalizeAmazonUrl($url);

        // Check blocklist
        $host = parse_url($url, PHP_URL_HOST);
        foreach ($this->blocklist as $blocked) {
            if (Str::contains($host, $blocked)) {
                return [
                    'success' => false,
                    'error' => 'This website is not allowed'
                ];
            }
        }

        try {
            // Check if we should use WebScrapingAPI (for Amazon and other difficult sites)
            $useScrapingApi = $this->shouldUseScrapingApi($url);

            $html = null;

            if ($useScrapingApi && config('services.web_scraping_api.key')) {
                // Try WebScrapingAPI first for better reliability on difficult sites
                $html = $this->fetchViaScrapingApi($url);
            }

            if (!$html) {
                // Fall back to direct HTTP request (also primary path for non-difficult sites)
                $response = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                        'Accept-Language' => 'en-US,en;q=0.9',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'Cache-Control' => 'max-age=0',
                        'Sec-Ch-Ua' => '"Chromium";v="122", "Not(A:Brand";v="24", "Google Chrome";v="122"',
                        'Sec-Ch-Ua-Mobile' => '?0',
                        'Sec-Ch-Ua-Platform' => '"macOS"',
                        'Sec-Fetch-Dest' => 'document',
                        'Sec-Fetch-Mode' => 'navigate',
                        'Sec-Fetch-Site' => 'none',
                        'Sec-Fetch-User' => '?1',
                        'Upgrade-Insecure-Requests' => '1',
                    ])
                    ->get($url);

                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'error' => 'Could not access website (HTTP ' . $response->status() . ')'
                    ];
                }

                $html = $response->body();

                // Detect bot/CAPTCHA pages (very short response = blocked)
                if (strlen($html) < 10000 && (
                    str_contains(strtolower($html), 'robot') ||
                    str_contains(strtolower($html), 'captcha') ||
                    str_contains(strtolower($html), 'automated access') ||
                    str_contains(strtolower($html), 'unusual traffic')
                )) {
                    return [
                        'success' => false,
                        'error' => 'This website is blocking automated access. Try copying just the product URL (remove tracking parameters after the product ID).'
                    ];
                }
            }

            // Extract data with multiple fallback methods
            $data = [
                'title' => $this->extractTitle($html, $url),
                'image_url' => $this->extractImage($html, $url),
                'price' => $this->extractPrice($html, $url),
            ];

            // Check if we got meaningful data
            $hasData = !empty($data['title']) || !empty($data['image_url']) || !empty($data['price']);

            if (!$hasData) {
                return [
                    'success' => false,
                    'error' => 'Could not extract product details. The website may be blocking automated access. Please enter details manually.'
                ];
            }

            // Return success even if some fields are empty
            return [
                'success' => true,
                'data' => $data
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Timeout or connection error
            if (Str::contains($e->getMessage(), 'timed out') || Str::contains($e->getMessage(), 'timeout')) {
                return [
                    'success' => false,
                    'error' => 'Website took too long to respond. This site may require manual entry.'
                ];
            }
            return [
                'success' => false,
                'error' => 'Unable to connect to website. Please enter details manually.'
            ];
        } catch (\Exception $e) {
            \Log::error('URL scraping error', ['url' => $url, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Unable to load page. Please enter details manually.'
            ];
        }
    }

    public function downloadImage(string $imageUrl, string $directory = 'wish-photos'): ?string
    {
        try {
            $response = Http::timeout(15)->get($imageUrl);

            if (!$response->successful()) {
                return null;
            }

            // Generate unique filename
            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = $directory . '/' . Str::random(40) . '.' . $extension;

            // Store in default disk (local public disk or cloud storage in production)
            Storage::put($filename, $response->body());

            return $filename;

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    protected function extractMetaTag(string $html, string $property): ?string
    {
        $pattern = '/<meta[^>]+property=["\']' . preg_quote($property, '/') . '["\'][^>]+content=["\'](.*?)["\']/i';

        if (preg_match($pattern, $html, $matches)) {
            return trim($matches[1]);
        }

        // Try name attribute as fallback
        $pattern = '/<meta[^>]+name=["\']' . preg_quote($property, '/') . '["\'][^>]+content=["\'](.*?)["\']/i';

        if (preg_match($pattern, $html, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function extractTitle(string $html, string $url): ?string
    {
        // Try Amazon-specific patterns FIRST (more reliable than meta tags)
        if (Str::contains($url, 'amazon.')) {
            // Try productTitle span
            if (preg_match('/<span[^>]+id=["\']productTitle["\'][^>]*>(.*?)<\/span>/is', $html, $matches)) {
                $title = $this->cleanTitle($matches[1]);
                if ($title && !Str::contains(strtolower($title), 'amazon')) {
                    return $title;
                }
            }

            // Try title attribute in productTitle
            if (preg_match('/<span[^>]+id=["\']productTitle["\'][^>]+title=["\']([^"\']+)["\']/is', $html, $matches)) {
                $title = $this->cleanTitle($matches[1]);
                if ($title && !Str::contains(strtolower($title), 'amazon')) {
                    return $title;
                }
            }

            // Try JSON-LD structured data
            if (preg_match('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
                $json = json_decode($matches[1], true);
                if (isset($json['name'])) {
                    $title = $this->cleanTitle($json['name']);
                    if ($title && !Str::contains(strtolower($title), 'amazon')) {
                        return $title;
                    }
                }
            }
        }

        // Try Open Graph title
        $title = $this->extractMetaTag($html, 'og:title');
        if ($title) {
            $cleanTitle = $this->cleanTitle($title);
            if (!Str::contains(strtolower($cleanTitle), 'amazon.com')) {
                return $cleanTitle;
            }
        }

        // Try Twitter card title
        $title = $this->extractMetaTag($html, 'twitter:title');
        if ($title) {
            $cleanTitle = $this->cleanTitle($title);
            if (!Str::contains(strtolower($cleanTitle), 'amazon.com')) {
                return $cleanTitle;
            }
        }

        // Try regular title tag as last resort
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $cleanTitle = $this->cleanTitle($matches[1]);
            // Only return if it's not just "Amazon.com"
            if ($cleanTitle && !Str::contains(strtolower($cleanTitle), 'amazon.com')) {
                return $cleanTitle;
            }
        }

        return null;
    }

    protected function extractImage(string $html, string $url): ?string
    {
        // Try Target-specific patterns FIRST
        if (Str::contains($url, 'target.com')) {
            // Try JSON-LD structured data
            if (preg_match('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
                $json = json_decode($matches[1], true);
                \Log::info('Target JSON-LD found', ['json' => $json]);
                if (isset($json['image'])) {
                    if (is_string($json['image'])) {
                        return $json['image'];
                    } elseif (is_array($json['image']) && isset($json['image'][0])) {
                        return $json['image'][0];
                    }
                }
            }

            // Look for primary product image - try multiple patterns
            $imagePatterns = [
                '/<img[^>]+data-test=["\']image-gallery-\d+["\'][^>]+src=["\']([^"\']+)["\']/i',
                '/<img[^>]+class=["\'][^"\']*styles__StyledImg[^"\']*["\'][^>]+src=["\']([^"\']+)["\']/i',
                '/<img[^>]+class=["\'][^"\']*ProductImagesimage[^"\']*["\'][^>]+src=["\']([^"\']+)["\']/i',
                '/<picture[^>]*>.*?<img[^>]+src=["\']([^"\']+)["\'][^>]*>/is',
            ];

            foreach ($imagePatterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    \Log::info('Target image found with pattern', ['pattern' => $pattern, 'image' => $matches[1]]);
                    return $matches[1];
                }
            }

            \Log::warning('No Target image found', ['url' => $url]);
        }

        // Try Amazon-specific patterns FIRST
        if (Str::contains($url, 'amazon.')) {
            // Try JSON-LD structured data
            if (preg_match('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
                $json = json_decode($matches[1], true);
                if (isset($json['image'])) {
                    if (is_string($json['image'])) {
                        return $json['image'];
                    } elseif (is_array($json['image']) && isset($json['image'][0])) {
                        return $json['image'][0];
                    }
                }
            }

            // Look for landingImage in data attributes
            if (preg_match('/data-old-hires=["\']([^"\']+)["\']/i', $html, $matches)) {
                return html_entity_decode($matches[1]);
            }

            // Look for landingImage JSON
            if (preg_match('/["\']landingImage["\'][^}]+["\']url["\']:\s*["\']([^"\']+)["\']/i', $html, $matches)) {
                return str_replace('\/', '/', $matches[1]);
            }

            // Try landingImage img tag
            if (preg_match('/<img[^>]+id=["\']landingImage["\'][^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
                return $matches[1];
            }

            // Try imgTagWrapper
            if (preg_match('/<img[^>]+class=["\'][^"\']*imgTagWrapper[^"\']*["\'][^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
                return $matches[1];
            }
        }

        // Try Open Graph image
        $image = $this->extractMetaTag($html, 'og:image');
        if ($image) {
            return $image;
        }

        // Try Twitter card image
        $image = $this->extractMetaTag($html, 'twitter:image');
        if ($image) {
            return $image;
        }

        // Try Hobby Lobby-specific patterns
        if (Str::contains($url, 'hobbylobby.com')) {
            // Look for product image in picture tags
            if (preg_match('/<picture[^>]*class=["\'][^"\']*product[^"\']*["\'][^>]*>.*?<img[^>]+src=["\']([^"\']+)["\']/is', $html, $matches)) {
                return $matches[1];
            }

            // Look for main product image
            if (preg_match('/<img[^>]+class=["\'][^"\']*product.*?image[^"\']*["\'][^>]+src=["\']([^"\']+)["\']/is', $html, $matches)) {
                return $matches[1];
            }

            // Look for data-src attribute (lazy loading)
            if (preg_match('/<img[^>]+data-src=["\']([^"\']+)["\'][^>]*product/is', $html, $matches)) {
                return $matches[1];
            }
        }

        // Try JSON-LD structured data (generic fallback)
        if (preg_match('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            $json = json_decode($matches[1], true);
            if (isset($json['image'])) {
                if (is_string($json['image'])) {
                    return $json['image'];
                } elseif (is_array($json['image']) && isset($json['image'][0])) {
                    return $json['image'][0];
                }
            }
        }

        // Try itemprop="image" attribute
        if (preg_match('/itemprop=["\']image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function extractPrice(string $html, string $url): ?string
    {
        // Try Open Graph price
        $price = $this->extractMetaTag($html, 'og:price:amount');
        if ($price) {
            return $this->cleanPrice($price);
        }

        $price = $this->extractMetaTag($html, 'product:price:amount');
        if ($price) {
            return $this->cleanPrice($price);
        }

        // Try Walmart-specific patterns
        if (Str::contains($url, 'walmart.com')) {
            // Look for price in JSON-LD
            if (preg_match('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
                $json = json_decode($matches[1], true);
                \Log::info('Walmart JSON-LD found', ['json' => $json]);

                // Walmart uses offers as an array
                if (isset($json['offers']) && is_array($json['offers']) && isset($json['offers'][0]['price'])) {
                    return $this->cleanPrice($json['offers'][0]['price']);
                } elseif (isset($json['offers']['price'])) {
                    return $this->cleanPrice($json['offers']['price']);
                } elseif (isset($json['price'])) {
                    return $this->cleanPrice($json['price']);
                }
            }

            // Look for itemprop="price" attribute
            if (preg_match('/itemprop=["\']price["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
                \Log::info('Walmart price found via itemprop', ['price' => $matches[1]]);
                return $this->cleanPrice($matches[1]);
            }

            // Look for data-price attribute
            if (preg_match('/data-price=["\']([0-9.]+)["\']/i', $html, $matches)) {
                \Log::info('Walmart price found via data-price', ['price' => $matches[1]]);
                return $this->cleanPrice($matches[1]);
            }

            // Look for price in span with specific classes
            if (preg_match('/<span[^>]+class=["\'][^"\']*price[^"\']*["\'][^>]*>.*?\$([0-9,.]+)/is', $html, $matches)) {
                \Log::info('Walmart price found via span', ['price' => $matches[1]]);
                return $this->cleanPrice($matches[1]);
            }

            \Log::warning('No Walmart price found', ['url' => $url]);
        }

        // Try Target-specific patterns
        if (Str::contains($url, 'target.com')) {
            // Look for price in JSON-LD
            if (preg_match('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
                $json = json_decode($matches[1], true);
                if (isset($json['offers']['price'])) {
                    return $this->cleanPrice($json['offers']['price']);
                } elseif (isset($json['price'])) {
                    return $this->cleanPrice($json['price']);
                }
            }

            // Look for data-test attribute with price
            if (preg_match('/data-test=["\']product-price["\'][^>]*>.*?\$([0-9.]+)/is', $html, $matches)) {
                return $this->cleanPrice($matches[1]);
            }
        }

        // Try Amazon-specific patterns
        if (Str::contains($url, 'amazon.')) {
            // Look for price in various Amazon formats
            $patterns = [
                // Standard price whole/fraction combo
                '/<span[^>]+class=["\'][^"\']*a-price-whole[^"\']*["\'][^>]*>([^<]+)<\/span><span[^>]+class=["\'][^"\']*a-price-fraction[^"\']*["\'][^>]*>([^<]+)</',
                // Just price whole
                '/<span[^>]+class=["\'][^"\']*a-price-whole[^"\']*["\'][^>]*>([0-9,]+)</',
                // Price to pay
                '/<span[^>]+class=["\'][^"\']*priceToPay[^"\']*["\'][^>]*>.*?\$([0-9,.]+)/',
                // Apex price
                '/<span[^>]+class=["\'][^"\']*apexPriceToPay[^"\']*["\'][^>]*>.*?\$([0-9,.]+)/',
                // JSON price amount
                '/["\']priceAmount["\']:([0-9.]+)/',
                // Buy box price
                '/<span[^>]+id=["\']priceblock_ourprice["\'][^>]*>.*?\$([0-9,.]+)/',
                '/<span[^>]+id=["\']priceblock_dealprice["\'][^>]*>.*?\$([0-9,.]+)/',
                // Mobile price patterns
                '/<span[^>]+class=["\'][^"\']*a-color-price[^"\']*["\'][^>]*>.*?\$([0-9,.]+)/',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    // Handle whole + fraction pattern
                    if (isset($matches[2]) && !empty($matches[2])) {
                        $price = $matches[1] . '.' . $matches[2];
                    } else {
                        $price = $matches[1];
                    }
                    $cleanedPrice = $this->cleanPrice($price);
                    if ($cleanedPrice) {
                        return $cleanedPrice;
                    }
                }
            }
        }

        // Generic fallback patterns for other sites
        // Try JSON-LD structured data (generic)
        if (preg_match('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            $json = json_decode($matches[1], true);
            // Handle offers as array
            if (isset($json['offers']) && is_array($json['offers']) && isset($json['offers'][0]['price'])) {
                return $this->cleanPrice($json['offers'][0]['price']);
            }
            // Handle offers as object
            elseif (isset($json['offers']['price'])) {
                return $this->cleanPrice($json['offers']['price']);
            }
            // Direct price field
            elseif (isset($json['price'])) {
                return $this->cleanPrice($json['price']);
            }
        }

        // Try itemprop="price" attribute
        if (preg_match('/itemprop=["\']price["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $this->cleanPrice($matches[1]);
        }

        // Try common price class patterns
        $pricePatterns = [
            '/<span[^>]+class=["\'][^"\']*product-price[^"\']*["\'][^>]*>.*?\$([0-9,.]+)/is',
            '/<div[^>]+class=["\'][^"\']*price[^"\']*["\'][^>]*>.*?\$([0-9,.]+)/is',
            '/<span[^>]+class=["\'][^"\']*price[^"\']*["\'][^>]*>.*?\$([0-9,.]+)/is',
        ];

        foreach ($pricePatterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                return $this->cleanPrice($matches[1]);
            }
        }

        return null;
    }

    protected function cleanTitle(string $title): string
    {
        // Remove extra whitespace and decode HTML entities
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);

        // Remove common Amazon suffixes
        $title = preg_replace('/ - Amazon\.com.*$/i', '', $title);
        $title = preg_replace('/\s*:\s*Amazon\.[a-z]+.*$/i', '', $title);

        return $title;
    }

    protected function cleanPrice(string $price): ?string
    {
        // Remove currency symbols and non-numeric characters except decimal point
        $price = preg_replace('/[^\d.]/', '', $price);

        // Ensure valid decimal format
        if (preg_match('/^\d+\.?\d{0,2}$/', $price)) {
            return $price;
        }

        return null;
    }

    protected function normalizeAmazonUrl(string $url): string
    {
        // Convert Amazon mobile URLs to desktop URLs for better scraping
        if (Str::contains($url, 'amazon.')) {
            // Extract ASIN from various Amazon URL formats
            $asin = null;

            // Mobile format: /gp/aw/d/{ASIN}/
            if (preg_match('/\/gp\/aw\/d\/([A-Z0-9]{10})/i', $url, $matches)) {
                $asin = $matches[1];
            }
            // Desktop format: /dp/{ASIN}/
            elseif (preg_match('/\/dp\/([A-Z0-9]{10})/i', $url, $matches)) {
                $asin = $matches[1];
            }
            // Product format: /product/{ASIN}/
            elseif (preg_match('/\/product\/([A-Z0-9]{10})/i', $url, $matches)) {
                $asin = $matches[1];
            }

            // If we found an ASIN, construct clean desktop URL
            if ($asin) {
                $host = parse_url($url, PHP_URL_HOST);
                return "https://{$host}/dp/{$asin}";
            }
        }

        return $url;
    }

    protected function looksLikeProductUrl(string $url): bool
    {
        // Check for common non-product page patterns
        $nonProductPatterns = [
            '/\/c\//',           // Category pages (Target, Walmart)
            '/\/b\//',           // Brand/department pages (Amazon)
            '/\/s\?/',           // Search results (Amazon)
            '/search/',          // Search pages
            '/category/',        // Category pages
            '/collection/',      // Collection pages
            '/browse/',          // Browse pages
            '/shop\//',          // Shop landing pages
            '/deals\/?$/',       // Deals landing pages
        ];

        foreach ($nonProductPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return false;
            }
        }

        // Amazon product URLs should have /dp/ or /gp/product/
        if (Str::contains($url, 'amazon.')) {
            return preg_match('/\/(dp|gp\/product|gp\/aw\/d)\/[A-Z0-9]{10}/i', $url);
        }

        // For other sites, assume it's a product page if we can't determine otherwise
        return true;
    }

    protected function shouldUseScrapingApi(string $url): bool
    {
        // Use WebScrapingAPI for sites that typically block scrapers
        $difficultSites = [
            'amazon.',
            'ebay.',
            'walmart.com',
            'target.com',
            'bestbuy.com',
        ];

        foreach ($difficultSites as $site) {
            if (Str::contains($url, $site)) {
                return true;
            }
        }

        return false;
    }

    protected function fetchViaScrapingApi(string $url): ?string
    {
        try {
            $apiKey = config('services.web_scraping_api.key');

            if (!$apiKey) {
                return null;
            }

            // Target requires JavaScript rendering and more time
            $isTarget = Str::contains($url, 'target.com');
            $timeout = $isTarget ? 60 : 30; // Give Target 60 seconds
            $renderJs = $isTarget ? '1' : '0'; // Enable JS for Target

            // Call WebScrapingAPI
            $response = Http::timeout($timeout)
                ->get('https://api.webscrapingapi.com/v2', [
                    'api_key' => $apiKey,
                    'url' => $url,
                    'render_js' => $renderJs,
                ]);

            $body = $response->body();

            // Check if response is JSON (error/status response)
            $json = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                if (isset($json['error'])) {
                    \Log::warning('WebScrapingAPI error', ['response' => $json, 'url' => $url]);
                    return null;
                }
                if (isset($json['status'])) {
                    \Log::warning('WebScrapingAPI status error', ['response' => $json]);
                    return null;
                }
            }

            if ($response->successful() && strlen($body) > 1000) {
                return $body;
            }

            return null;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Specific handling for timeout errors
            \Log::warning('WebScrapingAPI timeout', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }
}
