<?php

namespace App\Events\Broadcast;

use App\Http\Resources\ScheduleResource;
use App\Models\Instance;
use App\Models\Schedule;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Phobiavr\PhoberLaravelCommon\Clients\StaffClient;
use Phobiavr\PhoberLaravelCommon\Exceptions\ServiceUnavailableException;

class ScheduleUpdatedOverlay implements ShouldBroadcast {
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public readonly Schedule $schedule) {
    }

    public function broadcastOn(): array {
        $mac = Instance::query()->whereKey($this->schedule->instance_id)->value('mac_address');
        $secret = (string) config('service.overlay_secret');

        if (!$mac || $secret === '') {
            return [];
        }

        $slug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $mac));

        return [new Channel('schedule.' . $slug . '.' . $secret)];
    }

    public function broadcastAs(): string {
        return 'ScheduleUpdated';
    }

    public function broadcastWith(): array {
        $resource = ScheduleResource::make($this->schedule);

        if ($this->schedule?->session_id) {
            //TODO:: refactor
            try {
                $response = StaffClient::sessionById($this->schedule->session_id);
                if ($response->ok()) {
                    $data = json_decode($response->body());
                    $resource->servicedByName = $data->serviced_by_name ?? null;
                    $resource->customer = $data->customer ?? null;
                }
            } catch (ServiceUnavailableException $e) {
                Log::error('Failed to enrich overlay broadcast: staff-service unreachable', [
                    'session_id' => $this->schedule->session_id,
                    'message'    => $e->getMessage(),
                ]);
            }
        }

        return $resource->resolve();
    }
}
