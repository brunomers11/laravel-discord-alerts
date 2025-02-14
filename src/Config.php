<?php

namespace Spatie\DiscordAlerts;

use Spatie\DiscordAlerts\Exceptions\JobClassDoesNotExist;
use Spatie\DiscordAlerts\Exceptions\WebhookDoesNotExist;
use Spatie\DiscordAlerts\Exceptions\WebhookUrlNotValid;
use Spatie\DiscordAlerts\Jobs\SendToDiscordChannelJob;

class Config
{
    public static function getJob(array $arguments): SendToDiscordChannelJob
    {
        $jobClass = config('discord-alerts.job');

        if (is_null($jobClass) || ! class_exists($jobClass)) {
            throw JobClassDoesNotExist::make($jobClass);
        }

        return app($jobClass, $arguments);
    }

    public static function getWebhookUrl(string $name): string
    {
        if (filter_var($name, FILTER_VALIDATE_URL)) {
            return $name;
        }

        $url = config("discord-alerts.webhook_urls.{$name}");

        if (is_null($url)) {
            throw WebhookDoesNotExist::make($name);
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw WebhookUrlNotValid::make($name, $url);
        }

        return $url;
    }

    public static function getAvatarUrl(string $name): ?string
    {
        $url = config("discord-alerts.avatar_urls.{$name}", '');
    
        // If the URL is empty, return null (no avatar included in payload)
        if ($url === '') {
            return null;
        }
    
        // Validate that it is a proper URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid avatar URL: {$url}");
        }
    
        // Optional: Enforce HTTPS only
        if (!preg_match('/^https:\/\//', $url)) {
            throw new \InvalidArgumentException("Invalid avatar URL: {$url}. Must use HTTPS.");
        }
    
        return $url;
    }    

    public static function getConnection(): string
    {
        $connection = config("discord-alerts.queue_connection");

        if(is_null($connection)) {
            $connection = config("queue.default");
        }

        return $connection;
    }
}
