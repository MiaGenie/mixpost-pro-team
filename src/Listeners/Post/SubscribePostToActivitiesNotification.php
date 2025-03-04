<?php

namespace Inovector\Mixpost\Listeners\Post;

use Inovector\Mixpost\Contracts\ShouldReceivePostModel;

class SubscribePostToActivitiesNotification
{
    public function handle(ShouldReceivePostModel $event): void
    {
        $event->post->subscribeToActivitiesNotifications(user: $event->post->user_id);
    }
}
