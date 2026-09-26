<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('journeys:close-idle')->everyFiveMinutes();
