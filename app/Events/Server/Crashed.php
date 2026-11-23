<?php

namespace App\Events\Server;

use App\Events\Event;
use App\Models\Server;
use Illuminate\Queue\SerializesModels;

/** The server process exited on its own (no stop/restart/kill and no console command shortly before). */
class Crashed extends Event
{
    use SerializesModels;

    public function __construct(public Server $server) {}
}
