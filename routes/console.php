<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('lines:expire')->hourly();
