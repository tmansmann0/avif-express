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

    private $workerId = 0;

    private static $instances = [];

    public function __construct($workerId = 0)
    {   
        $this->workerId = absint($workerId);
        $this->action = 'bgic_' . $this->workerId;
        parent::__construct();
        $this->quality = Options::getImageQuality();
        $this->speed = Options::getComSpeed();
        $this->driver = IS_IMAGICK_AVIF ? 'imagick' : 'gd';
        add_filter($this->identifier . '_seconds_between_batches', array($this, 'getSecondsBetweenBatches'));
        add_filter($this->identifier . '_default_time_limit', array($this, 'getDefaultTimeLimit'));
        add_filter($this->identifier . '_pre_dispatch', array($this, 'maybePreventDispatch'), 10, 2);

        
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
        if (Cron::shouldPauseBackgroundProcessing()) {
            $this->pauseWorker();
            return $item;
        }

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

    public function maybePreventDispatch($cancel, $chainId)
    {
        if (Cron::shouldPauseBackgroundProcessing()) {
            $this->pauseWorker();
            return true;
        }

        return $cancel;
    }

    public function getSecondsBetweenBatches($seconds)
    {
        $configured = Options::getBgSleepSeconds();
        return max(0, (int)$configured);
    }

    public function getDefaultTimeLimit($seconds)
    {
        return 8;
    }

    public function killWorker()
    {
        $this->pauseWorker();
        $this->delete_all();
        $this->cancelWorker();
    }

    public function isProcessingSafe()
    {
        if (method_exists($this, 'is_processing')) {
            return (bool) $this->is_processing();
        }

        return false;
    }

    public function isPausedSafe()
    {
        if (method_exists($this, 'is_paused')) {
            return (bool) $this->is_paused();
        }

        return false;
    }

    public function isCancelledSafe()
    {
        if (method_exists($this, 'is_cancelled')) {
            return (bool) $this->is_cancelled();
        }

        return false;
    }

    public function hasQueuedItems()
    {
        if (method_exists($this, 'is_queued')) {
            return (bool) $this->is_queued();
        }

        if (method_exists($this, 'is_queue_empty')) {
            return ! $this->is_queue_empty();
        }

        return false;
    }

    public function pauseWorker()
    {
        if (method_exists($this, 'pause')) {
            $this->pause();
            return true;
        }

        return false;
    }

    public function resumeWorker()
    {
        if (method_exists($this, 'resume')) {
            $this->resume();
            return true;
        }

        if ($this->hasQueuedItems()) {
            $this->dispatch();
            return true;
        }

        return false;
    }

    public function cancelWorker()
    {
        if (method_exists($this, 'cancel')) {
            $this->cancel();
            return true;
        }

        if (method_exists($this, 'cancel_process')) {
            $this->cancel_process();
            return true;
        }

        return false;
    }

    //optional
    protected function complete()
    {
        parent::complete();
        //add any optional works in future after task 
    }
}
