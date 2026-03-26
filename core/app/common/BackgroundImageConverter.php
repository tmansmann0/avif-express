<?php 

namespace Avife\common;
use PijushGupta\ImageConverter\ImageConverter;
use WP_Background_Process;
use Avife\common\Options;
use Exception;

class BackgroundImageConverter extends WP_Background_Process{

    protected $prefix = 'avife';

    protected $action = 'bgic';

    protected $quality;

    protected $speed;

    protected $driver;

    private static $instances = [];

    public function __construct($workerId = 0)
    {   
        parent::__construct();
        $this->action = 'bgic_' . absint($workerId);
        $this->quality = Options::getImageQuality();
        $this->speed = Options::getComSpeed();
        $this->driver = IS_IMAGICK_AVIF ? 'imagick' : 'gd';
        add_filter($this->identifier . '_seconds_between_batches', array($this, 'getSecondsBetweenBatches'));

        
    }
    /**
     * Gets the single instance of the class.
     *
     * @return self
     */
    public static function get_instance($workerId = 0) {
        $workerId = absint($workerId);
        if (! isset(self::$instances[$workerId])) {
            self::$instances[$workerId] = new self($workerId);
        }
        return self::$instances[$workerId];
    }

    //actual works  
    protected function task($item){
        if (!file_exists($item)) {
            return false;
        }

        try{
            $converter = new ImageConverter($item);
            $converter->setFormat('avif')->setDriver($this->driver)->setQuality($this->quality)->setSpeed($this->speed)->convert();
        }catch(Exception $e){
            Utility::logError($e->getMessage());
        }
        
        return false;
    }

    public function getSecondsBetweenBatches($seconds)
    {
        $configured = Options::getBgSleepSeconds();
        return max(0, (int)$configured);
    }

    //optional
    protected function complete()
    {
        parent::complete();
        //add any optional works in future after task 
    }
}
