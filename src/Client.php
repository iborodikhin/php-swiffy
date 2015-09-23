<?php
namespace Swiffy;

use Buzz\Browser;
use Buzz\Client\Curl;

/**
 * Google's swiffy client.
 */
class Client
{
    const TIMEOUT = 10;
    /**
     * Google API version.
     *
     * @var string
     */
    protected $apiVersion = "v1";

    /**
     * Google API URL.
     *
     * @var string
     */
    protected $apiUrl = "https://www.googleapis.com/rpc?key=AIzaSyCC_WIu0oVvLtQGzv4-g7oaWNoc-u8JpEI";

    /**
     * Google API User-Agent.
     *
     * @var string
     */
    protected $userAgent = "Swiffy Flash Extension";

    /**
     * Returns flash converted to html5
     *
     * @param  string         $filename
     * @return boolean|string
     */
    public function convert($filename, $jsonOnly = false)
    {
        $content = file_get_contents($filename);

        if (false === $content) {
            return false;
        }

        $request = json_encode(array(
            "apiVersion" => $this->apiVersion,
            "method"     => "swiffy.convertToHtml",
            "params"     => array(
                "client" => $this->userAgent,
                "input"  => $this->base64safe_encode($content),
            ),
        ));

        $browser  = new Browser();
        $client = new Curl();
        $client->setTimeout(self::TIMEOUT);

        $browser->setClient($client);
        $response = $browser->post($this->apiUrl, array("Content-Type" => "application/json"), $request);

        $response = json_decode($response->getContent(), true);

        if (array_key_exists("error", $response)) {
            return false;
        }

        $result = $this->base64safe_decode($response["result"]["response"]["output"]);

        if ($jsonOnly){
            $result = $this->getJson($result);
        }

        return $result;
    }

    /**
     * URL-safe base64 encode.
     *
     * @param  string $data
     * @return mixed
     */
    protected function base64safe_encode($data)
    {
        return str_replace(array("+", "/"), array("-", "_"), base64_encode($data));
    }

    public function getJson($subject){        
        $pattern = '/swiffyobject\s*=\s*({.*});\s*<\/script>/i';
        preg_match($pattern, $subject, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }

    /**
     * URL-safe base64 decode.
     *
     * @param  string $data
     * @return string
     */
    protected function base64safe_decode($data)
    {
        $result = str_replace(array("-", "_"), array("+", "/"), $data);
        $result = str_pad($result, strlen($result) + (4 - strlen($result) % 4) % 4, "=", STR_PAD_RIGHT);
        $gzip   = base64_decode($result);

        if (false !== $gzip) {
            return gzdecode($gzip);
        }

        return false;
    }
}
