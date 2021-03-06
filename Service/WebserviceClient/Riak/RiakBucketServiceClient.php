<?php

namespace Kbrw\RiakBundle\Service\WebserviceClient\Riak;

use Guzzle\Http\Exception\CurlException;
use Kbrw\RiakBundle\Exception\RiakUnavailableException;
use Kbrw\RiakBundle\Service\WebserviceClient\BaseServiceClient;

class RiakBucketServiceClient extends BaseServiceClient
{
    /**
     * @param  \Kbrw\RiakBundle\Model\Cluster\Cluster $cluster
     * @param  \Kbrw\RiakBundle\Model\Bucket\Bucket   $bucket
     * @return array<string>
     */
    public function keys($cluster, $bucket)
    {
        $keys = array();
        $request = $this->getClient($cluster->getGuzzleClientProviderService(), $this->getConfig($cluster, $bucket->getName(), "stream"))->get();
        $request->getCurlOptions()->set(CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$keys) {
            $content = json_decode($data, true);
            if (is_array($content) && array_key_exists("keys", $content)) {
                foreach ($content["keys"] as $key) {
                    $keys[] = $key;
                }
            }

            return strlen($data);
        });
        try {
            $response = $request->send();
            $this->logResponse($response, array("method" => "GET"));
        } catch (CurlException $e) {
            $this->logger->err("Riak is unavailable" . $e->getMessage());
            throw new RiakUnavailableException();
        } catch (\Exception $e) {
            $this->logger->err("Error while getting keys" . $e->getMessage());
        }

        return $keys;
    }

    /**
     * @param  \Kbrw\RiakBundle\Model\Cluster\Cluster $cluster
     * @param  \Kbrw\RiakBundle\Model\Bucket\Bucket   $bucket
     * @return integer
     */
    public function count($cluster, $bucket)
    {
        $keys = 0;
        $request = $this->getClient($cluster->getGuzzleClientProviderService(), $this->getConfig($cluster, $bucket->getName(), "stream"))->get();
        $request->getCurlOptions()->set(CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$keys) {
            $content = json_decode($data, true);
            if (is_array($content) && array_key_exists("keys", $content)) {
                $keys += count($content["keys"]);
            }

            return strlen($data);
        });
        try {
            $response = $request->send();
            $this->logResponse($response, array("method" => "GET"));
        } catch (CurlException $e) {
            $this->logger->err("Riak is unavailable" . $e->getMessage());
            throw new RiakUnavailableException();
        } catch (\Exception $e) {
            $this->logger->err("Error while getting keys" . $e->getMessage());
        }

        return $keys;
    }

    /**
     * @param  \Kbrw\RiakBundle\Model\Cluster\Cluster $cluster
     * @param  string                                 $bucketName
     * @return \Kbrw\RiakBundle\Model\Bucket\Bucket
     */
    public function properties($cluster, $bucketName)
    {
        $bucket = null;
        $request = $this->getClient($cluster->getGuzzleClientProviderService(), $this->getConfig($cluster, $bucketName, null, true))->get();
        try {
            $response = $request->send();
            $extra = array("method" => "GET");
            if ($response->getStatusCode() == "200") {
                $ts = microtime(true);
                $bucket = $this->serializer->deserialize($response->getBody(true), "Kbrw\RiakBundle\Model\Bucket\Bucket", "json");
                $extra["deserialization_time"] = microtime(true) - $ts;
            }
            $this->logResponse($response, $extra);
        } catch (CurlException $e) {
            $this->logger->err("Riak is unavailable" . $e->getMessage());
            throw new RiakUnavailableException();
        } catch (\Exception $e) {
            $this->logger->err("Error while getting properties on bucket '" . $bucketName . "'. Full message is : " . $e->getMessage());
        }

        return $bucket;
    }

    /**
     * @param  \Kbrw\RiakBundle\Model\Cluster\Cluster $cluster
     * @param  \Kbrw\RiakBundle\Model\Bucket\Bucket   $bucket
     * @return boolean
     */
    public function save($cluster, $bucket)
    {
        $request = $this->getClient($cluster->getGuzzleClientProviderService(), $this->getConfig($cluster, $bucket->getName(), null))->put();
        try {
            $extra = array("method"=> "PUT");
            $ts = microtime(true);
            $request->setBody($this->serializer->serialize($bucket, "json"));
            $extra["serialization_time"] = microtime(true); - $ts;
            $request->setHeader("Content-Type", "application/json");
            $response = $request->send();
            $this->logResponse($response, $extra);
            return ($response->getStatusCode() == "204");
        } catch (CurlException $e) {
            $this->logger->err("Riak is unavailable" . $e->getMessage());
            throw new RiakUnavailableException();
        } catch (\Exception $e) {
            $this->logger->err("Error while setting properties on bucket '" . $bucket->getName() . "'. Full message is : " . $e->getMessage());
        }

        return false;
    }

    /**
     * @param  \Kbrw\RiakBundle\Model\Cluster\Cluster $cluster
     * @param  \Kbrw\RiakBundle\Model\Bucket\Bucket   $bucket
     * @return array<string,string>
     */
    public function getConfig($cluster, $bucketName, $keys = "stream", $props = false)
    {
        $config = array();
        $config["protocol"] = $cluster->getProtocol();
        $config["domain"]   = $cluster->getDomain();
        $config["port"]     = $cluster->getPort();
        $config["bucket"]   = $bucketName;
        $config["keys"]     = $keys;
        $config["props"]    = $props ? "true" : null;

        return $config;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @var \JMS\Serializer\Serializer
     */
    public $serializer;
}
