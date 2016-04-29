<?php
namespace Saft\Example\PropertyHelper;
/**
 * TitleHelper with FileCache and BasicTriplePatternStore
 *
 * This file is ready to use and allows you, to insert it into your project and provide a titlehelper service out
 * of the box. Before you do that, please run 'composer update' to get all the required vendors.
 *
 * To setup a service, you can use Saft\Skeleton\PropertyHelper\RequestHandler. It encapsulates all the hassle you
 * need to do to setup the infrastructure. You just need to configure a few things and are good to go.
 *
 * If you want to use your own store, e.g. Virtuoso, just replace the $store variable and set it up. After that,
 * it should automatically use your new store instead of the BasicTriplePatternStore.
 *
 *
 * Usage:
 *   + make the index.php accessible
 *   + to build an index call index.php?action=createindex
 *   + to fetch values (e.g. titles) call index.php?action=fetchvalues&payload=http://uri/you/want/title/from
 */
use Saft\Addition\Virtuoso\Store\Virtuoso;
use Saft\Rdf\LiteralImpl;
use Saft\Rdf\NamedNodeImpl;
use Saft\Rdf\NodeFactoryImpl;
use Saft\Rdf\StatementImpl;
use Saft\Rdf\StatementFactoryImpl;
use Saft\Rdf\StatementIteratorFactoryImpl;
use Saft\Sparql\Query\QueryFactoryImpl;
use Saft\Sparql\Query\QueryUtils;
use Saft\Sparql\Result\ResultFactoryImpl;
use Saft\Store\BasicTriplePatternStore;
use Saft\Skeleton\PropertyHelper\RequestHandler;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

require 'vendor/autoload.php';
//require 'vendor/saft/saft-skeleton/vendor/tedivm/stash/autoload.php';
// handle action
$action = isset($_REQUEST['action']) ? strtolower($_REQUEST['action']) : '';
// get used graph, use http://titlecache/ as default
$graph = isset($_REQUEST['graph']) ? $_REQUEST['graph'] : 'http://titlecache/';
// get used cache backend, use file as default
$cacheName = isset($_REQUEST['cache-name']) ? $_REQUEST['cache-name'] : 'filesystem';
// may get preferd language
$language = isset($_REQUEST['language']) ? $_REQUEST['language'] : '';

$logFile = "./log.txt";

$payload = array();
if ( isset($_REQUEST['payload']) && !empty($_REQUEST['payload']) ) {
    $payload = explode(',', $_REQUEST['payload']);    
} elseif (isset($_FILES['payload'])) {
    $payload = explode("\n",  file_get_contents($_FILES['payload']['tmp_name']) );
}

// configuration file for all cache backends
$cachesConfig = "./config/caches-config.yml";
// pre set result - that container will be used to communicate with the client later on

$result = array('result' => false, 'errorMessage' => '');

try {
    // set virtuoso store
    $store = new Virtuoso(
        new NodeFactoryImpl(),
        new StatementFactoryImpl(),
        new QueryFactoryImpl(),
        new ResultFactoryImpl(),
        new StatementIteratorFactoryImpl(),
        array(
            "dsn" => "VOS",
            "username" => "dba",
            "password" => "dba",
        )
    );

    // setup request handler with graph
    $requestHandler = new RequestHandler($store, new NamedNodeImpl($graph));

    // get cache config
    $cacheConfig = array( 'name' => $cacheName );

    // read cache config from yaml file
    if ( file_exists($cachesConfig) ) {
        $yaml = new Parser();
        try {
            $caches = $yaml->parse(file_get_contents($cachesConfig));
        } catch (ParseException $e) {
            throw new \Exception('Unable to parse the YAML string from caches file: ' . $e->getMessage());
        }

        //var_dump($caches);
        if ( ! isset($caches['caches']) ) {
            throw new \Exception('Caches class in caches file not found.');
        }

        if ( ! isset($caches['caches'][$cacheName]) ) {
            throw new \Exception('Config for current cache in caches file not found.');
        }

        $cacheConfig = array_merge($cacheConfig, $caches['caches'][$cacheName]);
    } else {
        throw new \Exception('Caches YAML file not found.');    
    }

    // setup cache
    $requestHandler->setupCache( $cacheConfig );
   
   // set titlehelper (title related properties will be used)
    $requestHandler->setType('title');
    
    switch($action) {
        // build index for resources and according property values
        case 'createindex':
            $requestHandler->handle('createindex');
            $result['result'] = true;
            break;
        // fetch values for certain resources
        case 'fetchvalues':        
            $result['result'] = $requestHandler->handle('fetchvalues', $payload, $language);
            break;
        default;
            throw new \Exception('Parameter action not given, empty or unknown: '. $action);
            break;
    }
    
} catch (\Exception $e) {
    $result['errorMessage'] = $e->getMessage();
}

$fp = fopen($logFile, 'a');
$msg = 'action: ' . $action . ', cache: '.$cacheName.', graph: '.$graph;
$msg .= ! empty($payload) ? ", payloads: ". count($payload) : "";

if ( $result['result'] !== false ) {
    fwrite($fp, '[INFO] ' . $msg ."\n" );
} else {
    fwrite($fp, '[ERROR] '.$result['errorMessage'].', '.$msg . "\n" );
}

fclose($fp);

// output result
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Content-Type: application/json');
echo json_encode($result);