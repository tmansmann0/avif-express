<?php

namespace Avife\common;

if (!defined('ABSPATH')) {
    exit;
}

use Avife\common\CronManager;
use Avife\common\Options;
use Avife\common\BackgroundImageConverter;
use Avife\traits\FileTrait;

class Cron
{
    use FileTrait;
    private const AVIF_BG_ACTIVITY_TRANSIENT = 'avif_bg_activity_users';
    private const AVIF_BG_ACTIVITY_PREFIX = 'u';

    public function initiateCron()
    {
        $cron = new CronManager(
            'avife_auto_convert',
            [$this, 'initiateConversion']
        );

        $backgroundConv = Options::getBackgroundConv();
        $currentSchedule = wp_next_scheduled('avife_auto_convert');

        // If background conversion is off, clear any existing cron job.
        if ($backgroundConv == 'off') {
            if ($currentSchedule) {
                $cron->clear();
            }
            return;
        }

        $eventInterval = Options::getBackgroundConvEvent();

        // If the cron job is NOT scheduled, schedule it.
        if (!$currentSchedule) {
            $cron->schedule($eventInterval);
        }
        // If the cron job is scheduled but the interval has changed, clear and reschedule.
        else {
            $scheduledInterval = $cron->getCurrentSchedule();
            if ($scheduledInterval !== $eventInterval) {
                $cron->clear();
                $cron->schedule($eventInterval);
            }
        }
    }

    //the actual work getting done here - not related to schedule or action hook
    public function initiateConversion()
    {
        // check if background image conversion is enabled or not 
        $directoryToTarget = Options::getBackgroundConv();
        if ($directoryToTarget == 'off') return false;

        $directoryPaths = [];
        if ($directoryToTarget == 'upload' || $directoryToTarget == 'themeandupload') {
            $directoryPaths[] = wp_upload_dir()['basedir'];
        }

        if ($directoryToTarget == 'theme' || $directoryToTarget == 'themeandupload') {
            //for only active theme directory
            if (is_child_theme()) {
                $directoryPaths[] = get_stylesheet_directory();
                $directoryPaths[] = get_template_directory();
            } else {
               $directoryPaths[] = get_template_directory();
            }
            //for whole theme directory just use get_theme_root() - not ideal for saving server space 
        }

        $filesToConvert = [];
        foreach ($directoryPaths as $directoryPath) {
            $filesToConvert = array_merge($filesToConvert,$this->findFiles($directoryPath,array('jpg','png','jpeg'),1));
        }

        if (empty($filesToConvert)) return false;

        if (Options::getBgQuietWindowEnabled() && !self::isInQuietWindow()) {
            return false;
        }

        if (Options::getBgIdleAware() && self::isBusyWindow()) {
            return false;
        }

        $batchSize = Options::getBgRunBatchSize();
        $workerCount = Options::getBgWorkerCount();
        $maxFileConversions = $batchSize * $workerCount;

        if (count($filesToConvert) > $maxFileConversions) {
            $filesToConvert = array_slice($filesToConvert, 0, $maxFileConversions);
        }

        if (empty($filesToConvert)) return false;

        for ($worker = 0; $worker < $workerCount; $worker++) {
            $backgroundImageConverterObj =  BackgroundImageConverter::get_instance($worker);

            $workerSlice = array_filter(
                $filesToConvert,
                function($index) use ($worker, $workerCount) {
                    return ($index % $workerCount) === $worker;
                },
                ARRAY_FILTER_USE_KEY
            );

            foreach ($workerSlice as $fileToConvert) {
                $backgroundImageConverterObj->push_to_queue($fileToConvert);
            }

            if (!$backgroundImageConverterObj->is_queue_empty()) {
                $backgroundImageConverterObj->save()->dispatch();
            }
        }

        return true;
    }

    public static function trackWebsiteActivity()
    {
        if (defined('DOING_CRON') && DOING_CRON) {
            return;
        }

        $activity = get_transient(self::AVIF_BG_ACTIVITY_TRANSIENT);
        if (!is_array($activity)) {
            $activity = [];
        }

        $user = self::getActivityUserKey();
        $activity[$user] = time();

        $window = max(15, (int)Options::getBgActivityWindowSeconds());
        set_transient(self::AVIF_BG_ACTIVITY_TRANSIENT, $activity, $window * 2);
    }

    private static function isBusyWindow()
    {
        $activity = self::getRecentActivity();
        $activeUsers = is_array($activity) ? count($activity) : 0;
        return $activeUsers >= (int)Options::getBgActiveUsers();
    }

    private static function isInQuietWindow()
    {
        $currentSeconds = (int)current_time('timestamp');
        $now = (int)strftime('%H', $currentSeconds) * 3600 + (int)strftime('%M', $currentSeconds) * 60;
        $start = self::getWindowSeconds(Options::getBgQuietWindowStart());
        $end = self::getWindowSeconds(Options::getBgQuietWindowEnd());

        if ($start === false || $end === false) {
            return false;
        }

        if ($start <= $end) {
            return $now >= $start && $now <= $end;
        }

        return ($now >= $start) || ($now <= $end);
    }

    private static function getRecentActivity()
    {
        $activity = get_transient(self::AVIF_BG_ACTIVITY_TRANSIENT);
        if (!is_array($activity) || empty($activity)) {
            return [];
        }

        $window = max(15, (int)Options::getBgActivityWindowSeconds());
        $cutoff = time() - $window;

        foreach ($activity as $userKey => $lastSeen) {
            if ((int)$lastSeen < $cutoff) {
                unset($activity[$userKey]);
            }
        }

        return $activity;
    }

    private static function getWindowSeconds($value)
    {
        if (!is_string($value)) {
            return false;
        }

        if (!preg_match('/^(?:2[0-3]|[01]?\d):[0-5]\d$/', $value)) {
            return false;
        }

        $parts = explode(':', $value);
        return ((int)$parts[0] * 60 * 60) + ((int)$parts[1] * 60);
    }

    private static function getActivityUserKey()
    {
        if (is_user_logged_in()) {
            return self::AVIF_BG_ACTIVITY_PREFIX . get_current_user_id();
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return self::AVIF_BG_ACTIVITY_PREFIX . md5($ip);
    }
}
