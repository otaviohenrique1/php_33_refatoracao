<?php

namespace CViniciusSDias\GoogleCrawler\Proxy;
use CViniciusSDias\GoogleCrawler\Proxy\HttpClient\NoProxyGoogleHtttpClient;
use CViniciusSDias\GoogleCrawler\Proxy\UrlParcer\NoProxyGoogleUrlParser;

class NoProxyAbstractFactory implements GoogleProxyAbstractFactory
{
  public function createGoogleHttpClient(): GoogleHttpClient
  {
    return new NoProxyGoogleHtttpClient();
  }

  public function createGoogleUrlParser(): GoogleUrlParser
  {
    return new NoProxyGoogleUrlParser();
  }

}
