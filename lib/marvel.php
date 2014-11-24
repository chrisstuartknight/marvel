<?php
/**
 * Marvel
 * Connect to the Marvel API and get data
 * @author Chris Knight <chris.stuart.knight@gmail.com
 * @extends Curl
 */
class Marvel extends Curl {
    /**
     * cfg.
     * @var array
     */
    private $cfg = array();
    /**
     * apiKey.
     * @var string
     */
    private $apiKey = null;
    /**
     * privateKey.
     * @var string
     */
    private $privateKey = null;
    /**
     * url.
     * Marvel API location
     * @var string
     */
    private $url = null;
    /**
     * ts.
     * timestamp
     * @var string
     */
    private $ts = null;
    /**
     * limit.
     * @var int
     */
    private $limit = 0;
    /**
     * modifiedSince.
     * Date to limit the API by
     * @var string
     */
    private $modifiedSince = null;
    /**
     * characters.
     * List of characters
     * @var array
     */
    private $characters = array();
    /** __construct
     * @param array $cfg
     * @throws Exception
     */
    public function __construct($cfg) {
        try {
            $this->cfg = $cfg;
            $this->apiKey = $this->cfg['marvel']['apiKey'];
            $this->privateKey = $this->cfg['marvel']['privateKey'];
            $this->url = $this->cfg['marvel']['url'];
            $this->limit = $this->cfg['marvel']['limit'];
            $this->modifiedSince = $this->cfg['marvel']['modifiedSince'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * call.
     * Connect to the Marvel API
     * @param $method
     * @param $params
     * @return bool
     * @throws Exception 'Cannot connect to the Marvel API'
     */
    private function call($method, $params) {
        try {
            // set the timestamp for each request, and rebuild auth
            $this->setTimestamp();
            $params = $this->getAuthentication($params);

            // call the api
            $response = $this->get($this->url . $method, $params);

            // got a response, decode and return it if it's valid
            if($response->body) {
                $response = json_decode($response->body,true);

                if(is_array($response) && $response['code'] == 200) {
                    return $response['data'];
                } else {
                    throw new Exception('Error from MarvelAPI: HTTP' . $response['code'] . ' ' . $response['status']);
                }
            } else {
                throw new Exception('Error Connecting to Marvel API');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
            return false;
        }
    }

    /**
     * getHash.
     * Build the hash from the ts, privateKey and apiKey
     * @return string
     * @throws Exception
     */
    private function getHash() {
        try {
            return md5($this->ts . $this->privateKey . $this->apiKey);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * setTimestamp.
     * set the timestamp
     * @throws Exception
     */
    private function setTimestamp() {
        try {
            $this->ts = date('U'); 
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * getAuthentication.
     * Add the apiKey, timestamp and hash to the call
     * @param $params
     * @return array
     * @throws Exception
     */
    private function getAuthentication($params) {
        try {
            $params['apikey'] = $this->apiKey;
            $params['ts'] = $this->ts;
            $params['hash'] = $this->getHash();

            return $params;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * getCharacters.
     * Gets a list of characters from the Marvel API, and loops if there
     * is more than 100. It can do the full list of over 1400, but the DPU
     * is very high
     *
     * If you run this more than once quickly, it'll check the class
     * @return array
     * @throws Exception
     */
    public function getCharacters() {
        try {
            if(count($this->characters) == 0) {
                $x = 0;

                do { 
                    $params = array(
                        'modifiedSince' => $this->modifiedSince,
                        'limit' => $this->limit,
                        'offset' => ($x * $this->limit)
                    );

                    $response = $this->call('characters', $params);
                    $this->characters = array_merge($this->characters, $response['results']);
                    $x++;
                } while($this->limit == $response['count']);
            }

            return $this->characters;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * getCharacterNames.
     * Grab the character names only
     * @return array
     * @throws Exception
     */
    public function getCharacterNames() {
        try {
            $this->getCharacters();
            $names = array();

            //remove all the alias information, reduces ability to match
            foreach($this->characters as $k => $character) {
                $names[$k] = trim(preg_replace("/\([^)]+\)/","",$character['name']));
            }

            //remove duplicates
            return array_unique($names);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
?>
