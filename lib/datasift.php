<?php
/**
 * Datasift
 * A wrapper to prepare the Datasift class
 * @author Chris Knight <chris.stuart.knight@gmail.com
 */
class Datasift {
    /**
     * apiKey.
     * @var string
     */
    private $apiKey = null;
    /**
     * user.
     * @var string
     */
    private $user = null;
    /**
     * client.     
     * The instantiated Datasift class
     * @var DataSift_User|null
     */
    private $client = null;
    /**
     * cdsl.
     * The CDSL that you want to process with Datasift
     * @var string
     */
    private $cdsl = null;
    /**
     * streamHash.
     * The hash of the filter that is active
     * @var string
     */
    private $streamHash = null;
    /**
     * types.
     * Datasift tags to filter upon, for example reddit.content
     * @var array
     */
    private $types = array();

    /** __construct
     * @param array $cfg
     * @throws Exception 'Usually it cannot create the DataSift_User'
     */
    public function __construct($cfg = array()) {
        try {
            $this->apiKey = $cfg['datasift']['apiKey'];
            $this->user = $cfg['datasift']['user'];
            $this->types = $cfg['datasift']['types'];

            $this->client = new DataSift_User($this->user, $this->apiKey);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * buildCDSL.
     * Generate the CDSL from a source array, for various types
     * @param $data
     * @return bool
     * @throws Exception 'The CDSL did not build correctly, probably missing types'
     */
    public function buildCDSL($data) {
        try {
            //need some types for this to work
            if($this->types) {
                $cdsl = array();

                if($data && is_array($data)) {
                    $data = implode(',', $data);
                    //comment only double quotes, not single
                    $data = '"' . addcslashes($data,'"') . '"';
                }

                //build the data for each filter type
                foreach($this->types as $type) {
                    $cdsl[] = '(' . $type . ' contains_any ' . $data . ' AND ' . $type . ' contains_any "Marvel")';
                }

                //join it all
                $this->cdsl = implode(' OR ', $cdsl);
                return true;
            } else {
                throw new Exception('No types found');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * createDefinition.
     * If you have a valid CDSL string, create a filter for it
     * @throws Exception 'You need to run buildCDSL first'
     */
    public function createDefinition() {
        try {
            if($this->cdsl) {
                $this->filter = $this->client->createDefinition($this->cdsl);
                $this->streamHash = $this->filter->getHash();
            } else {
                throw new Exception('No CDSL Set');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * getConsumer.
     * process the consumer, it's now ready to run
     * @return object
     * @throws Exception
     */
    public function getConsumer() {
        try {
            if($this->filter) {
                $this->consumer = $this->filter->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, new EventHandler());
                return $this->consumer;
            } else {
                throw new Exception('No Filter Defined');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

/**
 * EventHandler
 */
class EventHandler implements DataSift_IStreamConsumerEventHandler {
    public function onInteraction($consumer, $interaction, $hash) {
        echo "INTERACTION: ".json_encode($interaction).PHP_EOL.PHP_EOL;
    }
 
    // Triggered when a connection is successfully setup
    public function onConnect($consumer) {
        echo "Connected to DataSift".PHP_EOL;
    }
 
    // Triggered when an error message is received from DataSift
    public function onError($consumer, $message) {
        echo 'ERROR: '.$message.PHP_EOL;
    }
 
    // Triggered when an interaction is marked as deleted. For sources such as Twitter you must delete this interaction in your application to meet Ts&Cs.
    public function onDeleted($consumer, $interaction, $hash) {
        echo 'DELETE: Interaction '.$interaction['interaction']['id'].PHP_EOL;
    }
  
    // Ignore other events for quickstart example
    public function onStatus($consumer, $type, $info) {}
    public function onWarning($consumer, $message) {}
    public function onDisconnect($consumer) {}
    public function onStopped($consumer, $reason) {}
}
?>
