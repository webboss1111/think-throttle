<?php


namespace think\middleware\throttle;

/**
 * 计数器滑动窗口算法
 * Class CouterSlider
 * @package think\middleware\throttle
 */
class CouterSlider extends ThrottleAbstract
{
    public function allowRequest(string $key, int $now, int $max_requests, int $duration, $cache)
    {
        $history = $cache->get($key, []);

        // 移除过期的请求的记录
        $history = array_values(array_filter($history, function ($val) use ($now, $duration) {
            return $val >= $now - $duration;
        }));

        $this->cur_requests = count($history);
        if ($this->cur_requests < $max_requests) {
            // 允许访问
            $history[] = $now;
            $cache->set($key, $history, $duration);
            return true;
        }

        if ($history) {
            $wait_seconds = $duration - ($now - $history[0]);
            $this->wait_seconds = $wait_seconds > 0 ? $wait_seconds : 0;
        }

        return false;
    }

}