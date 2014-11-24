<?php
require_once 'config/config.php';
require_once $cfg['composer'];
require_once $cfg['lib'] . 'marvel.php';

try {
    $mode = $cfg['process']['mode'];
    $file = $cfg['process']['file'];
    $output = $cfg['process']['output'];

    // override the defaults if you find them in the argv
    if(count($argv) > 0) {
        foreach($argv as $k => $arg) {
            switch($k) {
                case 1:
                    $file = $arg;
                break;

                case 2:
                    $output = $arg;
                break;

                case 2:
                    $mode = $arg;
                break;

                default:
                break;
            }
        }
    }

    if($mode == null) {
        throw new Exception('Process: No mode set.');
    }

    if(! $cfg['process']['allowed'] || ! in_array(strtolower($mode), $cfg['process']['allowed'])) {
        throw new Exception('Process: ' . $mode . ' mode not allowed.');
    }

    if(! file_exists($file) || ! is_readable($file)) {
        throw new Exception('Process: ' . $file . ' does not exist, or is unreadable');
    }

    // process the input file, and buffer the results
    $fh = fopen($file,'r');
    
    if($fh) {
        while(($buffer = fgets($fh, 4096)) !== false) {
            if(trim($buffer) != '') { //lose the blanks
                //remove the additional interaction and decode
                $buffer = str_replace('INTERACTION: ','' ,$buffer);
                $data[] = json_decode($buffer,true);
            }
        }
    }

    array_shift($data); //drop the connect message

    // grab a list of characters, to compare to
    $marvel = new Marvel($cfg);
    $characters = $marvel->getCharacterNames();
    $csv = array();

    // loop the interactions for every character, it's possible a character might be mentioned in multiple tweets
    // so get all of the references.
    foreach($data as $interaction) {
        foreach($characters as $k => $character) {
            // this entry doesn't exist, so set the defaults
            if(! isset($csv[$k]) || ! is_array($csv[$k])) {
                $csv[$k] = array(
                    'name' => $character,
                    'tweets' => 0,
                    'facebook' => 0,
                    'reddit' => 0,
                    'followers_count' => 0,
                    'favourites_count' => 0,
                    'most_seen_count' => '',
                    'most_seen_tweet' => '',
                    'male' => 0,
                    'female' => 0,
                );
            }

            // process the twitter interaction
            if(isset($interaction['twitter'])) {
                if(stristr($interaction['twitter']['text'], $character)) {
                    $csv[$k]['tweets']++;
                    $csv[$k]['followers_count'] += $interaction['twitter']['user']['followers_count'];
                    $csv[$k]['favourites_count'] += $interaction['twitter']['user']['favourites_count'];

                    // make a record of the most seen tweet
                    if($interaction['twitter']['user']['followers_count'] > $csv[$k]['most_seen_count']) {
                        $csv[$k]['most_seen_count'] = $interaction['twitter']['user']['followers_count'];
                        $csv[$k]['most_seen_tweet'] = str_replace(array("\r", "\n"), '', $interaction['twitter']['text']);
                    }

                    // probably a bit too simple, but detect if the user is male or female
                    // this code does actually get used in each interaction, so it probably should be a function really
                    if(isset($interaction['demographic'])) {
                        if(stristr($interaction['demographic']['gender'],'male')) {
                            $csv[$k]['male']++;
                        } else {
                            $csv[$k]['female']++;
                        }
                    }
                }
            }

            // process facebook interaction
            if(isset($interaction['facebook'])) {
                if(stristr($interaction['facebook']['message'], $character)) {
                    $csv[$k]['facebook']++;

                    if(isset($interaction['demographic'])) {
                        if(stristr($interaction['demographic']['gender'],'male')) {
                            $csv[$k]['male']++;
                        } else {
                            $csv[$k]['female']++;
                        }
                    }
                }
            }

            // process reddit interaction
            // most of these data files don't have reddit content, as it's more specific
            if(isset($interaction['reddit'])) {
                if(stristr($interaction['reddit']['content'], $character)) {
                    $csv[$k]['reddit']++;

                    if(isset($interaction['demographic'])) {
                        if(stristr($interaction['demographic']['gender'],'male')) {
                            $csv[$k]['male']++;
                        } else {
                            $csv[$k]['female']++;
                        }
                    }
                }
            }
        }
    }

    // write the csv output
    $file = $output . '.' . $mode;

    // is the file writeable and is there actually some data to write
    if((! file_exists($file) || is_writeable($file)) && count($csv) > 0) { 
        $fhw = fopen($output . '.' . $mode, 'w');

        if($fhw) {
            if($mode == 'csv') {
                // put in the headers
                fputcsv($fhw, array_keys($csv[0]), ',', '"');

                foreach($csv as $fields) {
                    fputcsv($fhw, $fields, ',', '"');
                }
            }
        }
    }
} catch (Exception $e) {
    print_r($e->getMessage());
}
?>
