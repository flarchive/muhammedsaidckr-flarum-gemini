<?php

namespace Msc\Gemini\Listener;

use Flarum\Discussion\Event\Started;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Queue\Queue;
use Msc\Gemini\Agent;
use Msc\Gemini\Job\ReplyJob;

class ReplyToPost
{
    public function __construct(
        protected Agent $agent,
        protected Queue $queue,
        protected SettingsRepositoryInterface $settings
    ) {
    }

    /**
     * @param  \Flarum\Discussion\Event\Started  $event
     * @return void
     */
    public function handle(Started $event): void
    {
        if (!$this->settings->get('muhammedsaidckr-gemini.enable_on_discussion_started')) {
            return;
        }

        $enabledTags = json_decode($this->settings->get('muhammedsaidckr-gemini.enabled-tags') ?? '[]', true);

        if (!empty($enabledTags)) {
            $discussionTags = $event->discussion->tags->pluck('id')->toArray();
            if (empty(array_intersect($discussionTags, $enabledTags))) {
                return;
            }
        }

        $this->queue->push(new ReplyJob($event->discussion));
    }
}
