<?php

namespace CViniciusSDias\GoogleCrawler;

use CViniciusSDias\GoogleCrawler\Exception\InvalidGoogleHtmlException;
use CViniciusSDias\GoogleCrawler\Proxy\GoogleProxyAbstractFactory;
use CViniciusSDias\GoogleCrawler\Proxy\HttpClient\GoogleHttpClient;
use CViniciusSDias\GoogleCrawler\Proxy\NoProxyAbstractFactory;
use CViniciusSDias\GoogleCrawler\Proxy\UrlParcer\GoogleUrlParser;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use DOMElement;

/**
 * Google Crawler
 *
 * @package CViniciusSDias\GoogleCrawler
 * @author Vinicius Dias
 */
class Crawler
{
    private GoogleHttpClient $httpClient;
    private GoogleUrlParser $parser;

    public function __construct(
        GoogleProxyAbstractFactory $factory = null,
    ) {
        if ($factory === null) {
            $factory = new NoProxyAbstractFactory();
        }

        $this->httpClient = $factory->createGoogleHttpClient();
        $this->parser = $factory->createGoogleUrlParser();
    }

    /**
     * Returns the 100 first found results for the specified search term
     *
     * @param SearchTermInterface $searchTerm
     * @param string $googleDomain
     * @param string $countryCode
     * @return ResultList
     * @throws \GuzzleHttp\Exception\ServerException If the proxy was overused
     * @throws \GuzzleHttp\Exception\ConnectException If the proxy is unavailable or $countrySpecificSuffix is invalid
     */
    public function getResults(SearchTermInterface $searchTerm, string $googleDomain = 'google.com', string $countryCode = ''): ResultList
    {
        $googleUrl = "https://$googleDomain/search?q={$searchTerm}&num=100";
        if (!empty($countryCode)) {
            $googleUrl .= "&gl={$countryCode}";
        }

        $response = $this->httpClient->getHttpResponse($googleUrl);
        $stringResponse = (string) $response->getBody();
        $domCrawler = new DomCrawler($stringResponse);
        $googleResultList = $this->createGoogleResultList($domCrawler);

        $resultList = new ResultList($googleResultList->count());

        $domElementParser = new DomElementParser($this->parser);
        foreach ($googleResultList as $googleResultElement) {
            $parsedResultMaybe = $domElementParser->parse($googleResultElement);
            $parsedResultMaybe->select(fn ($parsedResult) => $resultList->addResult($parsedResult));
        }

        return $resultList;
    }

    private function createGoogleResultList(DomCrawler $domCrawler): DomCrawler
    {
        $googleResultList = $domCrawler->filterXPath('//div[@class="Gx5Zad fP1Qef xpd EtOod pkphOe"]');
        if ($googleResultList->count() === 0) {
            throw new InvalidGoogleHtmlException('No parsable element found');
        }
        return $googleResultList;
    }
}
