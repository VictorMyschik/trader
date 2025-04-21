<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testMy(): void
    {
        // $content = $this->getContent();

        $content = file_get_contents(__DIR__ . '/test.html');

        $crawler = new Crawler($content);

        $scriptContent = $crawler->filter('div.row.with-mb.sm-gutters');

        $properties = [];

        $scriptContent->filter('.teaser-tile')->each(function (Crawler $node) use (&$properties) {
            $properties[] = [
                'title' => $node->filter('.teaser-title')->text(),
                'rooms' => $node->filter('.unit-item')->eq(0)->filter('span')->text(),
                'area'  => $node->filter('.unit-item')->eq(1)->filter('span')->text(),
                'price' => $node->filter('.price-item')->text(),
                'link'  => $node->filter('a')->attr('href'),
                'id'    => $node->attr('data-id'),
            ];
        });
    }

    private function getContent(): string
    {
        try {
            $client = new Client(['cookies' => true]);
            $url = 'https://realting.com/ru/poland/warsaw/apartments/3-bedrooms?movemap-input=1&slug=property-for-sale&type=apartments&Estate%5Bform_type%5D=apartments&Estate%5Broom_alias%5D=3-bedrooms&geoArray=208350%2C208351%2C76415%2C208352%2C115130&search=%D0%92%D0%B0%D1%80%D1%88%D0%B0%D0%B2%D0%B0&Estate%5Bgeo_id%5D=208350&Estate%5Bcurrency%5D=USD&Estate%5BminArea%5D=60&Estate%5BroomCnt%5D%5B3%5D=3&Estate%5Bzoom%5D=11&Estate%5Bx1%5D=20.63507&Estate%5By1%5D=52.00179&Estate%5Bx2%5D=21.37871&Estate%5By2%5D=52.46061&referrer_id=';

            $response = $client->get(
                $url,
                [
                    'headers' => [
                        'Accept'          => '*/*',
                        'Connection'      => 'keep-alive',
                        'Accept-Encoding' => 'gzip, deflate, br',
                        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36',

                    ],
                    'cookies' => new CookieJar(),
                ]
            );

            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            $code = $e->getCode();
            $body = $e->getResponse()->getBody()->getContents();
        }
    }

    public function testImport(): void
    {
        /** @var TelegramService $service */
        $service = app(TelegramService::class);
        $service->sendMessage(1, SiteType::OLX, []);
    }
}
