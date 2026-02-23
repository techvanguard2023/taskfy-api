<?php

use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\TaskServer;

Mcp::web('/mcp/tasks', TaskServer::class);

