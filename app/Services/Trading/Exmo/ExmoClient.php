<?php

declare(strict_types=1);

namespace App\Services\Trading\Exmo;

use App\Services\Trading\StockClientInterface;
use Mockery\Exception;

final class ExmoClient implements StockClientInterface
{
    public function getOrderBook(string $pair, int $limit): array
    {
        return $this->apiQuery('order_book', ['pair' => $pair, 'limit' => $limit]);
    }

    public function getHistory(string $pair): array
    {
        return $this->apiQuery('trades', ['pair' => $pair]);
    }

    public function getPairsSettings(): array
    {
        return $this->apiQuery('pair_settings');
    }

    public function getBalance(): array
    {
        return $this->apiQuery('user_info');
    }

    public function createOrder(array $parameters): array
    {
        return $this->apiQuery('order_create', $parameters);
    }

    public function cancelOrder(int $orderId): array
    {
        return $this->apiQuery('order_cancel', ['order_id' => $orderId]);
    }

    public function getOpenOrder(): array
    {
        return $this->apiQuery('user_open_orders');
    }

    /**
     * API Exmo
     * Downloaded from https://github.com/exmo-dev/exmo_api_lib/blob/master/php/exmo.php
     */
    private function apiQuery($apiName, array $req = []): mixed
    {
        $mt = explode(' ', microtime());
        // API settings
        $url = "https://api.exmo.com/v1.1/$apiName";
        $req['nonce'] = $mt[1] . substr($mt[0], 2, 6);
        // generate the POST data string
        $postData = http_build_query($req);
        $sign = hash_hmac('sha512', $postData, env('EXMO_SECRET'));
        // generate the extra headers
        $headers = array(
            'Sign: ' . $sign,
            'Key: ' . env('EXMO_KEY'),
        );

        static $ch = null;

        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; PHP client; ' . php_uname('s') . '; PHP/' . phpversion() . ')');
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // run the query
        $res = curl_exec($ch);
        if ($res === false) {
            throw new Exception('Could not get reply: ' . curl_error($ch));
        }
        $dec = @json_decode($res, true);
        if ($dec === null) {
            throw new Exception('Invalid data received, please make sure connection is working and requested API exists');
        }

        return $dec;
    }
}
