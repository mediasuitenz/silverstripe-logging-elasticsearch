# Silverstripe logging Elasticsearch

Out of the box Silverstripe uses a package called monolog as its “ready to go” logging solution. 
Monolog seems to be ubiquitous for PHP logging in multiple frameworks and is well supported..  

You can specify a handler from a folder of handlers that connects to an interface for a wide range of logging approaches into a Slack channel, Stream into a logging file (most common approach),  write  to DB etc. 

One of these handlers is designed for ElasticSearch. However due to SS4 restricting requirement to monolog v1.2.7.1 there are some minor complications. 

## How it works 

We have the monolog package which is used to bridge the SS specific framework internal errors/logs and converts into the PSR4 compatible standard, then hands this over to a client that handles the request/response to the ElasticSearch cloud instance.

This client is a package named elastica which binds the output of the logging from monolog to a curl request to the cloud instance. Another code base that is very popular in php community. Though seems to be redundant in later versions of monolog. 

*In silverstripe 5 we can use monolog v3.4.1 which handles this all natively with the API provided from  ElasticSearch.  This will mitigate the need for the custom code described in the solution.

The older releases of elastica are not compatible with the Cloud ES api. However upgrading elastica then creates issues with the older version monolog v1.2.1.7 codebase. 

Most notably the “code formatter” references methods that are now deprecated and removed from the elastica codebase. 

## Installation 

composer require mediasuite/silverstripe-logging-elasticsearch

Add the following code snippit to your _config.php with the 3 ENV varaibles set in your .env based on your ES cloud instance config

app/_config.php

```

$logger = Injector::inst()->get(LoggerInterface::class);
if ($logger instanceof Logger
    && Environment::getEnv('ELASTIC_SEARCH_INDEX')
    && Environment::getEnv('ELASTIC_SEARCH_HOST')
    && Environment::getEnv('ELASTIC_SEARCH_APIKEY')) {
    $options = array(
        'index' => Environment::getEnv('ELASTIC_SEARCH_INDEX')      // Elastic index name
    );
    $config = [
        'connections' => [
            [   'host' => Environment::getEnv('ELASTIC_SEARCH_HOST'),
                'port' => 443,
                'transport' => 'Https',
                'headers' => [
                'Authorization' => 'ApiKey' . Environment::getEnv('ELASTIC_SEARCH_APIKEY')
                ]
            ]
        ]
    ];
    $client = new Client($config);
    $handler = new CustomESHandler($client, $options);
    $logger->pushHandler($handler);
}
```

